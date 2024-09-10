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

namespace App\Http\Service\Crud;

use App\Http\Dao\Crud\SystemCrudFieldDao;
use App\Http\Service\BaseService;
use App\Http\Service\Config\DictDataService;
use App\Http\Service\Config\DictTypeService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * 实体字段
 * Class SystemCrudFormService.
 * @email 136327134@qq.com
 * @date 2024/2/24
 * @mixin SystemCrudFieldDao
 */
class SystemCrudFieldService extends BaseService
{
    /**
     * SystemCrudFormService constructor.
     */
    public function __construct(SystemCrudFieldDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 根据字段唯一值获取实体信息和字段信息.
     * @return array
     * @throws BindingResolutionException
     * @email 136327134@qq.com
     * @date 2024/3/19
     */
    public function formFieldUniqidByFieldCrud(array $formFieldUniqids)
    {
        $list = $this->dao->formFieldUniqidByFieldList($formFieldUniqids);

        if (! $list) {
            return [];
        }

        $crudIds = array_merge(array_unique(array_column($list, 'crud_id')));

        $crudList = app()->make(SystemCrudService::class)->getCrudList($crudIds);

        $crudData = [];
        foreach ($crudList as $item) {
            $crudData[$item['id']] = $item;
        }

        $data = [];
        foreach ($list as $item) {
            $data[$item['crud_id']]['crud_info'] = $crudData[$item['id']];
            $data[$item['crud_id']]['field'][]   = $item;
        }

        return $data;
    }

    /**
     * 根据字段获取实体信息.
     * @return array
     * @throws BindingResolutionException
     * @email 136327134@qq.com
     * @date 2024/3/28
     */
    public function fieldNameByFieldCrud(array $fields, string $tableName)
    {
        $crudIds = [];
        foreach ($fields as $field) {
            [$table, $name] = strstr($field, '.') !== false ? explode('.', $field) : [$tableName, $field];

            $id = app()->make(SystemCrudService::class)->value(['table_name_en' => $table], 'id');

            $crudIds[$id][] = $name;
        }

        if (! $crudIds) {
            return [];
        }

        $data = [];
        foreach ($crudIds as $id => $item) {
            $crudList = app()->make(SystemCrudService::class)->getCrudList(
                crudIds: [$id],
                with: [
                    'field' => fn ($q) => $q->with(['association'])->whereIn('field_name_en', $item),
                ]
            );

            if ($crudList) {
                $crudInfo = $crudList[0];
                $value    = $crudInfo['field'];
                unset($crudInfo['field']);

                $data[$id] = [
                    'crud_info' => $crudInfo,
                    'field'     => $value,
                ];
            }
        }

        return $data;
    }

    /**
     * 获取表字段列表.
     * @return mixed[]
     * @email 136327134@qq.com
     * @date 2024/2/27
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     * @throws BindingResolutionException
     */
    public function getTableFieldList(int $crudId, string $name = '')
    {
        [$page, $limit] = $this->getPageValue();
        $fieldList      = $this->dao->setDefaultSort(['id' => 'asc'])->select(
            where: ['field_name' => $name, 'crud_id' => $crudId],
            page: $page,
            limit: $limit
        )->toArray();
        $associationCrudId = array_filter(array_column($fieldList, 'association_crud_id'));
        $column            = app()->get(SystemCrudService::class)->getCrudTableAll($associationCrudId, true);

        $reTable       = '';
        $crudService   = app()->make(SystemCrudService::class);
        $reTablecrudId = $crudService->value(['id' => $crudId], 'crud_id');
        if ($reTablecrudId) {
            $reTable = $crudService->value(['id' => $reTablecrudId], 'table_name_en');
            if ($reTable) {
                $reTable .= '_id';
            }
        }

        $dataDictId = array_column($fieldList, 'data_dict_id');
        $dataColumn = app()->make(DataDictService::class)->column(['id' => $dataDictId], 'name', 'id');
        foreach ($fieldList as &$item) {
            $item['association_crud_table_name_en'] = $column[$item['association_crud_id']]['table_name_en'] ?? '';
            $item['association_crud_table_name']    = $column[$item['association_crud_id']]['table_name'] ?? '';
            if ($reTable && $item['field_name_en'] === $reTable) {
                $item['is_re_table'] = 1;
            } else {
                $item['is_re_table'] = 0;
            }
            $item['data_dict_name'] = $item['data_dict_id'] ? $dataColumn[$item['data_dict_id']] ?? '' : '';
        }

        return $fieldList;
    }

    /**
     * 获取某个表下的字段和关联字段
     * 当前主表的字段和一对一关联的字段.
     * @return array
     * @throws BindingResolutionException
     * @throws \ReflectionException
     * @email 136327134@qq.com
     * @date 2024/3/7
     */
    public function getCrudField(int $crudId, bool $approve = false)
    {
        $notField  = ['deleted_at', 'id'];
        $fieldInfo = $this->dao->select(
            where: ['crud_id' => $crudId, 'not_field' => $notField],
            field: [
                'field_name', 'field_name_en', 'crud_id', 'id',
                'association_crud_id', 'data_dict_id', 'prev_field', 'form_value',
            ],
            with: [
                'association' => fn ($q) => $q->with([
                    'field' => fn ($q) => $q->whereNotIn('field_name_en', $notField)
                        ->select([
                            'field_name', 'field_name_en', 'crud_id',
                            'prev_field', 'data_dict_id', 'form_value',
                            'id',
                        ]),
                ])->select(['table_name', 'table_name_en', 'id']),
            ]
        )->toArray();

        $fieldListData = $this->mergeSortField($fieldInfo);

        $dataDictId = array_merge(array_filter(array_column($fieldInfo, 'data_dict_id')));
        foreach ($fieldInfo as $item) {
            if (! empty($item['association']['field'])) {
                foreach ($item['association']['field'] as &$value) {
                    $value['field_name']    = $item['association']['table_name'] . '.' . $value['field_name'];
                    $value['field_name_en'] = $item['association']['table_name_en'] . '.' . $value['field_name_en'];
                }
                $fieldListData = array_merge($fieldListData, $this->mergeSortField($item['association']['field']));
                $dataDictId    = array_merge($dataDictId, array_merge(array_filter(array_column($item['association']['field'], 'data_dict_id'))));
            }
        }

        // 获取数据字段数据
        $dictDataService = app()->make(DictDataService::class);
        $dictData        = [];
        foreach ($dataDictId as $id) {
            $typeName      = app()->make(DictTypeService::class)->value($id, 'ident');
            $dictData[$id] = $dictDataService->getTreeData(['type_id' => $id, 'type_name' => $typeName]);
        }

        if ($approve) {
            foreach ($fieldListData as $key => $item) {
                $newItem = [];
                if ($item['data_dict_id'] && isset($dictData[$item['data_dict_id']])) {
                    $newItem['options'] = $dictData[$item['data_dict_id']] ?? [];
                } else {
                    $newItem['options'] = [];
                }

                $newItem['field']    = $item['field_name_en'];
                $newItem['title']    = $item['field_name'];
                $newItem['type']     = $item['form_value'];
                $newItem['id']       = $item['id'];
                $newItem['crud_id']  = $item['crud_id'];
                $newItem['is_user']  = !empty($item['association']['table_name_en']) && $item['association']['table_name_en'] === SystemCrudService::SYSTEM_TABLE_TABLE_USER;
                $newItem['is_frame']  = !empty($item['association']['table_name_en']) && $item['association']['table_name_en'] === SystemCrudService::SYSTEM_TABLE_TABLE_FRAME;
                $fieldListData[$key] = $newItem;
            }
        } else {
            foreach ($fieldListData as $key => $item) {
                if ($item['data_dict_id'] && isset($dictData[$item['data_dict_id']])) {
                    $item['data_dict_list'] = $dictData[$item['data_dict_id']];
                } else {
                    $item['data_dict_list'] = [];
                }
                $item['is_user']  = !empty($item['association']['table_name_en']) && $item['association']['table_name_en'] === SystemCrudService::SYSTEM_TABLE_TABLE_USER;
                $item['is_frame']  = !empty($item['association']['table_name_en']) && $item['association']['table_name_en'] === SystemCrudService::SYSTEM_TABLE_TABLE_FRAME;
                $fieldListData[$key] = $item;
            }
        }

        return $fieldListData;
    }

    /**
     * 合并排序结果.
     * @return array
     * @email 136327134@qq.com
     * @date 2024/3/7
     */
    public function mergeSortField(array $data)
    {
        [$emptyPrevFieldData, $prevFieldData] = $this->sortField($data);
        return array_merge($emptyPrevFieldData, $prevFieldData);
    }

    /**
     * 排序字段.
     * @return array
     * @email 136327134@qq.com
     * @date 2024/3/7
     */
    public function sortField(array $data, string $aname = 'prev_field', string $bname = 'field_name_en')
    {
        $emptyPrevFieldData = [];
        $prevFieldData      = [];
        foreach ($data as $item) {
            if (is_object($item)) {
                $item = (array) $item;
            }
            if ($item[$aname]) {
                $prevFieldData[] = $item;
            } else {
                $emptyPrevFieldData[] = $item;
            }
        }

        $length = count($prevFieldData);
        for ($i = 0; $i < $length - 1; ++$i) {
            for ($j = 0; $j < $length - $i - 1; ++$j) {
                // 比较相邻的元素并交换顺序
                if ($prevFieldData[$j][$aname] == $prevFieldData[$j + 1][$bname]) {
                    $temp                  = $data[$j];
                    $prevFieldData[$j]     = $prevFieldData[$j + 1];
                    $prevFieldData[$j + 1] = $temp;
                }
            }
        }

        return [$emptyPrevFieldData, $prevFieldData];
    }

    /**
     * 获取某个实体下一对一关联的所有实体ID.
     * @return array
     * @throws BindingResolutionException
     * @throws \ReflectionException
     * @email 136327134@qq.com
     * @date 2024/3/14
     */
    public function getAssociationIds(int $crudId, string $field = 'association_crud_id', string $key = '')
    {
        return $this->dao->column(['crud_id' => $crudId], $field, $key);
    }

    /**
     * 根据当前的实体获取到当前实体一对一关联的实体id,不包含从表内添加的默认字段.
     * @return array
     * @throws BindingResolutionException
     * @throws \ReflectionException
     * @email 136327134@qq.com
     * @date 2024/4/2
     */
    public function crudByAssociationIds(int $crudId)
    {
        return $this->dao->column(['association_crud_id' => $crudId, 'is_default' => 0], 'crud_id');
    }
}
