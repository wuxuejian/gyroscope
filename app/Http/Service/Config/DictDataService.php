<?php

declare(strict_types=1);
/**
 *  +----------------------------------------------------------------------
 *  | 陀螺匠 [ 赋能开发者，助力企业发展 ]
 *  +----------------------------------------------------------------------
 *  | Copyright (c) 2016~2024 https://www.tuoluojiang.com All rights reserved.
 *  +----------------------------------------------------------------------
 *  | Licensed 陀螺匠并不是自由软件，未经许可不能去掉陀螺匠相关版权
 *  +----------------------------------------------------------------------
 *  | Author: 陀螺匠 Team <admin@tuoluojiang.com>
 *  +----------------------------------------------------------------------
 */

namespace App\Http\Service\Config;

use App\Constants\CacheEnum;
use App\Http\Dao\Config\DictDataDao;
use App\Http\Model\BaseModel;
use App\Http\Service\BaseService;
use App\Task\system\BatchUpdateDictTask;
use crmeb\interfaces\ResourceServicesInterface;
use crmeb\services\FormService;
use crmeb\traits\service\ResourceServiceTrait;
use Hhxsv5\LaravelS\Swoole\Task\Task;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class DictDataService extends BaseService implements ResourceServicesInterface
{
    use ResourceServiceTrait;

    public function __construct(DictDataDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 根据id获取数据.
     * @return mixed[]
     * @throws BindingResolutionException
     * @email 136327134@qq.com
     * @date 2024/4/3
     */
    public function idByValues(int $typeId, array $ids = [])
    {
        return $this->dao->idByValues($typeId, $ids);
    }

    public function getList(array $where, array $field = ['*'], $sort = 'sort', array $with = []): array
    {
        [$page, $limit] = $this->getPageValue();
        return Cache::tags([CacheEnum::TAG_DICT])->remember(md5(json_encode($where) . $page . $limit), (int) sys_config('system_cache_ttl', 3600), function () use ($where, $field, $with, $page, $limit, $sort) {
            $list = $this->dao->setDefaultSort($sort)->select($where, $field, $with, $page, $limit)->each(function (&$item) use ($where) {
                $item['hasChildren'] = $this->dao->count(['pid' => $item['value'], 'type_name' => $where['type_name'], 'level' => $item['level'] + 1]);
            });
            $count = $this->dao->count($where);
            return $this->listData($list, $count);
        });
    }

    /**
     * 获取字典属性数据.
     * @return array
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getTreeData($where, $field = ['*'], $with = [])
    {
        $where['status'] = 1;
        return Cache::tags([CacheEnum::TAG_DICT])->remember(md5('tree' . json_encode($where) . json_encode($field)), (int) sys_config('system_cache_ttl', 3600), function () use ($where, $field, $with) {
            $list = toArray($this->dao->select($where, $field, $with));
            foreach ($list as &$item) {
                $item['checkbox'] = false;
            }
            return get_tree_children($list, keyName: 'value');
        });
    }

    /**
     * 新增表单.
     */
    public function resourceCreate(array $other = []): array
    {
        return $this->elForm('新增字典数据', $this->getFormRule(collect($other)), '/ent/config/dict_data');
    }

    /**
     * 保存数据.
     * @return BaseModel|mixed|Model
     * @throws BindingResolutionException
     */
    public function resourceSave(array $data)
    {
        if ($data['value'] =='' || is_null($data['value'])) {
            throw $this->exception('请填写正确的数据值');
        }
        if (! $data['pid']) {
            $data['level'] = 1;
        } else {
            $data['level'] = $this->dao->value(['value' => $data['pid']], 'level') + 1;
        }
        $type = toArray(app()->get(DictTypeService::class)->get($data['type_id'], ['level', 'ident']));
        if ($data['level'] > $type['level']) {
            throw $this->exception('已超出字典最大层级限制');
        }
        $data['type_name'] = $type['ident'];
        if ($this->dao->exists(['value' => $data['value'], 'type_name' => $data['type_name']])) {
            throw $this->exception('字典数据值已在重复, 请勿重复添加');
        }
        $res = $this->dao->create($data);
        Cache::tags([CacheEnum::TAG_DICT, CacheEnum::TAG_CUSTOMER])->flush();
        return $res;
    }

    /**
     * 修改表单.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function resourceEdit(int $id, array $other = []): array
    {
        $info = toArray($this->dao->get($id));
        if (! $info) {
            throw $this->exception('修改的字典数据不存在');
        }
        return $this->elForm('修改字典数据', $this->getFormRule(collect($info)), '/ent/config/dict_data/' . $id, 'PUT');
    }

    /**
     * 修改数据.
     * @return int|mixed
     * @throws BindingResolutionException
     */
    public function resourceUpdate($id, array $data)
    {
        if (! $data['value']) {
            throw $this->exception('请填写正确的数据值');
        }
        if (! $data['pid']) {
            $data['level'] = 1;
        } else {
            $data['level'] = $this->dao->value(['value' => $data['pid']], 'level') + 1;
        }
        $type = toArray(app()->get(DictTypeService::class)->get($data['type_id'], ['level', 'ident']));
        if ($data['level'] > $type['level']) {
            throw $this->exception('已超出字典最大层级限制');
        }
        $data['type_name'] = $type['ident'];
        if ($this->dao->exists(['not_id' => $id, 'value' => $data['value'], 'type_name' => $data['type_name']])) {
            throw $this->exception('字典数据值已存在, 请勿重复添加');
        }
        $res = $this->dao->update($id, $data);
        Cache::tags([CacheEnum::TAG_DICT, CacheEnum::TAG_CUSTOMER])->flush();
        return $res;
    }

    public function resourceShowUpdate($id, array $data)
    {
        $res = $this->dao->update($id, $data);
        Task::deliver(new BatchUpdateDictTask((int) $id));
        Cache::tags([CacheEnum::TAG_DICT, CacheEnum::TAG_CUSTOMER])->flush();
        return $res;
    }

    /**
     * 删除字典数据.
     * @return int|mixed
     * @throws \ReflectionException
     * @throws BindingResolutionException
     */
    public function resourceDelete($id, ?string $key = null)
    {
        $id     = explode(',', $id);
        $subIds = $this->getSubData($id);
        $res    = $this->dao->delete(['id' => $subIds, 'is_default' => 0]);
        Cache::tags([CacheEnum::TAG_DICT, CacheEnum::TAG_CUSTOMER])->flush();
        return $res;
    }

    /**
     * 多个字典回显.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getNamesByValue(string $ident, mixed $value): array
    {
        if ($value === '') {
            return [];
        }

        return Cache::tags([CacheEnum::TAG_DICT])->remember(md5('names_' . $ident . json_encode($value)), (int) sys_config('system_cache_ttl', 3600), function () use ($value, $ident) {
            return $this->dao->setDefaultSort(['level' => 'asc'])->column(['dict_value' => $value, 'type_name' => $ident], 'name');
        });
    }

    /**
     * 字典回显.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getNameByValue(string $ident, mixed $value): string
    {
        if ($value === '') {
            return '';
        }
        return Cache::tags([CacheEnum::TAG_DICT])->remember(md5('name_' . $ident . json_encode($value)), (int) sys_config('system_cache_ttl', 3600), function () use ($value, $ident) {
            return (string) $this->dao->value(['dict_value' => $value, 'type_name' => $ident], 'name');
        });
    }

    /**
     * 获取默认合同分类数据.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getDefaultContractCategory(): array
    {
        return $this->combination($this->getTreeData(['type_name' => 'contract_type']));
    }

    /**
     * 组合分类数据.
     */
    public function combination(array $options, array $combination = []): array
    {
        $result = [];
        foreach ($options as $option) {
            $newCombination = array_merge($combination, [$option['value']]);
            if (isset($option['children'])) {
                $result = array_merge($result, $this->combination($option['children'], $newCombination));
            } else {
                $result[] = $newCombination;
            }
        }
        return array_map(function ($item) {
            return is_array($item) ? json_encode($item) : (string) $item;
        }, $result);
    }

    /**
     * 获取完整分类数据.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getCompleteData(string $ident, array|string $value): array
    {
        $info = $this->dao->get(['dict_value' => $value, 'type_name' => $ident], ['id', 'name', 'value', 'pid']);
        if (! $info) {
            return [];
        }
        if ($info['pid'] > 0) {
            return array_merge($this->getCompleteData($ident, $info['pid']), [$info['value']]);
        }
        return [$info['value']];
    }

    /**
     * 根据名称获取value.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getValuesByName(mixed $name, string $ident): array
    {
        $values = [];
        $name   = array_filter(explode('/', $name));
        if (! $name) {
            return $values;
        }
        $where = ['name_eq' => $name, 'type_name' => $ident, 'status' => 1];
        if ($ident == 'area_cascade') {
            $pid      = $this->dao->value(array_merge($where, ['pid' => 0]), 'value');
            $values[] = $pid;
            for ($i = 0; $i < count($name) - 1; ++$i) {
                $pid = $this->dao->value(array_merge($where, ['pid' => $pid]), 'value');
                if ($pid < 1) {
                    break;
                }
                $values[] = $pid;
            }
        } else {
            $list = $this->dao->column($where, 'value', 'name');
            foreach ($name as $tmpValue) {
                if (isset($list[$tmpValue])) {
                    $values[] = $list[$tmpValue];
                }
            }
        }
        return $values;
    }

    /**
     * 根据类型获取value.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getValuesByType(array|string $name, string $ident, string $inputType, string $type): mixed
    {
        $key = md5(json_encode(['name' => $name, 'ident' => $ident, 'inputType' => $inputType, 'type' => $type]));
        return Cache::remember($key, 60, function () use ($name, $ident, $inputType, $type) {
            $values = [];
            switch ($inputType) {
                case 'checked':
                    $values = $this->getValuesByName($name, $ident);
                    break;
                case 'select':
                    if ($type == 'multiple') {
                        $name = array_filter(explode('#', $name));
                        foreach ($name as $item) {
                            $values[] = $this->getValuesByName($item, $ident);
                        }
                    } else {
                        $values = $this->getValuesByName($name, $ident);
                    }
                    break;
                case 'radio':
                    $values = $this->dao->value(['name_eq' => $name, 'type_name' => $ident, 'status' => 1], 'value');
                    break;
                default:
                    $values = $name;
                    break;
            }
            return $values;
        });
    }

    /**
     * 获取下级数据ID.
     * @return array
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    private function getSubData($id, $ids = [], $field = ['id', 'value', 'pid'])
    {
        $list = toArray($this->dao->select(['id' => $id], $field));
        if ($list) {
            $ids   = array_merge($ids, array_column($list, 'id'));
            $child = toArray($this->dao->select(['pid' => array_column($list, 'value')], $field));
            if ($child) {
                return $this->getSubData(array_column($child, 'id'), $ids, $field);
            }
            return $ids;
        }
        return array_merge($ids, array_column($list, 'id'));
    }

    /**
     * 获取表单规则.
     * @return array
     */
    private function getFormRule(Collection $collection)
    {
        $type = app()->get(DictTypeService::class)->get($collection->get('type_id', 0));
        if (! $type) {
            throw $this->exception('缺少字典关联参数');
        }
        $form[] = FormService::hidden('type_id', $collection->get('type_id', ''));
        $form[] = FormService::input('type_name', '字典标识', $type->name)->readonly(true);
        if ($collection->get('pid', '') !== '') {
            $parentLevel = $this->dao->value(['value' => $collection->get('pid', '')], 'level');
            $list        = get_tree_children(toArray($this->dao->select(['not_id' => $collection->get('id', ''), 'type_id' => $collection->get('type_id', 0), 'level_lt' => $parentLevel], ['value', 'id', 'pid', 'name as label'])), keyName: 'value');
            $form[]      = FormService::cascader('pid', '父级数据')
                ->options($list)->appendRule('value', $collection->get('pid', ''))
                ->props(['props' => ['checkStrictly' => true, 'emitPath' => false]]);
        }
        $value = $collection->get('value', '');
        if (! $value) {
            $value = $this->dao->count(['type_name' => $type->ident]) + 1;
        }
        $form[] = FormService::input('name', '数据名称', $collection->get('name', ''))->required();
        $form[] = FormService::input('value', '数据值', $value)->required();
        $form[] = FormService::number('sort', '排序', (int) $collection->get('sort', 0))->min(0)->max(999999)->precision(0);
        $form[] = FormService::radio('status', '状态', (int) $collection->get('status', 1))->options([['value' => 1, 'label' => '启用'], ['value' => 0, 'label' => '停用']]);
        $form[] = FormService::textarea('mark', '备注', $collection->get('mark', ''))->placeholder('请输入备注信息，最多可输入200字')->maxlength(200);
        return $form;
    }

    /**
     * 获取字典数据.
     * @param $where
     * @param array $field
     * @param array $with
     * @return array
     * @throws BindingResolutionException
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function getIdNameByTypeName($where, array $field = ['value as id', 'pid', 'name', 'level'], array $with = []): array
    {
        $where['status'] = 1;
        return Cache::tags([CacheEnum::TAG_DICT])->remember(md5('select' . json_encode($where) . json_encode($field)), (int) sys_config('system_cache_ttl', 3600), function () use ($where, $field, $with) {
            return get_tree_children(toArray($this->dao->select($where, $field, $with)));
        });
    }
}
