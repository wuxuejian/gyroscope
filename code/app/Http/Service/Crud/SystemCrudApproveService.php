<?php

declare(strict_types=1);
/**
 *  +----------------------------------------------------------------------
 *  | 陀螺匠 [ 赋能开发者，助力企业发展 ]
 *  +----------------------------------------------------------------------
 *  | Copyright (c) 2016~2025 https://www.tuoluojiang.com All rights reserved.
 *  +----------------------------------------------------------------------
 *  | Licensed 陀螺匠并不是自由软件，未经许可不能去掉陀螺匠相关版权
 *  +----------------------------------------------------------------------
 *  | Author: 陀螺匠 Team <admin@tuoluojiang.com>
 *  +----------------------------------------------------------------------
 */

namespace App\Http\Service\Crud;

use App\Constants\CacheEnum;
use App\Constants\Crud\CrudFormEnum;
use App\Http\Dao\Crud\SystemCrudApproveDao;
use App\Http\Service\BaseService;
use App\Http\Service\Client\ClientLabelService;
use App\Http\Service\Config\DictDataService;
use crmeb\interfaces\ResourceServicesInterface;
use crmeb\traits\service\ResourceServiceTrait;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Cache;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Class SystemCrudApproveService.
 * @email 136327134@qq.com
 * @date 2024/3/21
 * @mixin SystemCrudApproveDao
 */
class SystemCrudApproveService extends BaseService implements ResourceServicesInterface
{
    use ResourceServiceTrait;

    private int $cacheTtl;

    public function __construct(SystemCrudApproveDao $dao)
    {
        $this->dao      = $dao;
        $this->cacheTtl = (int) sys_config('system_cache_ttl', 3600);
    }

    /**
     * 列表.
     * @param mixed $sort
     */
    public function getList(array $where, array $field = ['*'], $sort = 'id', array $with = []): array
    {
        $cateService = app()->get(SystemCrudCateService::class);
        $listData    = parent::getList($where, $field, $sort, [
            'card' => function ($query) {
                $query->select(['id', 'avatar', 'name', 'phone']);
            },
            'process' => function ($query) {
                $query->select(['info->nodeUserList as info', 'approve_id']);
            },
            'crud' => fn ($q) => $q->select(['id', 'table_name', 'cate_ids']),
        ]);
        foreach ($listData['list'] as &$list) {
            if ($list['crud']) {
                $names             = $cateService->column(['ids' => $list['crud']['cate_ids']], 'name');
                $list['cate_name'] = $names ? implode('、', $names) : '';
            } else {
                $list['cate_name'] = '';
            }
            if (isset($list['process']['info']['userList']) && $list['process']['info']['userList']) {
                $list['userList'] = implode('、', array_column($list['process']['info']['userList'], 'name'));
            } else {
                $list['userList'] = '';
            }
            unset($list['process']);
        }
        return $listData;
    }

    /**
     * 保存配置信息.
     * @return mixed
     * @throws BindingResolutionException
     */
    public function resourceSave(array $data)
    {
        $userId         = uuid_to_uid($this->uuId(false));
        $processService = app()->get(SystemCrudApproveProcessService::class);
        $ruleService    = app()->get(SystemCrudApproveRuleService::class);
        $baseConfig     = $this->checkBaseConfig($data, $userId);
        $processConfig  = $processService->checkProcessConfig($data, $userId);
        $ruleConfig     = $ruleService->checkRuleConfig($data, $userId);
        $res            = $this->transaction(function () use ($baseConfig, $processConfig, $ruleConfig, $processService, $ruleService) {
            $res1 = $this->dao->create($baseConfig);
            if (! $res1) {
                throw $this->exception('保存基础配置失败');
            }
            $res2 = $processService->saveMore($processConfig, $res1->id);
            if (! $res2) {
                throw $this->exception('保存流程配置失败');
            }
            $ruleConfig['approve_id'] = $res1->id;
            $res3                     = $ruleService->create($ruleConfig);
            if (! $res3) {
                throw $this->exception('保存规则配置失败');
            }
            return $res1;
        });
        Cache::tags([CacheEnum::TAG_APPROVE])->flush();
        return $res;
    }

    /**
     * 获取配置信息详情.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function resourceEdit(int $id, array $other = []): array
    {
        return Cache::tags([CacheEnum::TAG_APPROVE])->remember('approve_config_' . $id, $this->cacheTtl, function () use ($id) {
            $baseConfig    = toArray($this->dao->get($id));
            $processConfig = [];
            $ruleConfig    = [];
            if ($baseConfig) {
                $processConfig = app()->get(SystemCrudApproveProcessService::class)->value(['approve_id' => $id, 'is_initial' => 1], 'info');
                $ruleConfig    = toArray(app()->get(SystemCrudApproveRuleService::class)->get(['approve_id' => $id], ['*'], [
                    'abCard' => function ($query) {
                        $query->select(['id', 'name', 'avatar', 'name']);
                    },
                ]));
            }
            return compact('baseConfig', 'processConfig', 'ruleConfig');
        });
    }

    /**
     * 保存配置信息.
     * @param mixed $id
     * @return mixed
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     */
    public function resourceUpdate($id, array $data)
    {
        if (! $this->dao->exists($id)) {
            throw $this->exception('修改的记录不存在，请确认后重试');
        }
        $userId         = uuid_to_uid($this->uuId(false));
        $processService = app()->get(SystemCrudApproveProcessService::class);
        $ruleService    = app()->get(SystemCrudApproveRuleService::class);
        $baseConfig     = $this->checkBaseConfig($data, $userId);
        $processConfig  = $processService->checkProcessConfig($data, $userId);
        $ruleConfig     = $ruleService->checkRuleConfig($data, $userId);
        $res            = $this->transaction(function () use ($id, $baseConfig, $processConfig, $ruleConfig, $processService, $ruleService) {
            // 保存基础配置
            $res1 = $this->dao->update(['id' => $id], $baseConfig);
            if (! $res1) {
                throw $this->exception('保存基础配置失败');
            }
            // 保存流程配置
            $processService->delete(['not_uniqued' => array_column($processConfig, 'uniqued'), 'approve_id' => $id]);
            if ($processConfig) {
                $res3 = $processService->saveMore($processConfig, $id);
                if (! $res3) {
                    throw $this->exception('保存流程配置失败');
                }
            }
            // 保存规则配置
            $ruleService->update(['approve_id' => $id], $ruleConfig);
            return true;
        });
        return $res && Cache::tags([CacheEnum::TAG_APPROVE])->flush();
    }

    /**
     * 删除.
     * @param mixed $id
     * @return int
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function resourceDelete($id, ?string $key = null)
    {
        if (! $this->dao->exists($id)) {
            throw $this->exception('删除的记录不存在');
        }
        return $this->dao->delete($id, $key) && Cache::tags([CacheEnum::TAG_APPROVE])->flush();
    }

    /**
     * 修改状态
     * @param mixed $id
     * @return mixed
     */
    public function resourceShowUpdate($id, array $data)
    {
        if ($res = $this->showUpdate($id, $data)) {
            Cache::tags([CacheEnum::TAG_APPROVE])->flush();
        }
        return $res;
    }

    /**
     * 处理基础配置.
     * @param mixed $userId
     * @param mixed $data
     * @param mixed $type
     * @return mixed
     */
    public function checkBaseConfig($data, $userId, $type = 'baseConfig'): array
    {
        return [
            'crud_id' => $data[$type]['crud_id'],
            'name'    => $data[$type]['name'],
            'icon'    => $data[$type]['icon'],
            'color'   => $data[$type]['color'],
            'info'    => $data[$type]['info'],
            'user_id' => $userId,
            'sort'    => $data[$type]['sort'] ?? 1,
        ];
    }

    public function resourceCreate(array $other = []): array
    {
        return [];
    }

    /**
     * 获取审批内容.
     * @return array
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     */
    public function getContent(int $crudId, int $linkId, int $applyId = 0)
    {
        $fieldService = app()->get(SystemCrudFieldService::class);
        $fields       = $fieldService->select(
            ['crud_id' => $crudId, 'is_form' => 1],
            ['field_name', 'field_name_en', 'form_value', 'field_type', 'form_field_uniqid', 'options', 'data_dict_id', 'association_crud_id'],
            ['association']
        )?->toArray();
        $moduleService = app()->get(CrudModuleService::class);
        $fn            = fn ($q) => $q->select(['id', 'name']);
        $record        = app()->get(SystemCrudApproveRecordService::class)->value(['approve_id' => $applyId, 'crud_id' => $crudId], 'data');
        $data          = $moduleService->model(crudId: $crudId)->setTrashed()->get(
            $linkId,
            array_column($fields, 'field_name_en'),
            ['ownerUser' => $fn, 'ownerFrame' => $fn, 'updateUser' => $fn, 'createUser' => $fn]
        )?->toArray();
        $dictService = app()->get(DictDataService::class);
        $content     = [];
        if ($data || $record) {
            foreach ($fields as $field) {
                if (isset($record[$field['field_name_en']])) {
                    $data[$field['field_name_en']] = $record[$field['field_name_en']];
                }
                $arr = [
                    'uniqued' => $field['form_field_uniqid'],
                    'label'   => $field['field_name'],
                    'type'    => $field['form_value'],
                ];
                if ($field['association_crud_id'] && $field['association']) {
                    $tableName = $fieldService->value(['crud_id' => $field['association_crud_id'], 'is_main' => 1], 'field_name_en');
                    if ($tableName) {
                        $value = $moduleService->setTableName($field['association']['table_name_en'])->value($data[$field['field_name_en']], $tableName);
                    }
                }
                $content[] = array_merge($arr, match ($field['form_value']) {
                    CrudFormEnum::FORM_IMAGE, CrudFormEnum::FORM_FILE => [
                        'type'  => 'uploadFrom',
                        'value' => json_decode($data[$field['field_name_en']], true),
                    ],
                    CrudFormEnum::FORM_INPUT_PERCENTAGE => [
                        'value' => $data[$field['field_name_en']] . '/' . ($field['options']['max'] ?? 100),
                    ],
                    CrudFormEnum::FORM_RADIO => [
                        'value' => $dictService->value(['value' => $data[$field['field_name_en']], 'type_id' => $field['data_dict_id']], 'name'),
                    ],
                    CrudFormEnum::FORM_CASCADER_RADIO => [
                        'value' => implode('/', $data[$field['field_name_en']] ? $dictService->column(['values' => explode('/', $data[$field['field_name_en']]), 'type_id' => $field['data_dict_id']], 'name') : []),
                    ],
                    CrudFormEnum::FORM_CHECKBOX => [
                        'value' => implode('、', $data[$field['field_name_en']] ? $dictService->column(['values' => explode('/', $data[$field['field_name_en']]), 'type_id' => $field['data_dict_id']], 'name') : []),
                    ],
                    CrudFormEnum::FORM_TAG => $field['data_dict_id']
                        ? ['value' => implode('、', $data[$field['field_name_en']] ? $dictService->column(['id' => explode('/', $data[$field['field_name_en']]), 'type_id' => $field['data_dict_id']], 'name') : [])]
                        : ['value' => implode('、', $data[$field['field_name_en']] ? app()->make(ClientLabelService::class)->idByValue(explode('/', $data[$field['field_name_en']])) : [])],
                    CrudFormEnum::FORM_INPUT_SELECT => match ($field['field_name_en']) {
                        'user_id'        => ['value' => $data['create_user']['name'] ?? ''],
                        'update_user_id' => ['value' => $data['update_user']['name'] ?? ''],
                        'frame_id'       => ['value' => $data['owner_frame']['name'] ?? ''],
                        'owner_user_id'  => ['value' => $data['owner_user']['name'] ?? ''],
                        default          => ['value' => $value ?? $data[$field['field_name_en']]]
                    },
                    CrudFormEnum::FORM_CASCADER => [
                        'value' => $this->getCascaderDictData((string) $data[$field['field_name_en']], (int) $field['data_dict_id']),
                    ],
                    CrudFormEnum::FORM_CASCADER_ADDRESS => [
                        'value' => implode('/', $data[$field['field_name_en']] ? $dictService->column(['id' => explode('/', $data[$field['field_name_en']]), 'type_id' => $field['data_dict_id']], 'name') : []),
                    ],
                    CrudFormEnum::FORM_SWITCH => [
                        'value' => $data[$field['field_name_en']] ? '开启' : '关闭',
                    ],
                    CrudFormEnum::FORM_RICH_TEXT => [
                        'value' => isset($data[$field['field_name_en']]) ? htmlspecialchars_decode($data[$field['field_name_en']]) : '',
                    ],
                    default => [
                        'value' => $data[$field['field_name_en']] ?? '',
                    ],
                });
            }
        }
        return $content;
    }

    /**
     * 获取审批.
     * @return mixed[]
     * @throws BindingResolutionException
     * @email 136327134@qq.com
     * @date 2024/4/2
     */
    public function getCrudApproveList(int $crudId)
    {
        $crudApproveId = app()->make(SystemCrudEventService::class)
            ->column(['crud_id' => $crudId, 'status' => 1], 'crud_approve_id');
        $crudApproveId = array_merge(array_unique(array_filter($crudApproveId)));
        return $this->dao->getCrudApprove($crudId, $crudApproveId);
    }

    /**
     * 获取tag内容.
     * @return string
     * @throws BindingResolutionException
     */
    private function getCascaderDictData(string $data, int $dictId)
    {
        $data = explode(',', $data);
        if (! $data) {
            return '';
        }
        $dataDict = app()->make(DictDataService::class)->idByValues($dictId);
        $res      = [];
        foreach ($data as $v) {
            $v  = array_filter(explode('/', $v));
            $vv = [];
            foreach ($dataDict as $item) {
                if (in_array($item['value'], $v)) {
                    $vv[] = $item['name'];
                }
            }
            $res[] = implode('/', $vv);
        }
        return implode('、', $res);
    }
}
