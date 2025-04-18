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

namespace App\Http\Service\Approve;

use App\Constants\ApproveEnum;
use App\Constants\CacheEnum;
use App\Http\Dao\Approve\ApproveDao;
use App\Http\Service\BaseService;
use App\Http\Service\Frame\FrameService;
use crmeb\traits\service\ResourceServiceTrait;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Cache;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * 审批记录表.
 */
class ApproveService extends BaseService
{
    use ResourceServiceTrait;

    protected $cardId;

    public function __construct(ApproveDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 列表.
     * @param mixed $sort
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function getList(array $where, array $field = ['*'], $sort = 'id', array $with = []): array
    {
        $uid = auth('admin')->id();
        if ($where['types']) {
            $this->getCardId();
            $where['id'] = $this->getUsableApprove($uid);
        }
        unset($where['types']);
        return parent::getList($where, $field, $sort, [
            'card' => function ($query) {
                $query->select(['id', 'avatar', 'name', 'phone']);
            },
            'rule' => function ($query) {
                $query->select(['id', 'refuse', 'abnormal', 'auto', 'approve_id']);
            },
            'process' => function ($query) {
                $query->select(['info->nodeUserList as info', 'approve_id']);
            },
        ]);
    }

    /**
     * 审批类型筛选列表.
     * @param mixed $uid
     * @param mixed $types
     * @return array
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getSearchList($types, int $uid)
    {
        switch ($types) {
            case 3:
                $where['id'] = app()->get(ApproveApplyService::class)->column(['user_id' => $uid], 'approve_id');
                $list        = $this->dao->setTrashed()->getList($where, ['*'], 0, 0, 'id');
                break;
            case 2:
                $where['id'] = app()->get(ApproveApplyService::class)->column([], 'approve_id');
                $list        = $this->dao->setTrashed()->getList($where, ['*'], 0, 0, 'id');
                break;
            case 1:
                $where['id'] = app()->get(ApproveUserService::class)->column(['user_id' => $uid], 'approve_id');
                $list        = $this->dao->setTrashed()->getList($where, ['*'], 0, 0, 'id');
                break;
            default:
                $where['id']        = $this->getUsableApprove($uid);
                $where['not_types'] = ApproveEnum::CUSTOMER_TYPES;
                $list               = $this->dao->getList($where, ['*'], 0, 0, 'id');
        }
        return $list;
    }

    /**
     * 保存配置信息.
     * @return mixed
     * @throws BindingResolutionException
     */
    public function resourceSave(array $data)
    {
        $this->getCardId();
        $baseConfig    = $this->checkBaseConfig($data);
        $formConfig    = $this->checkFormConfig($data);
        $processConfig = $this->checkProcessConfig($data);
        $ruleConfig    = $this->checkRuleConfig($data);
        $res           = $this->transaction(function () use ($baseConfig, $formConfig, $processConfig, $ruleConfig) {
            $res1 = $this->dao->create($baseConfig);
            if (! $res1) {
                throw $this->exception('保存基础配置失败');
            }
            $res2 = app()->get(ApproveFormService::class)->saveMore($formConfig, $res1->id);
            if (! $res2) {
                throw $this->exception('保存表单配置失败');
            }
            if ($baseConfig['examine']) {
                $res3 = app()->get(ApproveProcessService::class)->saveMore($processConfig, $res1->id);
                if (! $res3) {
                    throw $this->exception('保存流程配置失败');
                }
                $ruleConfig['approve_id'] = $res1->id;
                $res4                     = app()->get(ApproveRuleService::class)->create($ruleConfig);
                if (! $res4) {
                    throw $this->exception('保存规则配置失败');
                }
            }
            return true;
        });
        return $res && Cache::tags([CacheEnum::TAG_APPROVE])->flush();
    }

    /**
     * 获取配置信息详情.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function resourceEdit(int $id, array $other = []): array
    {
        return Cache::tags([CacheEnum::TAG_APPROVE])->remember('approve_config_' . $id, (int) sys_config('system_cache_ttl', 3600), function () use ($id) {
            $baseConfig = $this->dao->get(['id' => $id, 'entid' => 1]);
            if ($baseConfig) {
                $formService = app()->get(ApproveFormService::class);
                $formConfig  = [
                    'props' => $formService->dao->setDefaultSort(['sort' => 'asc'])->column(['approve_id' => $id], 'content'),
                    'form'  => $formService->value(['approve_id' => $id], 'config'),
                ];
                $processConfig = app()->get(ApproveProcessService::class)->value(['approve_id' => $id, 'is_initial' => 1], 'info');
                $ruleConfig    = app()->get(ApproveRuleService::class)->get(['approve_id' => $id], ['*'], [
                    'abCard' => function ($query) {
                        $query->select(['id', 'name', 'avatar']);
                    },
                ]);
            } else {
                $formConfig = [
                    'props' => [],
                    'form'  => [],
                ];
                $processConfig = [];
                $ruleConfig    = [];
            }
            return compact('baseConfig', 'formConfig', 'processConfig', 'ruleConfig');
        });
    }

    /**
     * 保存配置信息.
     * @param mixed $id
     * @return mixed
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function resourceUpdate($id, array $data)
    {
        if (! $info = $this->dao->get(['id' => $id, 'entid' => 1])) {
            throw $this->exception('修改的记录不存在，请确认后重试');
        }
        $this->getCardId();
        $baseConfig = $this->checkBaseConfig($data);
        $this->checkBindBusiness($info->toArray(), $baseConfig['types']);
        $formConfig    = $this->checkFormConfig($data);
        $processConfig = $this->checkProcessConfig($data);
        $ruleConfig    = $this->checkRuleConfig($data);
        $res           = $this->transaction(function () use ($id, $baseConfig, $formConfig, $processConfig, $ruleConfig) {
            // 保存基础配置
            $res1 = $this->dao->update(['id' => $id], $baseConfig);
            if (! $res1) {
                throw $this->exception('保存基础配置失败');
            }
            // 保存表单配置
            $formService = app()->get(ApproveFormService::class);
            $formService->delete(['not_uniqued' => array_column($formConfig, 'uniqued'), 'approve_id' => $id]);
            if ($formConfig) {
                $res2 = $formService->saveMore($formConfig, $id);
                if (! $res2) {
                    throw $this->exception('保存表单配置失败');
                }
            }
            // 保存流程配置
            $processService = app()->get(ApproveProcessService::class);
            $processService->delete(['not_uniqued' => array_column($processConfig, 'uniqued'), 'approve_id' => $id]);
            if ($processConfig) {
                $res3 = $processService->saveMore($processConfig, $id);
                if (! $res3) {
                    throw $this->exception('保存流程配置失败');
                }
            }
            // 保存规则配置
            app()->get(ApproveRuleService::class)->update(['approve_id' => $id], $ruleConfig);
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
        if (app()->get(ApproveApplyService::class)->exists(['approve_id' => $id])) {
            throw $this->exception('已存在关联的申请记录，无法删除！');
        }
        $info = toArray($this->dao->get(['id' => $id, 'entid' => 1]));
        if (! $info) {
            throw $this->exception('删除的记录不存在');
        }
        $this->checkBindBusiness($info, $info['types'], isDel: true);
        Cache::tags([CacheEnum::TAG_APPROVE])->flush();
        return $this->dao->delete($id, $key);
    }

    /**
     * 修改状态
     * @param mixed $id
     * @return mixed
     */
    public function resourceShowUpdate($id, array $data)
    {
        Cache::tags([CacheEnum::TAG_APPROVE])->flush();
        return $this->showUpdate($id, $data);
    }

    /**
     * 处理基础配置.
     * @param mixed $data
     * @param mixed $type
     * @return mixed
     */
    public function checkBaseConfig($data, $type = 'baseConfig')
    {
        return [
            'name'    => $data[$type]['name'],
            'icon'    => $data[$type]['icon'],
            'color'   => $data[$type]['color'],
            'info'    => $data[$type]['info'],
            'types'   => $data[$type]['types'] ?? 0,
            'entid'   => 1,
            'card_id' => $this->cardId,
            'user_id' => $this->cardId,
            'examine' => $data[$type]['examine'] ?? 1,
        ];
    }

    /**
     * 处理表单配置.
     * @param mixed $data
     * @param mixed $type
     * @return mixed
     */
    public function checkFormConfig($data, $type = 'formConfig')
    {
        $save = [];
        if (! $data[$type] || ! isset($data[$type]['props'])) {
            return $save;
        }
        foreach ($data[$type]['props'] as $k => $value) {
            $save[] = [
                'card_id'  => $this->cardId,
                'user_id'  => $this->cardId,
                'title'    => isset($value['title']) && $value['title'] ? $value['title'] : '',
                'info'     => $value['info'] ?? '',
                'value'    => isset($value['value']) && $value['value'] ? $value['value'] : '',
                'required' => isset($value['required']) && $value['required'] ? 1 : 0,
                'types'    => $value['type'],
                'content'  => json_encode($value),
                'props'    => isset($value['props']) && $value['props'] ? json_encode($value['props']) : '',
                'options'  => isset($value['options']) && $value['options'] ? json_encode($value['options']) : '',
                'config'   => isset($data[$type]['form']) && $data[$type]['form'] ? json_encode($data[$type]['form']) : '',
                'uniqued'  => isset($value['field']) && $value['field'] ? json_encode($value['field']) : '',
                'sort'     => $k,
            ];
        }
        return $save;
    }

    /**
     * 处理流程配置.
     * @param mixed $data
     * @param mixed $type
     * @return mixed
     */
    public function checkProcessConfig($data, $type = 'processConfig')
    {
        if (! isset($data[$type]) || ! $data[$type]) {
            return [];
        }
        return $this->getSerializeData($data[$type]);
    }

    /**
     * 初始化流程配置数据.
     * @param int $level
     * @param string $onlyValue
     * @param int $is_initial
     * @param mixed $group
     * @param mixed $data
     * @return array
     */
    public function getSerializeData($data, $level = 0, $onlyValue = '', $is_initial = 1, $group = 0)
    {
        $info[] = $this->getInfo($data, $onlyValue, $level, $is_initial, $group);
        if ($data['childNode']) {
            ++$level;
            if (isset($data['childNode'])) {
                $info = array_merge($info, $this->getSerializeData($data['childNode'], $level, $data['onlyValue'], 0, $group));
            }
        }
        if (isset($data['conditionNodes']) && $data['conditionNodes']) {
            foreach ($data['conditionNodes'] as $v) {
                ++$group;
                ++$level;
                $info[] = $this->getInfo($v, $data['onlyValue'], $level, 0, $group);
                if ($v['childNode']) {
                    $info = array_merge($info, $this->getSerializeData($v['childNode'], $level, $v['onlyValue'], 0, $group));
                }
            }
        }
        return $info;
    }

    /**
     * 组合流程数据.
     * @param string $parent
     * @param int $level
     * @param int $is_initial
     * @param int $group
     * @param mixed $data
     * @return array
     */
    public function getInfo($data, $parent = '', $level = 0, $is_initial = 0, $group = 0)
    {
        return [
            // 节点名称（申请人、审核人、抄送人）
            'name' => $data['nodeName'],
            // 节点类型：0、申请人；1、审核人；2、抄送人；3、条件；4、路由；
            'types'   => $data['type'],
            'uniqued' => $data['onlyValue'], // 节点唯一值
            // 审核人类型：1、指定成员；2、指定部门主管；7、连续多部门；5、申请人自己；4、申请人自选；(0、无此条件)
            'settype' => isset($data['settype']) && $data['settype'] ? $data['settype'] : 0,
            // 指定层级顺序：0、从上至下；1、从下至上；(-1、无此条件)
            'director_order' => $data['directorOrder'] ?? -1,
            // 指定主管层级/指定终点层级：1-10；(0、无此条件)
            'director_level' => isset($data['directorLevel']) && $data['directorLevel'] ? $data['directorLevel'] : 0,
            // 当前部门无负责人时：1、上级部门负责人审批；2、为空时跳过；(0、无此条件)
            'no_hander' => isset($data['noHanderAction']) && $data['noHanderAction'] ? $data['noHanderAction'] : 0,
            // 可选范围：1、不限范围；2、指定成员；(0、无此条件)
            'select_range' => isset($data['selectRange']) && $data['selectRange'] ? $data['selectRange'] : 0,
            // 指定的成员列表
            'user_list' => isset($data['nodeUserList']) && $data['nodeUserList'] ? $data['nodeUserList'] : [],
            // 选人方式：1、单选；2、多选；(0、无此条件)
            'select_mode' => isset($data['selectMode']) && $data['selectMode'] ? $data['selectMode'] : 0,
            // 多人审批方式：1、或签；2、会签；3、依次审批；(0、无此条件)
            'examine_mode' => isset($data['examineMode']) && $data['examineMode'] ? $data['examineMode'] : '',
            // 条件优先级
            'priority'       => isset($data['priorityLevel']) && $data['priorityLevel'] ? $data['priorityLevel'] : 0,
            'parent'         => $parent, // 父级唯一值
            'level'          => $level,
            'info'           => $is_initial > 0 ? $data : [],
            'is_initial'     => $is_initial,
            'is_child'       => $data['childNode'] ? 1 : 0,
            'is_condition'   => isset($data['conditionNodes']) && $data['conditionNodes'] ? 1 : 0,
            'card_id'        => $this->cardId,
            'user_id'        => $this->cardId,
            'groups'         => $group,
            'entid'          => 1,
            'condition_list' => $data['conditionList'] ?? [],
            // 指定部门负责人
            'dep_head' => $data['departmentHead'] ?? [],
            // 是否允许自选抄送人
            'self_select' => $data['ccSelfSelectFlag'] ?? 0,
        ];
    }

    /**
     * 处理规则配置.
     * @param mixed $data
     * @param mixed $type
     * @return mixed
     */
    public function checkRuleConfig($data, $type = 'ruleConfig')
    {
        return [
            'abnormal' => $data[$type]['abnormal'] ?: 0,
            'auto'     => $data[$type]['auto'] !== '' ? $data[$type]['auto'] : 2,
            'edit'     => isset($data[$type]['edit']) ? $data[$type]['edit'] : '',
            'recall'   => $data[$type]['recall'],
            'refuse'   => isset($data[$type]['refuse']) ? $data[$type]['refuse'] : 0,
            'card_id'  => $this->cardId,
            'user_id'  => $this->cardId,
        ];
    }

    /**
     * 获取申请人可用的配置ID.
     * @param mixed $userId
     * @return array|mixed
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function getUsableApprove($userId)
    {
        return Cache::tags([CacheEnum::TAG_APPROVE])->remember($userId . '_approve_ids', (int) sys_config('system_cache_ttl', 3600), function () use ($userId) {
            $ids       = app()->get(ApproveService::class)->column(['entid' => 1, 'status' => 1], 'id') ?? [];
            $processes = app()->get(ApproveProcessService::class)->select(['entid' => 1, 'is_initial' => 1, 'approve_id' => $ids], ['approve_id', 'info']);
            $approveId = [];
            if ($processes) {
                $frameServce = app()->get(FrameService::class);
                foreach ($processes as $process) {
                    $nodeUserList = $process->info['nodeUserList'];
                    if (isset($nodeUserList['depList']) && $nodeUserList['depList']) {
                        foreach ($nodeUserList['depList'] as $node) {
                            if ($frameServce->checkFrameUser($userId, $node['id'])) {
                                $approveId[] = $process->approve_id;
                            }
                        }
                    }
                    if (isset($nodeUserList['userList']) && $nodeUserList['userList']) {
                        foreach ($nodeUserList['userList'] as $node) {
                            if ($userId == $node['value']) {
                                $approveId[] = $process->approve_id;
                            }
                        }
                    }
                    if (isset($nodeUserList['userList'], $nodeUserList['depList']) && ! $nodeUserList['depList'] && ! $nodeUserList['userList']) {
                        $approveId[] = $process->approve_id;
                    }
                }
            }
            return $approveId;
        });
    }

    /**
     * 获取客户相关审批.
     * @param mixed $uuid
     * @param mixed $type
     * @param mixed $entId
     * @return array
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function getClientApprove($type, $uuid, $entId)
    {
        $status = match ((int) $type) {
            ApproveEnum::CUSTOMER_CONTRACT_PAYMENT     => sys_config('contract_refund_switch', 1),
            ApproveEnum::CUSTOMER_CONTRACT_RENEWAL     => sys_config('contract_renew_switch', 1),
            ApproveEnum::CUSTOMER_CONTRACT_EXPENSES    => sys_config('contract_disburse_switch', 1),
            ApproveEnum::CUSTOMER_INVOICE_ISSUANCE     => sys_config('invoicing_switch', 1),
            ApproveEnum::CUSTOMER_INVOICE_CANCELLATION => sys_config('void_invoice_switch', 1),
            default                                    => 0,
        };
        if (! $status) {
            return [];
        }
        $approve = toArray($this->dao->select([
            'id'     => $this->getUsableApprove(uuid_to_uid($uuid, $entId)),
            'types'  => $type,
            'entid'  => $entId,
            'status' => 1,
        ], ['id', 'name', 'icon', 'color', 'info']));
        if (! $approve) {
            throw $this->exception('尚未配置相关审批流程，请前往 人事->办公审批->审批设置 中配置');
        }
        return $approve;
    }

    /**
     * 获取用户名片ID.
     * @return $this
     * @throws BindingResolutionException
     */
    protected function getCardId()
    {
        $this->cardId = auth('admin')->id();
        return $this;
    }

    /**
     * 获取节点数据.
     * @param int $level
     * @param int $i
     * @param mixed $data
     * @return mixed
     */
    protected function getLevelChild($data, $level = 0, $i = 1)
    {
        if ($i == $level) {
            if (isset($data['childNode']['childNode'])) {
                unset($data['childNode']['childNode']);
            }
            return $data['childNode'];
        }
        if ($i > $level) {
            unset($data['childNode']);
            return $data;
        }
        ++$i;
        return $this->getLevelChild($data['childNode'], $level, $i);
    }

    /**
     * 判断是否与业务绑定.
     * @param mixed $isDel
     * @param mixed $approve
     * @param mixed $types
     * @param mixed $from
     * @throws BindingResolutionException
     */
    private function checkBindBusiness($approve, $types, $from = 'customer', $isDel = false)
    {
        $keys = match ($from) {
            'customer' => ['contract_refund_switch', 'contract_renew_switch', 'contract_disburse_switch', 'invoicing_switch', 'void_invoice_switch'],
            default    => []
        };
        if (in_array($types, ApproveEnum::CUSTOMER_TYPES) && in_array($types, array_values(sys_more($keys)))) {
            if ($isDel) {
                throw $this->exception('该审批流程与业务绑定，无法删除');
            }
            if ($types != $approve['types']) {
                throw $this->exception('该审批流程与业务绑定，无法修改指定控件组');
            }
        }
    }
}
