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

use App\Constants\Crud\CrudFormEnum;
use App\Constants\Crud\CrudOperatorEnum;
use App\Constants\Crud\CrudTriggerEnum;
use App\Http\Dao\Crud\CrudModuleDao;
use App\Http\Model\Crud\SystemCrud;
use App\Http\Service\Admin\AdminService;
use App\Http\Service\BaseService;
use App\Http\Service\Client\ClientLabelService;
use App\Http\Service\Config\DictDataService;
use App\Http\Service\Config\DictTypeService;
use App\Http\Service\Frame\FrameService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * 实体虚拟模型
 * Class CrudModuleService.
 * @email 136327134@qq.com
 * @date 2024/3/1
 * @mixin CrudModuleDao
 */
class CrudModuleService extends BaseService
{
    /**
     * CrudModuleService constructor.
     */
    public function __construct(CrudModuleDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 设置模型表名.
     * @return CrudModuleDao
     * @email 136327134@qq.com
     * @date 2024/3/1
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function model(string $tableName = '', int $crudId = 0)
    {
        if (! $tableName && ! $crudId) {
            throw $this->exception('缺少参数');
        }
        $service   = app()->get(SystemCrudService::class);
        $tableName = $service->value($tableName ? ['table_name_en' => $tableName] : $crudId, 'table_name_en');
        if (! $tableName) {
            throw $this->exception('没有查询到表名');
        }
        return $this->dao->setTableName($tableName);
    }

    /**
     * 获取表格展示字段.
     * @return array
     * @throws BindingResolutionException
     * @email 136327134@qq.com
     * @date 2024/3/9
     */
    public function getShowTableField(int $crudId, int $uid = 0, bool $association = false)
    {
        $select = [
            'id',
            'field_name_en',
            'field_name',
            'form_value',
            'association_crud_id',
            'data_dict_id',
        ];

        $fieldService = app()->make(SystemCrudFieldService::class);
        $showField    = [];

        // 查找用户自己保存的默认展示字段
        if ($uid) {
            $field = app()->make(SystemCrudTableUserService::class)->value(['crud_id' => $crudId, 'user_id' => $uid], 'show_field');
            if ($field) {
                $showFieldList = $fieldService->getFieldSelect($crudId, $field, $select);
                $showField     = [];
                foreach ($field as $item) {
                    foreach ($showFieldList as $value) {
                        if ($item === $value['field_name_en']) {
                            $showField[] = $value;
                        }
                    }
                }
            }
            unset($field);
        }

        // 如果用户没有保存，查找实体默认展示字段
        if (! $showField) {
            $field = app()->make(SystemCrudTableService::class)->value(['crud_id' => $crudId, 'is_index' => 1], 'show_field');
            if ($field) {
                $showFieldList = $fieldService->getFieldSelect($crudId, $field, $select);
                $showField     = [];
                foreach ($field as $item) {
                    foreach ($showFieldList as $value) {
                        if ($item === $value['field_name_en']) {
                            $showField[] = $value;
                        }
                    }
                }
            }
            unset($field);
        }

        // 如果实体没有保存，查找实体的全部字段展示出来
        if (! $showField) {
            $showField = $fieldService->getFieldTableShowRowSelect($crudId, $select);
        }

        return $association ? $this->getFieldAssociation($showField) : $showField;
    }

    /**
     * 获取一对一关联实体的相关信息.
     * @return array
     * @throws BindingResolutionException
     * @email 136327134@qq.com
     * @date 2024/3/12
     */
    public function getFieldAssociation(array $showField, string $tableNameEn = '')
    {
        foreach ($showField as $key => $item) {
            // 需要查询关联数据信息
            if ($item['association_crud_id']) {
                $crudInfo = app()->make(SystemCrudService::class)->get($item['association_crud_id'], ['id', 'table_name_en']);
                if ($crudInfo) {
                    $item['association_crud']                       = $crudInfo->toArray();
                    $item['association_crud']['field_main_name_en'] = app()->make(SystemCrudFieldService::class)
                        ->value(['crud_id' => $crudInfo->id, 'is_main' => 1], 'field_name_en');
                }
            }

            if (! isset($item['association_crud'])) {
                $item['association_crud'] = null;
            }

            if ($tableNameEn) {
                $item['table_name_en'] = $tableNameEn;
            }

            $showField[$key] = $item;
        }

        return $showField;
    }

    /**
     * 获取展示的自定义搜索.
     * @return array
     * @throws BindingResolutionException|\ReflectionException
     * @email 136327134@qq.com
     * @date 2024/3/12
     */
    public function getShowSearchField(int $crudId, int $uid = 0, bool $dataDict = true)
    {
        $select = [
            'id',
            'field_name_en',
            'field_name',
            'form_value',
            'data_dict_id',
        ];

        $fieldService = app()->make(SystemCrudFieldService::class);

        $seniorSearch = [];
        // 查找用户自己保存的搜索字段信息
        if ($uid) {
            $field = app()->make(SystemCrudTableUserService::class)->value(['crud_id' => $crudId, 'user_id' => $uid], 'senior_search');
            if ($field) {
                $seniorSearch = $fieldService->getFieldSelect($crudId, $field, $select);
            }
            unset($field);
        }

        if (! $seniorSearch) {
            $field = app()->make(SystemCrudTableService::class)->value(['crud_id' => $crudId, 'is_index' => 1], 'senior_search');
            if ($field) {
                $seniorSearch = $fieldService->getFieldSelect($crudId, $field, $select);
            }
            unset($field);
        }

        if (! $seniorSearch) {
            return [];
        }

        if ($dataDict) {
            foreach ($seniorSearch as $key => $search) {
                // 需要查询数据字段
                if ($dataDict) {
                    if ($search['data_dict_id']) {
                        $search['data_dict'] = app()->make(DictDataService::class)->getTreeData(['type_id' => $search['data_dict_id']]);
                    } else {
                        $search['data_dict'] = [];
                    }
                }
                $seniorSearch[$key] = $search;
            }
        }

        return $seniorSearch;
    }

    /**
     * 获取列表上能进行排序的字段.
     * @return array
     * @throws BindingResolutionException
     * @email 136327134@qq.com
     * @date 2024/3/15
     */
    public function getOrderByField(int $crudId)
    {
        $orderByformValue = [
            CrudFormEnum::FORM_DATE_TIME_PICKER,
            CrudFormEnum::FORM_DATE_PICKER,
            CrudFormEnum::FORM_CASCADER_RADIO,
            CrudFormEnum::FORM_RADIO,
            CrudFormEnum::FORM_INPUT_PERCENTAGE,
            CrudFormEnum::FORM_INPUT_PRICE,
            CrudFormEnum::FORM_INPUT_FLOAT,
            CrudFormEnum::FORM_INPUT_NUMBER,
            CrudFormEnum::FORM_SWITCH,
        ];

        $fieldService = app()->make(SystemCrudFieldService::class);

        $list = $fieldService->getOrderByFieldList($crudId, $orderByformValue);
        return $fieldService->mergeSortField($list);
        //        array_unshift($list, ['field_name_en' => 'default_field_name_en', 'field_name' => '默认排序']);
    }

    /**
     * 获取默认的搜索字段.
     * @return array
     */
    public function getkeywordDefaultField(array $fields)
    {
        $field = [];
        foreach ($fields as $item) {
            if (in_array($item['form_value'], [CrudFormEnum::FORM_INPUT, CrudFormEnum::FORM_TEXTAREA])) {
                $field[] = $item['field_name_en'];
            }
        }
        return $field;
    }

    /**
     * 列表查询.
     * @return array
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     * @email 136327134@qq.com
     * @date 2024/3/12
     */
    public function getModuleList(Request $request, SystemCrud $crud, array $defaultWhere, array $orderBy = [], array $viewSearch = [], string $keywordDefault = '', int $viewSearchBoolean = 0, int $crudId = 0, int $crudValue = 0, bool $uni = false)
    {
        $page  = (int) $request->post('page', 1);
        $limit = (int) $request->post('limit', 10);

        if (in_array($crud->table_name_en, SystemCrudService::SYSTEM_TABLE_TABLE)) {
            unset($defaultWhere['user_id'], $defaultWhere['show_search_type']);
        }

        $where = [];

        $showSearch = $this->getShowSearchField($crud->id, $defaultWhere['uid'], false);
        foreach ($showSearch as $search) {
            $value = $request->get($search['field_name_en'], '');

            if ($value !== '') {
                $when = [
                    'field_name' => $search['field_name_en'],
                    'form_value' => $search['form_value'],
                    'value'      => $value,
                ];
                $where[] = $when;
            }
        }

        // 默认的字段搜索
        $mainKey = app()->make(SystemCrudFieldService::class)->value(['crud_id' => $crud->id, 'is_main' => 1], 'field_name_en');
        if ($keywordDefault !== '' && $mainKey) {
            $where[] = [
                'field_name' => $mainKey,
                'form_value' => CrudFormEnum::FORM_INPUT,
                'value'      => $keywordDefault,
            ];
            $searchFields = $this->getkeywordDefaultField($crud->field->toArray());
            foreach ($searchFields as $key) {
                $where[] = [
                    'field_name' => $key,
                    'form_value' => CrudFormEnum::FORM_INPUT,
                    'value'      => $keywordDefault,
                ];
            }
        }

        // 详情中的关联列表展示
        if ($crudValue && $crudId) {
            $associationKey = app()->make(SystemCrudFieldService::class)->value(['crud_id' => $crud->id, 'association_crud_id' => $crudId], 'field_name_en');
            if ($associationKey) {
                $where[] = [
                    'field_name' => $associationKey,
                    'form_value' => CrudFormEnum::FORM_RADIO,
                    'value'      => $crudValue,
                ];
            }
        }

        // 获取搜索列表展示字段
        $showField = $this->getShowTableField($crud->id, $defaultWhere['uid'], true);
        if ($uni) {
            $select = [
                'id',
                'field_name_en',
                'field_name',
                'form_value',
                'association_crud_id',
                'data_dict_id',
            ];

            $fields           = array_column($showField, 'field_name_en');
            $crudFieldService = app()->make(SystemCrudFieldService::class);
            if (! in_array('created_at', $fields)) {
                $fieldInfo = $crudFieldService->get(['crud_id' => $crud->id, 'field_name_en' => 'created_at'], $select)?->toArray();
                if ($fieldInfo) {
                    array_push($showField, $fieldInfo);
                }
            }
            if (! in_array('owner_user_id', $fields)) {
                $fieldInfo = $crudFieldService->get(['crud_id' => $crud->id, 'field_name_en' => 'owner_user_id'], $select)?->toArray();
                if ($fieldInfo) {
                    array_push($showField, $fieldInfo);
                }
            }
        }

        [$select, $with, $otherWith] = $this->getFieldAndWith($showField);

        array_push($select, 'id');

        // 默认ID倒叙
        $orderBy = $orderBy ?: 'id';

        $join = app()->make(SystemCrudService::class)->getJoinCrudData($crud->id);

        if ($viewSearch) {
            $viewSearch = $this->setViewSearch($viewSearch, $crud->id);
        }

        $model = $this->model(crudId: $crud->id)->setJoin($join)->searchWhere($defaultWhere, $where, $viewSearch, $orderBy, $viewSearchBoolean === 0 ? 'or' : 'and');

        $count = $model->count();

        if ($join) {
            foreach ($select as &$field) {
                $field = $crud->table_name_en . '.' . $field;
            }
        }

        if (! $select) {
            $select = [$crud->table_name_en . '.*'];
        }

        $list = $model->when(count($with), fn ($q) => $q->with($with))->select($select)->forPage($page, $limit)->get()->toArray();

        $list = $this->otherWithList($otherWith, $list, $showField, $uni);

        return $this->listData($list, $count);
    }

    /**
     * 获取字段信息.
     * @return array
     * @email 136327134@qq.com
     * @date 2024/3/12
     */
    public function getFieldAndWith(array $showField, string $tableNameEn = '', bool $withTableName = false)
    {
        $select = $with = $otherWith = [];

        foreach ($showField as $item) {
            $select[] = $withTableName ? ($tableNameEn == $item['table_name_en'] ? '' : $item['table_name_en'] . '.') . $item['field_name_en'] : $item['field_name_en'];
            switch ($item['field_name_en']) {
                case 'frame_id':
                    $with['ownerFrame'] = fn ($q) => $q->select(['id', 'name']);
                    break;
                case 'owner_user_id':
                    $with['ownerUser'] = fn ($q) => $q->select(['id', 'name', 'avatar']);
                    break;
                case 'update_user_id':
                    $with['updateUser'] = fn ($q) => $q->select(['id', 'name', 'avatar']);
                    break;
                case 'user_id':
                    $with['createUser'] = fn ($q) => $q->select(['id', 'name', 'avatar']);
                    break;
            }

            if (isset($item['association_crud'])) {
                $otherWith[] = [
                    'table_name_en'                  => $item['table_name_en'] ?? '',
                    'association_crud_id'            => $item['association_crud_id'],
                    'field_name_en'                  => $item['field_name_en'],
                    'association_table_name_en'      => $item['association_crud']['table_name_en'],
                    'association_field_main_name_en' => $item['association_crud']['field_main_name_en'],
                    'association_field_id_name_en'   => $item['association_crud']['table_name_en'],
                ];
            }
        }

        return [$select, $with, $otherWith];
    }

    /**
     * 获取数据.
     * @return array|mixed
     * @email 136327134@qq.com
     * @date 2024/3/20
     */
    public function getValueAttribute(string $formValue, $value)
    {
        switch ($formValue) {
            case CrudFormEnum::FORM_TAG:
            case CrudFormEnum::FORM_CHECKBOX:
            case CrudFormEnum::FORM_CASCADER_ADDRESS:
            case CrudFormEnum::FORM_CASCADER_RADIO:
                $value = $value ? array_merge(
                    array_map(
                        fn ($vv) => preg_match('/^\d+$/', $vv) && ! in_array($formValue, [CrudFormEnum::FORM_CASCADER_RADIO, CrudFormEnum::FORM_CHECKBOX]) ? intval($vv) : $vv,
                        array_filter(
                            explode('/', $value)
                        )
                    )
                ) : [];
                break;
            case CrudFormEnum::FORM_IMAGE:
            case CrudFormEnum::FORM_FILE:
                $value = $value ? json_decode($value, true) : null;
                break;
            case CrudFormEnum::FORM_CASCADER:
                if (! is_null($value)) {
                    if (preg_match('/\/(\d+\/)+$/', $value)) {
                        $value     = explode(',', $value);
                        $dataValue = [];
                        foreach ($value as $item) {
                            $dataValue[] = array_merge(array_filter(explode('/', $item)));
                        }
                        $value = $dataValue;
                    } else {
                        $value = $value ? json_decode($value, true) : null;
                    }
                }
                break;
            case CrudFormEnum::FORM_DATE_PICKER:
            case CrudFormEnum::FORM_DATE_TIME_PICKER:
                if (in_array($value, ['0000-00-00', '0000-00-00 00:00:00'])) {
                    $value = '';
                }
                // no break
            case CrudFormEnum::FORM_RICH_TEXT:
                $value = $value ? htmlspecialchars_decode($value) : '';
                break;
        }

        return $value;
    }

    /**
     * 设置数据.
     * @return false|mixed|string
     * @email 136327134@qq.com
     * @date 2024/3/20
     */
    public function setValueAttribute(string $formValue, $value)
    {
        switch ($formValue) {
            case CrudFormEnum::FORM_TAG:
            case CrudFormEnum::FORM_CHECKBOX:
            case CrudFormEnum::FORM_CASCADER_ADDRESS:
            case CrudFormEnum::FORM_CASCADER_RADIO:
                $value = $value ? '/' . implode('/', is_array($value) ? $value : [$value]) . '/' : '';
                break;
            case CrudFormEnum::FORM_IMAGE:
            case CrudFormEnum::FORM_FILE:
                $value = json_encode($value);
                break;
            case CrudFormEnum::FORM_CASCADER:
                $impodeVal = [];
                foreach ($value as $item) {
                    $impodeVal[] = '/' . implode('/', $item) . '/';
                }
                $value = implode(',', $impodeVal);
                break;
            case CrudFormEnum::FORM_INPUT_SELECT:
                $value = $value['id'] ?? $value;
                break;
            case CrudFormEnum::FORM_RICH_TEXT:
                $value = htmlspecialchars($value);
                break;
        }

        return $value;
    }

    /**
     * 设置值制空.
     * @return int|string
     * @email 136327134@qq.com
     * @date 2024/3/26
     */
    public function setValueEmptyAttribute(string $formValue)
    {
        $value = '';
        switch ($formValue) {
            case CrudFormEnum::FORM_RADIO:
            case CrudFormEnum::FORM_INPUT_PRICE:
            case CrudFormEnum::FORM_INPUT_PERCENTAGE:
            case CrudFormEnum::FORM_INPUT_FLOAT:
            case CrudFormEnum::FORM_INPUT_NUMBER:
            case CrudFormEnum::FORM_SWITCH:
            case CrudFormEnum::FORM_SELECT:
            case CrudFormEnum::FORM_INPUT_SELECT:
                $value = 0;
                break;
        }

        return $value;
    }

    /**
     * 获取创建表单信息.
     * @param int $crudId 关联添加的实体ID
     * @param int $crudValue 关联添加的实体数据的ID
     * @return array
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     * @email 136327134@qq.com
     * @date 2024/3/14
     */
    public function getCreateForm(SystemCrud $crud, int $crudId = 0, int $crudValue = 0, int $id = 0, bool $uni = false)
    {
        $crudFormService = app()->make(SystemCrudFormService::class);
        $formInfo        = $crudFormService->get(['crud_id' => $crud->id, 'is_index' => 1])?->toArray();
        if (! $formInfo) {
            throw $this->exception('没有查询到表单信息');
        }

        $fieldName    = null;
        $defaultValue = (object) [];
        if ($crudId && $crudValue) {
            $service       = app()->make(SystemCrudFieldService::class);
            $fieldName     = $service->value(['crud_id' => $crud->id, 'association_crud_id' => $crudId], 'field_name_en');
            $mainFieldName = $service->value(['crud_id' => $crudId, 'is_main' => 1], 'field_name_en');
            if ($mainFieldName) {
                $name = $this->model(crudId: $crudId)->viewSearch(viewSearch: [
                    [
                        'field_name' => 'id',
                        'operator'   => CrudOperatorEnum::OPERATOR_EQ,
                        'value'      => $crudValue,
                    ],
                ])->value($mainFieldName);
                $defaultValue = ['id' => $crudValue, 'name' => $name];
            }
        }

        $fieldList = $this->getFormField($crud->id);
        $column    = [];

        if ($fieldList && $formInfo['options']) {
            $dictData        = [];
            $dictDataService = app()->make(DictDataService::class);

            foreach ($fieldList as $item) {
                $key = $item['crud']['id'] != $crud->id ? $item['crud']['table_name_en'] . '.' . $item['field_name_en'] : $item['field_name_en'];
                if ($fieldName == $key) {
                    $item['disabled'] = true;
                    if (! $id) {
                        $item['defaultValue'] = $defaultValue;
                    }
                }

                if ($item['data_dict_id']) {
                    if (! isset($dictData[$item['data_dict_id']])) {
                        $typeName                        = app()->make(DictTypeService::class)->value($item['data_dict_id'], 'ident');
                        $dictData[$item['data_dict_id']] = $dictDataService->getTreeData(['type_id' => $item['data_dict_id'], 'type_name' => $typeName]);
                    }
                    $item['data_dict'] = $dictData[$item['data_dict_id']];
                }

                if (! $item['create_modify'] && ! $id) {
                    $item['disabled'] = true;
                }
                if (! $item['update_modify'] && $id) {
                    $item['disabled'] = true;
                }

                $column[$key] = $item;
            }

            $formInfo['options'] = $crudFormService->getFormOptions($formInfo['options'], $column);
        }

        if ($uni) {
            $columnnew = [];
            $fields    = app()->make(SystemCrudFormService::class)->getFormFields($formInfo['options']);
            foreach ($fields as $key) {
                foreach ($column as $item) {
                    if ($key === $item['field_name_en']) {
                        $item['field_name_en'] = $item['crud']['id'] != $crud->id ? $item['crud']['table_name_en'] . '.' . $item['field_name_en'] : $item['field_name_en'];
                        $item['field_name_en'] = str_replace('.', '@', $item['field_name_en']);
                        $columnnew[]           = $item;
                    }
                }
            }
            $column = $columnnew;
        }

        return [
            'form_info'  => $formInfo,
            'crud'       => $crud->toArray(),
            'field_name' => $fieldName,
            'column'     => $column,
        ];
    }

    /**
     * 获取实体列表字段展示和搜索字段展示.
     * @return array
     * @throws BindingResolutionException
     * @throws \ReflectionException
     * @email 136327134@qq.com
     * @date 2024/4/7
     */
    public function getCrudInfo(SystemCrud $crud, int $uid, int $systemCrudId = 0, bool $uni = false)
    {
        $showField    = $this->getShowTableField($crud->id, $systemCrudId ? 0 : $uid);
        $seniorSearch = $this->getShowSearchField($crud->id, $systemCrudId ? 0 : $uid);
        $orderByField = $this->getOrderByField($crud->id);
        $viewSearch   = app()->make(SystemCrudService::class)->getEventCrud($crud->id)['field'];
        $crudInfo     = $crud->toArray();

        $mainFieldName               = '';
        $crudInfo['systemField']     = $crudInfo['customField'] = [];
        $crudInfo['systemListField'] = $crudInfo['customListField'] = [];
        if ($crudInfo['field']) {
            $systemField     = [];
            $customField     = [];
            $systemListField = [];
            $customListField = [];
            foreach ($crudInfo['field'] as $item) {
                if ($item['is_main']) {
                    $mainFieldName = $item['field_name_en'];
                }
                if ($item['is_default']) {
                    if (! in_array($item['field_name_en'], ['user_id', 'deleted_at'])) {
                        if (! in_array($item['form_value'], [CrudFormEnum::FORM_RICH_TEXT])) {
                            $systemListField[] = $item;
                        }
                        if (! in_array($item['form_value'], [CrudFormEnum::FORM_IMAGE, CrudFormEnum::FORM_FILE, CrudFormEnum::FORM_INPUT, CrudFormEnum::FORM_TEXTAREA, CrudFormEnum::FORM_RICH_TEXT])) {
                            $systemField[] = $item;
                        }
                    }
                } else {
                    if (! in_array($item['form_value'], [CrudFormEnum::FORM_RICH_TEXT])) {
                        $customListField[] = $item;
                    }
                    if (! in_array($item['form_value'], [CrudFormEnum::FORM_IMAGE, CrudFormEnum::FORM_FILE, CrudFormEnum::FORM_INPUT, CrudFormEnum::FORM_TEXTAREA, CrudFormEnum::FORM_RICH_TEXT])) {
                        $customField[] = $item;
                    }
                }
            }
            $crudInfo['systemField']     = $systemField;
            $crudInfo['customField']     = $customField;
            $crudInfo['systemListField'] = $systemListField;
            $crudInfo['customListField'] = $customListField;
        }

        if ($uni) {
            $newShowField = [];
            $mainItem     = [];
            foreach ($showField as $item) {
                if ($item['field_name_en'] === $mainFieldName) {
                    $mainItem = $item;
                } else {
                    $newShowField[] = $item;
                }
            }
            array_unshift($newShowField, $mainItem);
            $showField = $newShowField;
        }

        $crudInfo['main_field_name'] = $mainFieldName;

        if ($systemCrudId) {
            // 用户系统保存的配置信息用来回显
            $userOptions = app()->make(SystemCrudTableService::class)->get([
                'crud_id'  => $crudInfo['id'],
                'is_index' => 1,
            ])?->toArray();
        } else {
            // 用户自己保存的配置信息用来回显
            $userOptions = app()->make(SystemCrudTableUserService::class)->get([
                'crud_id' => $crudInfo['id'],
                'user_id' => $uid,
            ])?->toArray();

            if (! $userOptions) {
                $userOptions = app()->make(SystemCrudTableService::class)->get([
                    'crud_id'  => $crudInfo['id'],
                    'is_index' => 1,
                ])?->toArray();
            }
        }

        if (! $userOptions) {
            $userOptions = (object) [];
        } else {
            $crudService    = app()->make(SystemCrudService::class);
            $fieldService   = app()->make(SystemCrudFieldService::class);
            $associationIds = $fieldService->crudByAssociationIds($crud->id);

            if (! empty($userOptions['options']['tab'])) {
                $ids    = array_column($userOptions['options']['tab'], 'id');
                $column = $crudService->getCrudTableAll($ids);
                $tab    = [];
                foreach ($userOptions['options']['tab'] as $option) {
                    if (isset($column[$option['id']]) && in_array($option['id'], $associationIds)) {
                        $tab[] = $option;
                    }
                }
                $userOptions['options']['tab'] = $tab;
            }
            if (! empty($userOptions['options']['create'])) {
                $ids    = array_column($userOptions['options']['create'], 'id');
                $column = $crudService->getCrudTableAll($ids);
                $create = [];
                foreach ($userOptions['options']['create'] as $option) {
                    if (isset($column[$option['id']]) && in_array($option['id'], $associationIds)) {
                        $create[] = $option;
                    }
                }
                $userOptions['options']['create'] = $create;
            }
        }

        $fieldIds = array_column($viewSearch,'id');
        $association = app()->make(SystemCrudFieldService::class)->fieldIdByAssociationInfo($fieldIds);
        $associationData = [];
        foreach ($association as $item){
            $associationData[$item['id']] = $item['association'];
        }
        $newViewSearch = [];
        foreach ($viewSearch as $search) {
            $isUser  = !empty($associationData[$search['id']]) && $associationData[$search['id']]['table_name_en'] === SystemCrudService::SYSTEM_TABLE_TABLE_USER;
            $isFrame  = !empty($associationData[$search['id']]) && $associationData[$search['id']]['table_name_en'] === SystemCrudService::SYSTEM_TABLE_TABLE_FRAME;
            $newViewSearch[] = [
                'type'         => $search['form_value'],
                'field'        => $search['field_name_en'],
                'id'           => $search['id'],
                'is_user' => $isUser,
                'is_frame'=> $isFrame,
                'options'      => $search['data_dict'],
                'title'        => $search['label'],
                'crud_id'      => $search['crud_id'],
                'is_city_show' => $search['is_city_show'],
            ];
        }

        $viewSearch = $newViewSearch;

        return compact('crudInfo', 'userOptions', 'showField', 'seniorSearch', 'orderByField', 'viewSearch');
    }

    /**
     * 获取一对一关联展示字段.
     * @return array
     * @throws BindingResolutionException
     * @email 136327134@qq.com
     * @date 2024/3/12
     */
    public function getAssociationField(int $fieldId)
    {
        $fieldService = app()->make(SystemCrudFieldService::class);
        $fieldInfo    = $fieldService->get($fieldId);
        if (! $fieldInfo) {
            throw $this->exception('没有查询到实体字段信息信息');
        }
        if (! $fieldInfo->association_crud_id) {
            throw $this->exception('没有查询到一对一关联实体信息');
        }

        // 获取主字段展示
        $indexFieldName = $fieldService->value(['crud_id' => $fieldInfo->association_crud_id, 'is_main' => 1], 'field_name_en');

        $associationFieldNames = $fieldInfo->association_field_names;
        if (! in_array('id', $associationFieldNames)) {
            array_unshift($associationFieldNames, $indexFieldName, 'id');
        } else {
            array_unshift($associationFieldNames, $indexFieldName);
        }

        // 展示
        $associationField = $fieldService->getFieldSelect($fieldInfo->association_crud_id, $associationFieldNames);

        $indexfieldInfo = [];
        $fields         = [];
        foreach ($associationField as $item) {
            if ($indexFieldName != $item['field_name_en']) {
                $fields[] = $item;
            } else {
                $indexfieldInfo = $item;
            }
        }

        array_unshift($fields, $indexfieldInfo);

        return [
            'index_field_name'  => $indexFieldName,
            'association_field' => $fields,
        ];
    }

    /**
     * 获取一对一关联展示列表.
     * @return array
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @email 136327134@qq.com
     * @date 2024/3/12
     */
    public function getAssociationList(int $fieldId, string $keyword = '')
    {
        $fieldService = app()->make(SystemCrudFieldService::class);
        $fieldInfo    = $fieldService->get($fieldId, ['*'], ['crud' => fn ($q) => $q->select(['id', 'table_name_en'])]);
        if (! $fieldInfo) {
            throw $this->exception('没有查询到实体字段信息信息');
        }
        if (! $fieldInfo->association_crud_id) {
            throw $this->exception('没有查询到一对一关联实体信息');
        }
        // 获取主字段展示
        $indexFieldName = $fieldService->value(['crud_id' => $fieldInfo->association_crud_id, 'is_main' => 1], 'field_name_en');
        if (! $indexFieldName) {
            throw $this->exception('请先前往开发中设置当前实体的主字段展示');
        }

        $selectField = [
            'id',
            'field_name_en',
            'field_name',
            'form_value',
            'association_crud_id',
            'data_dict_id',
        ];

        $fieldSelect = $fieldService->getFieldSelect($fieldInfo->association_crud_id, $fieldInfo->association_field_names, $selectField);
        // 获取字段相关关联实体
        $fieldSelect = $this->getFieldAssociation($fieldSelect);

        [$select, $with, $otherWith] = $this->getFieldAndWith($fieldSelect);

        array_push($select, 'id', $indexFieldName);

        [$page, $limit] = $this->getPageValue();

        $where = $this->getSystemTableWhere($fieldInfo->crud->table_name_en);

        $where[] = [
            'form_value' => CrudFormEnum::FORM_INPUT,
            'field_name' => $indexFieldName,
            'value'      => $keyword,
        ];

        $model = $this->model(crudId: $fieldInfo->association_crud_id)->searchWhere(where: $where, orderBy: 'id');

        $count = $model->count();
        $list  = $model->forPage($page, $limit)
            ->when(count($with), fn ($q) => $q->with($with))
            ->select($select)->get()->toArray();

        $list = $this->otherWithList($otherWith, $list, $fieldSelect);

        return compact('list', 'count');
    }

    /**
     * 获取表单提交的字段信息.
     * @return mixed
     * @throws BindingResolutionException
     * @email 136327134@qq.com
     * @date 2024/3/13
     */
    public function getFormField($crudId)
    {
        $formFields = app()->make(SystemCrudService::class)->value($crudId, 'form_fields');
        if (! $formFields) {
            return [];
        }

        $scheduleId = [];
        foreach ($formFields as $field) {
            if (strstr($field, '.') !== false) {
                [$tableName, $fieldName] = explode('.', $field);
                $cid                     = app()->make(SystemCrudService::class)->value(['table_name_en' => $tableName], 'id');
                $scheduleId[$cid][]      = $fieldName;
            } else {
                $scheduleId[$crudId][] = $field;
            }
        }

        $list = [];
        foreach ($scheduleId as $k => $fields) {
            $res = app()->make(SystemCrudFieldService::class)->fieldByList((int) $k, $fields);
            if ($res) {
                $list = array_merge($list, $res);
            }
        }

        return $list;
    }

    /**
     * 根据字段获取数据字段详情.
     * @return array
     * @throws BindingResolutionException
     * @email 136327134@qq.com
     * @date 2024/4/9
     */
    public function fieldsByFieldInfo(array $fields, int $crudId)
    {
        $scheduleId = [];
        foreach ($fields as $field) {
            if (strstr($field, '.') !== false) {
                [$tableName, $fieldName] = explode('.', $field);
                $cid                     = app()->make(SystemCrudService::class)->value(['table_name_en' => $tableName], 'id');
                $scheduleId[$cid][]      = $fieldName;
            } else {
                $scheduleId[$crudId][] = $field;
            }
        }

        $list = [];
        foreach ($scheduleId as $k => $fields) {
            $res = app()->make(SystemCrudFieldService::class)->fieldByList((int) $k, $fields,0);
            if ($res) {
                $list = array_merge($list, $res);
            }
        }

        return $list;
    }

    /**
     * 设置视图搜索.
     * @return array
     * @throws BindingResolutionException
     * @email 136327134@qq.com
     * @date 2024/4/9
     */
    public function setViewSearch(array $viewSearch, int $crudId, string $keyName = 'field_name')
    {
        $fields = array_column($viewSearch, $keyName);
        if (! $fields) {
            return [];
        }

        $fieldList = $this->fieldsByFieldInfo($fields, $crudId);
        foreach ($viewSearch as &$search) {
            foreach ($fieldList as $item) {
                $key = $item['crud']['id'] == $crudId ?
                    (str_contains($search[$keyName], '.') ? $item['crud']['table_name_en'] . '.' . $item['field_name_en'] : $item['field_name_en'])
                    : $item['crud']['table_name_en'] . '.' . $item['field_name_en'];
                if ($key === $search[$keyName]) {
                    $search['field_type'] = $item['field_type'];
                    $search['form_value'] = $item['form_value'];
                    break;
                }
            }
            if ($keyName !== 'field_name') {
                $search['field_name'] = $search[$keyName];
                unset($search[$keyName]);
            }
        }
        return $viewSearch;
    }

    /**
     * 保存数据.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     * @email 136327134@qq.com
     * @date 2024/3/13
     */
    public function saveModule(SystemCrud $crud, array $data, array $systemData = [], int $crudId = 0, int $crudValue = 0)
    {
        $masterData   = [];
        $scheduleData = [];
        $scheduleIds  = [];

        $model = $this->model(crudId: $crud->id);

        foreach ($data as $key => $item) {
            $value = $this->setValueAttribute($item['item']['form_value'], $item['value']);

            if ($item['item']['crud_id'] === $crud->id) {
                $masterData[$item['item']['field_name_en']] = $value;

                if ($item['item']['is_uniqid'] && $model->count([$item['item']['field_name_en'] => $value])) {
                    throw $this->exception('字段'.$item['item']['field_name_en'].'的值不能重复');
                }

            } else {
                $scheduleIds[$item['item']['crud']['table_name_en']][] = [
                    'crud_id'           => $item['item']['crud']['id'],
                    'value'             => $value,
                    'form_field_uniqid' => $key,
                    'field_name_en'     => $item['item']['field_name_en'],
                ];

                $scheduleData[$item['item']['crud']['table_name_en']][$item['item']['field_name_en']] = $value;
            }

        }

        if (empty($masterData['user_id'])) {
            $masterData['user_id'] = $systemData['uid'];
        }
        if (empty($masterData['owner_user_id'])) {
            $masterData['owner_user_id'] = $systemData['uid'];
        }
        if (empty($masterData['created_at'])) {
            $masterData['created_at'] = date('Y-m-d H:i:s');
        }

        // 数据验证事件处理
        $this->handleEvent(crud: $crud, action: CrudTriggerEnum::TRIGGER_CREATED, data: $data, scheduleData: $scheduleData, isDataCheck: true);


        // 有关联的主体在详情中进行添加
        if ($crudId && $crudValue) {
            $fieldName              = app()->make(SystemCrudFieldService::class)->value(['crud_id' => $crud->id, 'association_crud_id' => $crudId], 'field_name_en');
            $masterData[$fieldName] = $crudValue;
        }

        [$masterId, $scheduleData] = $this->transaction(function () use ($model, $systemData, $crud, $masterData, $scheduleData) {
            $masterId = $model->getIncId($masterData);

            foreach ($scheduleData as $tableName => &$item) {
                $item[$crud->table_name_en . '_id'] = $masterId;

                if (empty($item['user_id'])) {
                    $item['user_id'] = $systemData['uid'];
                }
                if (empty($item['owner_user_id'])) {
                    $item['owner_user_id'] = $systemData['uid'];
                }
                if (empty($item['created_at'])) {
                    $item['created_at'] = date('Y-m-d H:i:s');
                }

                DB::table($tableName)->insert($item);
            }

            return [$masterId, $scheduleData];
        });
        // 处理事件
        $this->handleEvent($crud, CrudTriggerEnum::TRIGGER_CREATED, $masterId, $masterData, $scheduleData);
    }

    /**
     * 更新数据.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     * @email 136327134@qq.com
     * @date 2024/3/13
     */
    public function updateModule(SystemCrud $crud, int $id, array $data, array $systemData = [])
    {
        $masterData   = [];
        $scheduleData = [];

        $model = $this->model(crudId: $crud->id);

        foreach ($data as $item) {
            $value = $this->setValueAttribute($item['item']['form_value'], $item['value']);

            if ($item['item']['crud_id'] === $crud->id) {
                $masterData[$item['item']['field_name_en']] = $value;

                if ($item['item']['is_uniqid'] && $model->count([$item['item']['field_name_en'] => $value])) {
                    throw $this->exception('字段'.$item['item']['field_name_en'].'的值不能重复');
                }

            } else {
                $scheduleData[$item['item']['crud']['table_name_en']][$item['item']['field_name_en']] = $value;
            }
        }

        if (empty($masterData['update_user_id'])) {
            $masterData['update_user_id'] = $systemData['uid'];
        }

        // 数据验证事件处理
        $this->handleEvent(crud: $crud, action: CrudTriggerEnum::TRIGGER_UPDATED, id: $id, data: $data, scheduleData: $scheduleData, isDataCheck: true);

        [$masterRes, $scheduleData] = $this->transaction(function () use ($model, $crud, $systemData, $id, $masterData, $scheduleData) {
            $model->update($id, $masterData);

            foreach ($scheduleData as $tableName => $item) {
                if (empty($item['update_user_id'])) {
                    $item['update_user_id'] = $systemData['uid'];
                }
                if (empty($item['created_at'])) {
                    $item['created_at'] = date('Y-m-d H:i:s');
                }

                $db = DB::table($tableName);
                if ($db->where($crud->table_name_en . '_id', $id)->count()) {
                    $db->where($crud->table_name_en . '_id', $id)->update($item);
                } else {
                    $item[$crud->table_name_en . '_id'] = $id;
                    $db->insert($item);
                }
            }

            return [$masterData, $scheduleData];
        });

        // 其他事件处理
        $this->handleEvent($crud, CrudTriggerEnum::TRIGGER_UPDATED, $id, $masterData, $scheduleData);
    }

    /**
     * @return array
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function getFindUniModule(SystemCrud $crud, int $id, int $uid = 0)
    {
        $crudService  = app()->make(SystemCrudService::class);
        $fieldService = app()->make(SystemCrudFieldService::class);
        $formField    = $this->getFormField($crud->id);

        $select      = [];
        $fieldColumn = [];
        foreach ($formField as $item) {
            if ($item['crud']['id'] == $crud->id) {
                $select[]                            = $item['field_name_en'];
                $fieldColumn[$item['field_name_en']] = [
                    'form_value'  => $item['form_value'],
                    'field_name'  => $item['field_name'],
                    'association' => $item['association'] ?? null,
                ];
            }
        }

        array_push($select, 'id');

        $moduleInfo = $this->model(crudId: $crud->id)->get($id, $select)?->toArray();
        if (! $moduleInfo) {
            throw $this->exception('没有查询到' . $crud->table_name . '信息');
        }

        /** @var AdminService $adminService */
        $adminService = app()->make(AdminService::class)->getModel(false);
        $frameService = app()->make(FrameService::class);

        foreach ($moduleInfo as $key => $value) {
            $formValue = $fieldColumn[$key] ?? null;
            if (! $formValue) {
                continue;
            }

            switch ($key) {
                case 'user_id':
                case 'update_user_id':
                case 'owner_user_id':
                    if ($value) {
                        $moduleInfo[$key] = $adminService->where('id', $value)->select(['id', 'name'])->first()?->toArray();
                    }
                    break;
                case 'frame_id':
                    if ($value) {
                        $moduleInfo[$key] = $frameService->get($value, ['id', 'name'])?->toArray();
                    }
                    break;
            }

            if ($formValue['association']) {
                $fieldName = $fieldService->value(['crud_id' => $formValue['association']['id'], 'is_main' => 1], 'field_name_en');
                if (! $fieldName) {
                    continue;
                }
                $name = $this->dao->setTableName($formValue['association']['table_name_en'])->value($value, $fieldName);
                if ($name) {
                    $moduleInfo[$key] = ['id' => $value, 'name' => $name];
                }
            }
        }

        unset($formValue, $key, $value);

        // 获取附表信息
        $scheduleId = $crudService->value(['crud_id' => $crud->id], 'id');
        if ($scheduleId) {
            $select       = ['crud_id', 'is_form', 'data_dict_id', 'options', 'id', 'field_name_en', 'field_name', 'form_value'];
            $scheduleCrud = $crudService->get($scheduleId, ['id', 'table_name_en'], [
                'field' => fn ($q) => $q->where('is_form', 1)->select($select),
            ])?->toArray();

            if (! empty($scheduleCrud['field'])) {
                $field = array_column($scheduleCrud['field'], 'field_name_en');

                $scheduleFieldColumn = [];
                foreach ($scheduleCrud['field'] as $item) {
                    $fieldColumn[$scheduleCrud->table_name_en . '@' . $item['field_name_en']] = [
                        'form_value'  => $item['form_value'],
                        'field_name'  => $item['field_name'],
                        'association' => null,
                    ];
                    $scheduleFieldColumn[$item['field_name_en']] = [
                        'form_value'  => $item['form_value'],
                        'field_name'  => $item['field_name'],
                        'association' => null,
                    ];
                }

                $scheduleData = $this->model(crudId: $scheduleCrud['id'])->get([$crud->table_name_en . '_id' => $id], $field)?->toArray();

                if ($scheduleData) {
                    foreach ($scheduleData as $key => $datum) {
                        $formValue = $fieldColumn[$key] ?? null;
                        if (! $formValue) {
                            continue;
                        }
                        $datum = $this->getValueAttribute($formValue['form_value'], $datum);

                        switch ($key) {
                            case 'user_id':
                            case 'update_user_id':
                            case 'owner_user_id':
                                if ($datum) {
                                    $moduleInfo[$scheduleCrud['table_name_en'] . '@' . $key] = app()->make(AdminService::class)->get($datum, ['id', 'name'])?->toArray();
                                }
                                break;
                            case 'frame_id':
                                if ($datum) {
                                    $moduleInfo[$scheduleCrud['table_name_en'] . '@' . $key] = $frameService->get($datum, ['id', 'name'])?->toArray();
                                }
                                break;
                            default:
                                $moduleInfo[$scheduleCrud['table_name_en'] . '@' . $key] = $datum;
                                break;
                        }
                    }
                }
            }
        }

        $associationIds = $fieldService->crudByAssociationIds($crud->id);
        // 关联表信息
        $associationTable = $crudService->getCrudList($associationIds);

        $moduleValue = $this->otherWithList([], [$moduleInfo], $formField)[0];

        $values = [];
        foreach ($moduleValue as $key => $value) {
            if (isset($fieldColumn[$key])) {
                $values[] = [
                    'title'      => $fieldColumn[$key]['field_name'],
                    'form_value' => $fieldColumn[$key]['form_value'],
                    'value'      => $value,
                ];
            }
        }

        foreach ($moduleInfo as $key => $value) {
            $formValue = $fieldColumn[$key] ?? null;
            if (! $formValue) {
                continue;
            }
            $moduleInfo[$key] = $this->getValueAttribute($formValue['form_value'], $value);
        }

        return [
            'values'            => $values,
            'module_info'       => $moduleInfo,
            'association_table' => $associationTable,
        ];
    }

    /**
     * 获取实体数据.
     * @return array
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     * @email 136327134@qq.com
     * @date 2024/3/14
     */
    public function getFindModule(SystemCrud $crud, int $id, int $uid = 0)
    {
        $crudService  = app()->make(SystemCrudService::class);
        $fieldService = app()->make(SystemCrudFieldService::class);
        $formField    = $this->getFormField($crud->id);

        $select      = [];
        $fieldColumn = [];
        foreach ($formField as $item) {
            if ($item['crud']['id'] == $crud->id) {
                $select[]                            = $item['field_name_en'];
                $fieldColumn[$item['field_name_en']] = [
                    'form_value'  => $item['form_value'],
                    'association' => $item['association'] ?? null,
                ];
            }
        }

        array_push($select, 'id');

        $moduleInfo = $this->model(crudId: $crud->id)->get($id, $select)?->toArray();
        if (! $moduleInfo) {
            throw $this->exception('没有查询到' . $crud->table_name . '信息');
        }

        /** @var AdminService $adminService */
        $adminService = app()->make(AdminService::class)->getModel(false);
        $frameService = app()->make(FrameService::class);

        foreach ($moduleInfo as $key => $value) {
            $formValue = $fieldColumn[$key] ?? null;
            if (! $formValue) {
                continue;
            }
            $moduleInfo[$key] = $this->getValueAttribute($formValue['form_value'], $value);

            switch ($key) {
                case 'user_id':
                case 'update_user_id':
                case 'owner_user_id':
                    if ($value) {
                        $moduleInfo[$key] = $adminService->where('id', $value)->select(['id', 'name'])->first()?->toArray();
                    }
                    break;
                case 'frame_id':
                    if ($value) {
                        $moduleInfo[$key] = $frameService->get($value, ['id', 'name'])?->toArray();
                    }
                    break;
            }

            if ($formValue['association']) {
                $fieldName = $fieldService->value(['crud_id' => $formValue['association']['id'], 'is_main' => 1], 'field_name_en');
                if (! $fieldName) {
                    continue;
                }
                $name = $this->dao->setTableName($formValue['association']['table_name_en'])->value($value, $fieldName);
                if ($name) {
                    $moduleInfo[$key] = ['id' => $value, 'name' => $name];
                }
            }
        }
        unset($formValue, $key, $value);

        // 获取附表信息
        $scheduleId = $crudService->value(['crud_id' => $crud->id], 'id');
        if ($scheduleId) {
            $select       = ['crud_id', 'is_form', 'data_dict_id', 'options', 'id', 'field_name_en', 'field_name', 'form_value'];
            $scheduleCrud = $crudService->get($scheduleId, ['id', 'table_name_en'], [
                'field' => fn ($q) => $q->where('is_form', 1)->select($select),
            ])?->toArray();

            if (! empty($scheduleCrud['field'])) {
                $field = array_column($scheduleCrud['field'], 'field_name_en');

                $fieldColumn = [];
                foreach ($scheduleCrud['field'] as $item) {
                    $fieldColumn[$item['field_name_en']] = $item['form_value'];
                }

                $scheduleData = $this->model(crudId: $scheduleCrud['id'])->get([$crud->table_name_en . '_id' => $id], $field)?->toArray();

                if ($scheduleData) {
                    foreach ($scheduleData as $key => $datum) {
                        $formValue = $fieldColumn[$key] ?? null;
                        if (! $formValue) {
                            continue;
                        }
                        $datum = $this->getValueAttribute($formValue, $datum);

                        switch ($key) {
                            case 'user_id':
                            case 'update_user_id':
                            case 'owner_user_id':
                                if ($datum) {
                                    $moduleInfo[$scheduleCrud['table_name_en'] . '@' . $key] = app()->make(AdminService::class)->get($datum, ['id', 'name'])?->toArray();
                                }
                                break;
                            case 'frame_id':
                                if ($datum) {
                                    $moduleInfo[$scheduleCrud['table_name_en'] . '@' . $key] = $frameService->get($datum, ['id', 'name'])?->toArray();
                                }
                                break;
                            default:
                                $moduleInfo[$scheduleCrud['table_name_en'] . '@' . $key] = $datum;
                                break;
                        }
                    }
                }
            }
        }

        $associationIds = $fieldService->crudByAssociationIds($crud->id);
        // 关联表信息
        $associationTable = $crudService->getCrudList($associationIds);

        // 获取审批
        // $approve = app()->make(SystemCrudApproveService::class)->getCrudApproveList($crud->id);

        return [
            'module_info'       => $moduleInfo,
            'association_table' => $associationTable,
            // 'approve'           => $approve
        ];
    }

    /**
     * 删除数据.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     * @email 136327134@qq.com
     * @date 2024/3/14
     */
    public function deleteModule(SystemCrud $crud, int $id)
    {
        $model = $this->model(crudId: $crud->id);

        $moduleInfo = $model->get($id);
        if (! $moduleInfo) {
            throw $this->exception('没有查询到' . $crud->table_name . '信息');
        }

        //        $this->handleEvent(crud: $crud, action: CrudTriggerEnum::TRIGGER_DELETED, id: $id, isDataCheck: true);

        // 处理事件
        $this->handleEvent($crud, CrudTriggerEnum::TRIGGER_DELETED, id: $id);

        $moduleInfo->delete();
    }

    /**
     * 批量删除.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     * @email 136327134@qq.com
     * @date 2024/4/7
     */
    public function batchDeleteModule(SystemCrud $crud, array $ids)
    {
        $model = $this->model(crudId: $crud->id);

        $moduleList = $model->viewSearch(viewSearch: [
            [
                'field_name' => 'id',
                'operator'   => CrudOperatorEnum::OPERATOR_IN,
                'value'      => $ids,
            ],
        ])->select('id')->get()->toArray();
        if (! $moduleList) {
            throw $this->exception('没有查询到' . $crud->table_name . '信息');
        }

        foreach ($moduleList as $item) {
            $this->handleEvent(crud: $crud, action: CrudTriggerEnum::TRIGGER_DELETED, id: $item['id'], isDataCheck: true);

            $model->searchWhere(where: [
                [
                    'field_name' => 'id',
                    'form_value' => CrudFormEnum::FORM_SWITCH,
                    'value'      => $item['id'],
                ],
            ])->delete();

            // 处理事件
            $this->handleEvent($crud, CrudTriggerEnum::TRIGGER_DELETED, $item['id']);
        }
    }

    /**
     * 获取验证规则.
     * @return array
     * @email 136327134@qq.com
     * @date 2024/4/23
     */
    public function getValidationRule(array $item, string $fieldName)
    {
        $message  = [];
        $ruleData = [];
        if (! empty($item['options']['maxlength'])) {
            $ruleData[] = 'max:' . $item['options']['maxlength'];
        }
        if (! empty($item['options']['minlength'])) {
            $ruleData[] = 'min:' . $item['options']['minlength'];
        }
        if (! $item['is_default_value_not_null']) {
            $ruleData[] = 'required';
        }
        if (in_array($item['form_value'], [CrudFormEnum::FORM_FILE, CrudFormEnum::FORM_IMAGE])) {
            $ruleData[] = 'array';
            if (! empty($item['options']['max'])) {
                $ruleData[] = 'max:' . $item['options']['max'];
            }
        }
        if ($ruleData) {
            $validatorFieldName = str_replace('.', '_', $fieldName);

            foreach ($ruleData as $datum) {
                $msg = '';
                if ($datum == 'required') {
                    $msg = $item['field_name'] . '不能为空';
                } elseif (strstr($datum, 'max') !== false) {
                    [$var, $num] = explode(':', $datum);
                    $msg         = $item['field_name'] . '最大长度：' . $num;
                } elseif (strstr($datum, 'max') !== false) {
                    [$var, $num] = explode(':', $datum);
                    $msg         = $item['field_name'] . '最小长度：' . $num;
                } elseif ($datum === 'array') {
                    $msg = $item['field_name'] . '必须为数组';
                }

                $message[$validatorFieldName . '.' . ($var ?? $datum)] = $msg;
                unset($var);
            }
        }

        return [$message, $ruleData];
    }

    /**
     * 执行事件.
     * @return false
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     * @throws \Throwable
     * @email 136327134@qq.com
     * @date 2024/3/14
     */
    public function handleEvent(SystemCrud $crud, string $action, int $id = 0, array $data = [], array $scheduleData = [], bool $isDataCheck = false)
    {
        $eventService = app()->make(SystemCrudEventService::class);

        $eventIds = $eventService->getEventActionIds($crud->id, $isDataCheck);

        if ($eventIds) {
            $eventId = array_shift($eventIds);
            $event   = $eventService->get($eventId);
            if (! $event) {
                return false;
            }

            $eventService->runEvent($crud->id, $action, $event->toArray(), $eventIds, $id, $data, $scheduleData);
        }
    }

    /**
     * 获取看板表格展示字段.
     * @throws BindingResolutionException
     */
    public function getListQueryShowTableField(SystemCrud $crud, array $field = []): array
    {
        if (! $field) {
            return [];
        }

        $fieldNameList = app()->make(SystemCrudFieldService::class)->fieldNameByFieldCrud($field, $crud->table_name_en);

        $fieldList = [];
        foreach ($fieldNameList as $item) {
            foreach ($item['field'] as $field) {
                $fieldList[] = [
                    'crud_id'             => $item['crud_info']['id'],
                    'is_main'             => $field['is_main'],
                    'field_name'          => $field['field_name'],
                    'form_value'          => $field['form_value'],
                    'is_default'          => $field['is_default'],
                    'data_dict_id'        => $field['data_dict_id'],
                    'field_name_en'       => $field['field_name_en'],
                    'association_crud_id' => $field['association_crud_id'],
                    'association_crud'    => $field['association'] ? [
                        'id'                 => $field['association']['id'],
                        'table_name_en'      => $field['association']['table_name_en'],
                        'field_main_name_en' => app()->make(SystemCrudFieldService::class)->value(['crud_id' => $field['association']['id'], 'is_main' => 1], 'field_name_en'),
                    ] : null,
                    'table_name_en' => $item['crud_info']['table_name_en'],
                ];
            }
        }
        return $fieldList;
    }

    /**
     * 列表查询.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function getListQuery(array $showField, SystemCrud $crud, array $defaultWhere, array $orderBy = [], array $viewSearch = [], int $viewSearchBoolean = 0, int $page = 1, int $limit = 10): array
    {
        if (in_array($crud->table_name_en, SystemCrudService::SYSTEM_TABLE_TABLE) && isset($defaultWhere['user_id'])) {
            unset($defaultWhere['user_id']);
        }

        // 获取关联表字段
        $joinField = [];
        foreach ($showField as $item) {
            if (str_contains($item, '.')) {
                $joinField[] = $item;
            }
        }

        $where = [];
        // 获取搜索列表展示字段
        $showField = $this->getListQueryShowTableField($crud, $showField);
        if (! $showField) {
            return [];
        }

        [$select, $with, $otherWith] = $this->getFieldAndWith($showField, $crud->table_name_en, true);

        foreach ($otherWith as &$item) {
            $item['field_name_as'] = $item['table_name_en'] . '_' . $item['field_name_en'];
        }

        // 默认ID倒叙
        $orderBy = $orderBy ?: 'id';

        $join = app()->make(SystemCrudService::class)->getJoinCrudData($crud->id, 1);
        // 附加条件
        if ($viewSearch) {
            $viewSearch = $this->setViewSearch($viewSearch, $crud->id);
        }

        $model = $this->model(crudId: $crud->id)
            ->setJoin($join)
            ->searchWhere($defaultWhere, $where, $viewSearch, $orderBy, $viewSearchBoolean === 0 ? 'or' : 'and');

        $count = $model->count();

        // 拼接表名
        $select = array_map(function ($item) use ($crud) {
            if (str_contains($item, '.')) {
                $tmp = explode('.', $item);
                return $item . ' as ' . $tmp[0] . '_' . $tmp[1];
            }
            return $crud->table_name_en . '.' . $item . ' as ' . $crud->table_name_en . '_' . $item;
        }, array_merge($select, $joinField));

        if (! $select) {
            $select = [$crud->table_name_en . '.*'];
        }

        $list = $model->when(count($with), fn ($q) => $q->with($with))->select($select)->forPage($page, $limit)->get()->toArray();

        $list = $this->otherWithAliasList($otherWith, $list, $showField);
        return $this->listData($list, $count);
    }

    /**
     * 关联字段展示查询和数据转换.
     * @return array
     * @throws BindingResolutionException
     * @email 136327134@qq.com
     * @date 2024/3/12
     */
    public function otherWithAliasList(array $otherWith, array $list, array $formField = [])
    {
        foreach ($otherWith as $newWith) {
            $idName = $newWith['field_name_as'] ?? $newWith['field_name_en'];
            if (! $idName) {
                continue;
            }

            if (! $newWith['association_field_main_name_en']) {
                continue;
            }

            $ids      = array_column($list, $idName);
            $mainName = $newWith['association_field_main_name_en'];

            $withList = DB::table($newWith['association_table_name_en'])->whereIn('id', $ids)
                ->select(['id', $mainName])->get()->toArray();

            $withData = [];

            foreach ($withList as $item) {
                $item                  = (array) $item;
                $withData[$item['id']] = $item[$mainName];
            }

            foreach ($list as $index => $value) {
                $value[$idName] = $withData[$value[$idName]] ?? '';
                $list[$index]   = $value;
            }
        }

        $column = [];
        foreach ($formField as $item) {
            $column[$item['table_name_en'] . '_' . $item['field_name_en']] = [
                'form_value'    => $item['form_value'],
                'data_dict_id'  => $item['data_dict_id'],
                'table_name_en' => $item['table_name_en'] ?? '',
            ];
        }

        foreach ($list as $index => $value) {
            // 处理字段数据类型
            foreach ($value as $k => $v) {
                if (isset($column[$k])) {
                    $value[$k] = $this->getValueAttribute($column[$k]['form_value'], $value[$k]);
                    if ($column[$k]['data_dict_id']) {
                        switch ($column[$k]['form_value']) {
                            case CrudFormEnum::FORM_CHECKBOX:
                            case CrudFormEnum::FORM_TAG:
                            case CrudFormEnum::FORM_CASCADER_RADIO:
                                $dataDict = app()->make(DictDataService::class)
                                    ->idByValues($column[$k]['data_dict_id']);

                                $data = [];
                                if ($column[$k]['form_value'] === CrudFormEnum::FORM_TAG) {
                                    foreach ($value[$k] as $itemId) {
                                        foreach ($dataDict as $item) {
                                            if ($itemId == $item['id']) {
                                                $data[] = $item['name'];
                                            }
                                        }
                                    }
                                } else {
                                    foreach ($dataDict as $item) {
                                        if (is_array($value[$k]) && in_array($item['value'], $value[$k])) {
                                            $data[] = $item['name'];
                                        }
                                    }
                                    // 级联单选只保留最后一个
                                    //                                    if (CrudFormEnum::FORM_CASCADER_RADIO === $column[$k]['form_value']) {
                                    //                                        $data = array_pop($data);
                                    //                                    }
                                }
                                $value[$k] = implode('/', $data);
                                break;
                            case CrudFormEnum::FORM_RADIO:
                                $dataDict = app()->make(DictDataService::class)
                                    ->idByValues($column[$k]['data_dict_id']);

                                $newValue = null;
                                foreach ($dataDict as $item) {
                                    if ($item['value'] == $value[$k]) {
                                        $newValue = $item['name'];
                                    }
                                }
                                $value[$k] = $newValue;
                                break;
                            case CrudFormEnum::FORM_CASCADER:
                                if ($value[$k]) {
                                    $dataDict = app()->make(DictDataService::class)
                                        ->idByValues($column[$k]['data_dict_id']);

                                    $data = [];
                                    foreach ($value[$k] as $v) {
                                        $vv = [];
                                        foreach ($dataDict as $item) {
                                            if (in_array($item['value'], $v)) {
                                                $vv[] = $item['name'];
                                            }
                                        }
                                        $data[] = implode('/', $vv);
                                    }

                                    $value[$k] = $data;
                                }
                                break;
                        }
                    } elseif ($column[$k]['form_value'] == CrudFormEnum::FORM_CASCADER_ADDRESS && is_array($value[$k])) {
                        $dataDict = app()->make(DictDataService::class)
                            ->idsByValues($value[$k]);

                        $data = [];
                        foreach ($value[$k] as $itemId) {
                            foreach ($dataDict as $item) {
                                if ($itemId == $item['id']) {
                                    $data[] = $item['name'];
                                }
                            }
                        }

                        $value[$k] = $data;
                    } elseif ($column[$k]['form_value'] == CrudFormEnum::FORM_TAG && ! $column[$k]['data_dict_id']) {
                        $value[$k] = app()->make(ClientLabelService::class)->idByValue($value[$k]);
                    } else {
                        switch ($k) {
                            case 'user_id':
                                $value[$k] = $value['create_user']['name'] ?? $value[$k];
                                break;
                            case 'update_user_id':
                                $value[$k] = $value['update_user']['name'] ?? $value[$k];
                                break;
                            case 'frame_id':
                                $value[$k] = $value['owner_frame']['name'] ?? $value[$k];
                                break;
                            case 'owner_user_id':
                                $value[$k] = $value['owner_user']['name'] ?? $value[$k];
                                break;
                        }
                    }
                }
            }
            $list[$index] = $value;
        }

        return $list;
    }

    /**
     * 拆分别名.
     * @return array|string[]
     */
    public function splitAliasName(string $field): array
    {
        if (substr_count($field, '_') > 1) {
            if (preg_match('/^([^_]*)_([^_]*.*)$/', $field, $matches)) {
                return [$matches[1], $matches[2]];
            }
        }
        return explode('_', $field);
    }

    /**
     * 获取系统默认的搜索条件.
     * @return array
     */
    protected function getSystemTableWhere(string $tableName)
    {
        $where = [];
        switch ($tableName) {
            case SystemCrudService::SYSTEM_TABLE_TABLE_USER:
            case SystemCrudService::SYSTEM_TABLE_TABLE_FRAME:
                $where[] = [
                    'field_name' => 'status',
                    'form_value' => CrudFormEnum::FORM_INPUT_NUMBER,
                    'value'      => 1,
                ];
                break;
        }

        return $where;
    }

    /**
     * 关联字段展示查询和数据转换.
     * @return array
     * @throws BindingResolutionException
     * @email 136327134@qq.com
     * @date 2024/3/12
     */
    protected function otherWithList(array $otherWith, array $list, array $formField = [], bool $uni = false)
    {
        // 处理一对一关联字段展示
        foreach ($otherWith as $newWith) {
            $idName   = $newWith['field_name_en'];
            $ids      = array_column($list, $idName);
            $mainName = $newWith['association_field_main_name_en'];
            if (! $mainName) {
                continue;
            }

            $select = ['id', $mainName];
            if ($newWith['association_table_name_en'] === SystemCrudService::SYSTEM_TABLE_TABLE_USER) {
                array_push($select, ['avatar']);
            }
            $withList = DB::table($newWith['association_table_name_en'])->whereIn('id', $ids)
                ->select(['id', $mainName])->get()->toArray();

            $withData = [];
            foreach ($withList as $item) {
                $item = (array) $item;
                if ($uni) {
                    $withData[$item['id']] = $newWith['association_table_name_en'] === SystemCrudService::SYSTEM_TABLE_TABLE_USER ? $item : $item[$mainName];
                } else {
                    $withData[$item['id']] = $item[$mainName];
                }
            }

            foreach ($list as $index => $value) {
                $value[$idName] = $withData[$value[$idName]] ?? '';
                $list[$index]   = $value;
            }
        }

        $column = [];
        foreach ($formField as $item) {
            $column[$item['field_name_en']] = [
                'form_value'   => $item['form_value'],
                'data_dict_id' => $item['data_dict_id'],
            ];
        }

        foreach ($list as $index => $value) {
            // 处理字段数据类型
            foreach ($value as $k => $v) {
                if (isset($column[$k])) {
                    $value[$k] = $this->getValueAttribute($column[$k]['form_value'], $value[$k]);

                    if ($column[$k]['data_dict_id']) {
                        switch ($column[$k]['form_value']) {
                            case CrudFormEnum::FORM_CHECKBOX:
                            case CrudFormEnum::FORM_TAG:
                            case CrudFormEnum::FORM_CASCADER_RADIO:
                                $dataDict = app()->make(DictDataService::class)
                                    ->idByValues($column[$k]['data_dict_id']);

                                $data = [];
                                if ($column[$k]['form_value'] === CrudFormEnum::FORM_TAG) {
                                    foreach ($value[$k] as $itemId) {
                                        foreach ($dataDict as $item) {
                                            if ($itemId == $item['id']) {
                                                $data[] = $item['name'];
                                            }
                                        }
                                    }
                                } else {
                                    foreach ($dataDict as $item) {
                                        if (is_array($value[$k]) && in_array($item['value'], $value[$k])) {
                                            $data[] = $item['name'];
                                        }
                                    }
                                    // 级联单选只保留最后一个
                                    //                                    if (CrudFormEnum::FORM_CASCADER_RADIO === $column[$k]['form_value']) {
                                    //                                        $data = array_pop($data);
                                    //                                    }
                                }
                                $value[$k] = $data;
                                break;
                            case CrudFormEnum::FORM_RADIO:
                                $dataDict = app()->make(DictDataService::class)
                                    ->idByValues($column[$k]['data_dict_id']);

                                $newValue = null;
                                foreach ($dataDict as $item) {
                                    if ($item['value'] == $value[$k]) {
                                        $newValue = $item['name'];
                                    }
                                }
                                $value[$k] = $newValue;
                                break;
                            case CrudFormEnum::FORM_CASCADER:
                                if ($value[$k]) {
                                    $dataDict = app()->make(DictDataService::class)
                                        ->idByValues($column[$k]['data_dict_id']);

                                    $data = [];
                                    foreach ($value[$k] as $v) {
                                        $vv = [];
                                        foreach ($dataDict as $item) {
                                            if (in_array($item['value'], (array) $v)) {
                                                $vv[] = $item['name'];
                                            }
                                        }
                                        $data[] = implode('/', $vv);
                                    }

                                    $value[$k] = $data;
                                }
                                break;
                        }
                    } elseif ($column[$k]['form_value'] == CrudFormEnum::FORM_CASCADER_ADDRESS && is_array($value[$k])) {
                        $dataDict = app()->make(DictDataService::class)
                            ->idsByValues($value[$k]);

                        $data = [];
                        foreach ($value[$k] as $itemId) {
                            foreach ($dataDict as $item) {
                                if ($itemId == $item['id']) {
                                    $data[] = $item['name'];
                                }
                            }
                        }

                        $value[$k] = $data;
                    } elseif ($column[$k]['form_value'] == CrudFormEnum::FORM_TAG && ! $column[$k]['data_dict_id']) {
                        $value[$k] = app()->make(ClientLabelService::class)->idByValue($value[$k]);
                    } else {
                        switch ($k) {
                            case 'user_id':
                                $value[$k] = $value['create_user']['name'] ?? $value[$k];
                                break;
                            case 'update_user_id':
                                $value[$k] = $value['update_user']['name'] ?? $value[$k];
                                break;
                            case 'frame_id':
                                $value[$k] = $value['owner_frame']['name'] ?? $value[$k];
                                break;
                            case 'owner_user_id':
                                $value[$k] = $value['owner_user']['name'] ?? $value[$k];
                                break;
                        }
                    }
                }
            }

            if ($uni && isset($value['created_at'])) {
                $value['created_at'] = date('Y/m/d', strtotime($value['created_at']));
            }

            $list[$index] = $value;
        }

        return $list;
    }
}
