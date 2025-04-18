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

namespace App\Http\Controller\AdminApi\Module;

use App\Constants\Crud\CrudEventEnum;
use App\Constants\Crud\CrudFormEnum;
use App\Constants\Crud\CrudUpdateEnum;
use App\Http\Controller\AdminApi\AuthController;
use App\Http\Requests\Crud\SystemCrudEventRequest;
use App\Http\Requests\Crud\SystemCrudTableRequest;
use App\Http\Service\Crud\CrudModuleService;
use App\Http\Service\Crud\SystemCrudCateService;
use App\Http\Service\Crud\SystemCrudEventService;
use App\Http\Service\Crud\SystemCrudFieldService;
use App\Http\Service\Crud\SystemCrudFormService;
use App\Http\Service\Crud\SystemCrudService;
use App\Http\Service\Crud\SystemCrudTableService;
use App\Http\Service\Message\MessageTemplateService;
use crmeb\services\expressionLanguage\ExpressionLanguage;
use Illuminate\Contracts\Container\BindingResolutionException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Spatie\RouteAttributes\Attributes\Delete;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Prefix;
use Spatie\RouteAttributes\Attributes\Put;

/**
 * Class CrudController.
 * @email 136327134@qq.com
 * @date 2024/3/6
 */
#[Prefix('ent/crud')]
#[Middleware(['auth.admin', 'ent.auth', 'ent.log'])]
class CrudController extends AuthController
{
    /**
     * Crud constructor.
     */
    public function __construct(SystemCrudService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    /**
     * 获取表列表.
     * @return mixed
     * @throws BindingResolutionException
     * @email 136327134@qq.com
     * @date 2024/2/27
     */
    #[Get('database/list', '数据表列表')]
    public function index()
    {
        $where = $this->request->getMore([
            ['table_name', ''],
            ['cate_id', 0],
        ]);
        $where['crud_id'] = 0;
        return $this->success($this->service->getCrudTableList($where));
    }

    /**
     * 获取实体和应用组合成的数据.
     * @return mixed
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Get('database/tree', '获取实体和应用组合成的数据')]
    public function getCrudTree()
    {
        return $this->success($this->service->getCrudTree());
    }

    /**
     * 创建表.
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \Throwable
     * @throws BindingResolutionException
     * @email 136327134@qq.com
     * @date 2024/2/27
     */
    #[Post('database/create', '数据表创建')]
    public function createTable()
    {
        $data = $this->request->postMore([
            ['table_name', ''],
            ['table_name_en', ''],
            ['crud_id', 0],
            ['cate_ids', []],
            ['is_update_form', 0],
            ['is_update_table', 0],
            ['info', ''],
            ['show_comment', 0],
            ['comment_title', ''],
            ['show_log', 0],
        ]);
        if (! $data['table_name']) {
            return $this->fail('请输入表名');
        }
        $pattern = '/^[A-Za-z_]{1,100}$/';
        if (! preg_match($pattern, $data['table_name_en'])) {
            return $this->fail('表名不符合规范，应为字母和下划线的组合');
        }
        $this->service->saveCrudTable($data, auth('admin')->id());
        return $this->success('创建成功');
    }

    /**
     * 修改表.
     * @return mixed
     * @throws \ReflectionException
     * @throws BindingResolutionException
     * @email 136327134@qq.com
     * @date 2024/2/27
     */
    #[Put('database/update/{id}', '数据表修改')]
    public function updateTable($id)
    {
        $data = $this->request->postMore([
            ['table_name', ''],
            ['is_update_form', 0],
            ['is_update_table', 0],
            ['show_comment', 0],
            ['comment_title', ''],
            ['show_log', 0],
            ['info', ''],
            ['cate_ids', []],
        ]);
        if (! $data['table_name']) {
            return $this->fail('请输入表名');
        }
        $this->service->updateCrudTable((int) $id, $data);
        return $this->success('修改成功');
    }

    /**
     * 复制实体.
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     * @throws BindingResolutionException
     * @email 136327134@qq.com
     * @date 2024/3/5
     */
    #[Post('database/copy/{id}', '数据表复制')]
    public function copyCrud($id)
    {
        if (! $id) {
            return $this->fail('缺少参数');
        }
        $data = $this->request->postMore([
            ['table_name', ''],
            ['table_name_en', ''],
            ['crud_id', 0],
            ['cate_ids', []],
            ['info', ''],
        ]);
        $this->service->copyCrud($id, $data);
        return $this->success('复制成功');
    }

    /**
     * 删除表.
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     * @throws BindingResolutionException
     * @email 136327134@qq.com
     * @date 2024/2/27
     */
    #[Delete('database/del/{id}', '数据表删除')]
    public function deleteTable($id)
    {
        if (! $id) {
            return $this->fail('缺少参数');
        }
        $this->service->deleteCrudTable((int) $id);
        return $this->success('删除成功');
    }

    /**
     * 获取实体信息.
     * @return mixed
     * @email 136327134@qq.com
     * @date 2024/3/8
     */
    #[Get('database/info/{id}', '数据表信息')]
    public function getTableInfo($id)
    {
        if (! $id) {
            return $this->fail('缺少参数');
        }

        $crudInfo = $this->service->get($id, ['*', 'comment_title as comment_name']);
        if (! $crudInfo) {
            return $this->fail('没有查询到实体信息');
        }
        return $this->success($crudInfo->toArray());
    }

    /**
     * 添加字段.
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     * @throws BindingResolutionException
     * @email 136327134@qq.com
     * @date 2024/2/27
     */
    #[Post('field/save', '数据表字段添加')]
    public function saveField()
    {
        $data = $this->request->postMore([
            ['crud_id', 0],
            ['value', ''],
            ['field_name', ''],
            ['field_name_en', ''],
            ['is_default_value_not_null', 0],
            // ['is_table_show_row', 0],
            ['comment', ''],
            ['data_dict_id', 0],
            ['association_crud_id', 0],
            ['association_field_names', []],
            ['options', []],
            ['create_modify', 1],
            ['update_modify', 1],
            ['is_uniqid', 0],
        ]);
        if (! $data['crud_id']) {
            return $this->fail('缺少参数');
        }
        if (! $data['value']) {
            return $this->fail('请输选择数据表字段类型');
        }
        if (! $data['field_name']) {
            return $this->fail('请输入字段名');
        }
        $pattern = '/^[A-Za-z_]{1,100}$/';
        if (! preg_match($pattern, $data['field_name_en'])) {
            return $this->fail('字段名不符合规范，应为字母和下划线的组合');
        }
        if (in_array($data['value'], [
            CrudFormEnum::FORM_RADIO,
            CrudFormEnum::FORM_CASCADER_RADIO,
            CrudFormEnum::FORM_CHECKBOX,
            CrudFormEnum::FORM_CASCADER,
            CrudFormEnum::FORM_SELECT,
        ]) && ! $data['data_dict_id']) {
            return $this->fail('请选择数据字典');
        }
        $this->service->addField(
            (int) $data['crud_id'],
            $data['value'],
            $data['field_name'],
            $data['field_name_en'],
            (bool) $data['is_default_value_not_null'],
            true,
            $data['comment'] ?: $data['field_name'],
            (int) $data['data_dict_id'],
            (int) $data['association_crud_id'],
            (array) $data['association_field_names'],
            (array) $data['options'],
            (int) $data['create_modify'],
            (int) $data['update_modify'],
            (bool) $data['is_uniqid']
        );
        return $this->success('添加字段成功');
    }

    /**
     * 设置主展示字段.
     * @return mixed
     * @throws BindingResolutionException
     * @email 136327134@qq.com
     * @date 2024/3/1
     */
    #[Put('field/main/{id}', '数据表字段设置主展示字段')]
    public function mainField(SystemCrudFieldService $service, $id)
    {
        if (! $id) {
            return $this->fail('缺少参数');
        }
        $fieldInfo = $service->get($id);
        if (! $fieldInfo) {
            return $this->fail('修改的字段信息不存在');
        }
        if ($fieldInfo->form_value !== 'input') {
            return $this->fail('只有文本框才可以设置为主展示字段');
        }
        $service->updateMain($fieldInfo->crud_id, $id);
        $fieldInfo->is_main = 1;
        $fieldInfo->save();
        event('system.crud');
        return $this->success('设置成功');
    }

    /**
     * 修改字段.
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws BindingResolutionException
     * @email 136327134@qq.com
     * @date 2024/2/27
     */
    #[Put('field/update/{id}', '数据表字段修改')]
    public function updateField($id)
    {
        $data = $this->request->postMore([
            ['field_name', ''],
            ['is_default_value_not_null', 0],
            //            ['is_table_show_row', 0],
            ['comment', ''],
            ['data_dict_id', 0],
            ['association_crud_id', 0],
            ['association_field_names', []],
            ['options', []],
            ['create_modify', 1],
            ['update_modify', 1],
            ['is_uniqid', 0],
        ]);
        if (! $id) {
            return $this->fail('缺少参数');
        }
        $value = app()->make(SystemCrudFieldService::class)->value($id, 'form_value');
        if (in_array($value, [
            CrudFormEnum::FORM_RADIO,
            CrudFormEnum::FORM_CASCADER_RADIO,
            CrudFormEnum::FORM_CHECKBOX,
            CrudFormEnum::FORM_CASCADER,
            CrudFormEnum::FORM_SELECT,
        ]) && ! $data['data_dict_id']) {
            return $this->fail('请选择数据字典');
        }
        $this->service->updateField(
            (int) $id,
            $data['field_name'],
            (bool) $data['is_default_value_not_null'],
            true,
            (int) $data['data_dict_id'],
            (array) $data['association_field_names'],
            (array) $data['options'],
            (int) $data['create_modify'],
            (int) $data['update_modify'],
            (bool) $data['is_uniqid']
        );
        return $this->success('修改字段成功');
    }

    /**
     * 删除字段.
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     * @email 136327134@qq.com
     * @date 2024/2/27
     */
    #[Delete('field/del/{id}', '数据表字段删除')]
    public function deleteField($id)
    {
        if (! $id) {
            return $this->fail('缺少参数');
        }
        $this->service->deleteField((int) $id);
        return $this->success('删除字段成功');
    }

    /**
     * 获取表字段.
     * @return mixed
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     * @email 136327134@qq.com
     * @date 2024/2/27
     */
    #[Get('field/list/{id}', '数据表字段')]
    public function getFieldList(SystemCrudFieldService $service, $id)
    {
        if (! $id) {
            return $this->fail('缺少参数');
        }
        $name = $this->request->get('name', '');
        return $this->success($service->getTableFieldList((int) $id, $name));
    }

    /**
     * 获取字段详情.
     * @return mixed
     * @throws BindingResolutionException
     * @email 136327134@qq.com
     * @date 2024/3/9
     */
    #[Get('field/info/{id}', '数据表字段详情')]
    public function getFieldInfo(SystemCrudFieldService $service, $id)
    {
        if (! $id) {
            return $this->fail('缺少参数');
        }

        $fieldInfo = $service->get($id, ['*'], [
            'association' => fn ($q) => $q->select(['id', 'table_name', 'table_name_en']),
        ]);
        if (! $fieldInfo) {
            return $this->fail('没有查询到字段信息');
        }

        $fieldInfo = $fieldInfo->toArray();

        if ($fieldInfo['association_field_names']) {
            $fieldInfo['association_field_names_list'] = $service->getFieldSelect((int) $fieldInfo['association_crud_id'], $fieldInfo['association_field_names']);
        } else {
            $fieldInfo['association_field_names_list'] = [];
        }

        return $this->success($fieldInfo);
    }

    /**
     * 获取一对一关联字段展示.
     * @return mixed
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     * @email 136327134@qq.com
     * @date 2024/2/27
     */
    #[Get('field/association/{id}', '一对一关联字段展示')]
    public function getAssociationCrudField($id)
    {
        if (! $id) {
            return $this->fail('缺少参数');
        }
        return $this->success($this->service->getAssociationCrudField((int) $id));
    }

    /**
     * 获取表单类型.
     * @return mixed
     * @email 136327134@qq.com
     * @date 2024/2/27
     */
    #[Get('field/type', '表单类型')]
    public function getFieldType()
    {
        return $this->success(SystemCrudService::FORM_TYPE);
    }

    /**
     * 条件搜索类型.
     * @return mixed
     * @email 136327134@qq.com
     * @date 2024/2/27
     */
    #[Get('field/operator', '条件搜索类型')]
    public function getOperatorType()
    {
        return $this->success([
            'operator_string' => SystemCrudService::OPERATOR_TYPE,
            'operator_number' => SystemCrudService::OPERATOR_NUMBER_TYPE,
            'operator_timer'  => SystemCrudService::OPERATOR_TIMER_TYPE,
        ]);
    }

    /**
     * 事件类型.
     * @return mixed
     * @email 136327134@qq.com
     * @date 2024/2/29
     */
    #[Get('event/type', '触发器类型')]
    public function getEventType()
    {
        return $this->success(SystemCrudService::EVENT_TYPE);
    }

    /**
     * 获取聚合类型.
     * @return mixed
     * @email 136327134@qq.com
     * @date 2024/2/29
     */
    #[Get('event/aggregate', '触发器聚合类型')]
    public function getAggregateType()
    {
        return $this->success(SystemCrudService::AGGREGATE_TYPE);
    }

    /**
     * 获取执行动作类型.
     * @return mixed
     * @email 136327134@qq.com
     * @date 2024/2/29
     */
    #[Get('event/action', '触发器执行动作类型')]
    public function getActionType()
    {
        return $this->success(SystemCrudService::ACTION_TYPE);
    }

    /**
     * 获取某个实体下的字段信息和数据字典信息.
     * @return mixed
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     * @email 136327134@qq.com
     * @date 2024/3/6
     */
    #[Get('database/fields/{id}', '某实体下字段和数据字典信息')]
    public function getCrudField(SystemCrudFieldService $service, $id)
    {
        if (! $id) {
            return $this->fail('缺少参数');
        }
        $approve = $this->request->get('approve', 0);
        return $this->success($service->getCrudField((int) $id, (bool) $approve));
    }

    /**
     * 获取触发器详情.
     * @return mixed
     * @throws BindingResolutionException
     * @throws \ReflectionException
     * @email 136327134@qq.com
     * @date 2024/2/29
     */
    #[Get('event/info/{id}', '触发器详情')]
    public function getEventInfo(SystemCrudEventService $service, $id)
    {
        if (! $id) {
            return $this->fail('缺少参数');
        }
        return $this->success($service->getEventInfo((int) $id));
    }

    /**
     * 获取事件内的字段信息.
     * @param int $eventId
     * @return mixed
     * @throws \ReflectionException
     * @throws BindingResolutionException
     * @email 136327134@qq.com
     * @date 2024/3/19
     */
    #[Get('event/crud/{id}/{eventId?}', '触发器详情关联数据')]
    public function getEventCrud($id, $eventId = 0)
    {
        if (! $id) {
            return $this->fail('缺少参数');
        }
        return $this->success($this->service->getEventCrud((int) $id, (int) $eventId));
    }

    /**
     * 表单设置中获取字段和实体信息组合成的表单配置.
     * @return mixed
     * @throws \ReflectionException
     * @throws BindingResolutionException
     * @email 136327134@qq.com
     * @date 2024/3/18
     */
    #[Get('form/list/{id}', '表单设置中获取字段和实体信息组合成的表单配置')]
    public function fieldForm($id)
    {
        if (! $id) {
            return $this->fail('缺少参数');
        }

        return $this->success($this->service->getMainFieldForm((int) $id));
    }

    /**
     * 保存表单信息.
     * @return mixed
     * @throws BindingResolutionException
     * @throws \ReflectionException
     * @email 136327134@qq.com
     * @date 2024/2/27
     */
    #[Post('form/save/{id}', '保存表单信息')]
    public function saveForm(SystemCrudFormService $service, SystemCrudFieldService $fieldService, $id)
    {
        if (! $id) {
            return $this->fail('缺少参数');
        }
        $options       = $this->request->post('options', []);
        $fields        = $this->request->post('fields', []);
        $globalOptions = $this->request->post('global_options', []);
        if (! $fields) {
            return $this->fail('至少选择一个字段进行保存');
        }
        $mastarTableName = $this->service->value($id, 'table_name_en');
        $version         = $service->max(['crud_id' => $id], 'version');

        if ($version) {
            ++$version;
        } else {
            $version = 1;
        }
        $updateCrud = [];
        foreach ($fields as &$field) {
            $field                   = str_replace('@', '.', $field);
            [$tableName, $fieldName] = strstr($field, '.') !== false ? explode('.', $field) : [$mastarTableName, $field];
            if ($tableName !== $mastarTableName) {
                $tableId                = $this->service->value(['table_name_en' => $tableName], 'id');
                $updateCrud[$tableId][] = $fieldName;
            } else {
                $updateCrud[$id][] = $fieldName;
            }
        }
        $service->transaction(function () use ($fieldService, $updateCrud, $fields, $service, $id, $version, $options, $globalOptions) {
            $res = $service->create([
                'crud_id'        => $id,
                'version'        => $version,
                'options'        => $options,
                'fields'         => $fields,
                'global_options' => $globalOptions,
                'is_index'       => 1,
            ]);
            // 把所有数据改为不是表单
            $fieldService->updateForm($updateCrud);
            $fieldService->updateIsForm($updateCrud);
            $service->updateIndex($id, $res->id);
            $this->service->update($id, ['form_fields' => $fields]);
        });
        event('system.crud');
        return $this->success('保存成功');
    }

    /**
     * 表单查看.
     * @return mixed
     * @throws BindingResolutionException
     * @email 136327134@qq.com
     * @date 2024/3/19
     */
    #[Get('form/info/{id}', '表单详情')]
    public function findForm(SystemCrudFormService $service, $id)
    {
        if (! $id) {
            return $this->fail('缺少参数');
        }
        $crudInfo = $this->service->get($id);
        if (! $crudInfo) {
            return $this->success([]);
        }
        $formInfo = [];
        try {
            $formInfo = app()->make(CrudModuleService::class)->getCreateForm($crudInfo)['form_info'];
        } catch (\Throwable) {
        }
        if ((! isset($formInfo['global_options']) || ! $formInfo['global_options']) && $service->count(['crud_id' => $id, 'is_index' => 0])) {
            $formInfo['global_options'] = $service->getGlobalOptions($id);
        }
        return $this->success($formInfo);
    }

    /**
     * 获取视图数据.
     * @return mixed
     * @email 136327134@qq.com
     * @date 2024/4/13
     */
    #[Get('view/info/{id}', '视图详情')]
    public function findView(SystemCrudTableService $service, $id)
    {
        if (! $id) {
            return $this->fail('缺少参数');
        }
        $viewInfo = $service->get(['crud_id' => $id, 'is_index' => 1])?->toArray();
        if (! $viewInfo) {
            $viewInfo = [
                'options'       => [],
                'view_search'   => [],
                'senior_search' => [],
                'show_field'    => [],
            ];
        }
        return $this->success($viewInfo);
    }

    /**
     * 保存视图.
     * @return mixed
     * @throws BindingResolutionException
     * @throws \ReflectionException
     * @email 136327134@qq.com
     * @date 2024/2/27
     */
    #[Post('view/save/{id}', '保存视图')]
    public function saveView(SystemCrudTableRequest $request, SystemCrudTableService $service, $id)
    {
        if (! $id) {
            return $this->fail('缺少参数');
        }
        $searchOptions = $request->post('senior_search'); // 高级搜索条件
        $viewSearch    = $request->post('view_search'); // 视图搜索条件
        $showField     = $request->post('show_field'); // 展示字段
        $options       = $request->post('options'); // 其他配置信息

        $version = $service->max(['crud_id' => $id], 'version');

        if ($version) {
            ++$version;
        } else {
            $version = 1;
        }

        $tableInfo = $service->get(['crud_id' => $id, 'is_index' => 1])?->toArray();

        if ($tableInfo) {
            if (is_null($showField)) {
                $showField = $tableInfo['show_field'];
            }
            if (is_null($searchOptions)) {
                $searchOptions = $tableInfo['senior_search'];
            }
            if (is_null($options)) {
                $options['create'] = $tableInfo['create'] ?? [];
                $options['tab']    = $tableInfo['tab'] ?? [];
            } elseif (! isset($options['create']) || is_null($options['create'])) {
                $options['create'] = $tableInfo['options']['create'] ?? [];
            } elseif (! isset($options['tab']) || is_null($options['tab'])) {
                $options['tab'] = $tableInfo['options']['tab'] ?? [];
            }
        }

        $service->transaction(function () use ($viewSearch, $service, $showField, $id, $version, $options, $searchOptions) {
            $service->update(['crud_id' => $id], ['is_index' => 0]);
            $service->create([
                'crud_id'       => $id,
                'version'       => $version,
                'options'       => $options,
                'view_search'   => $viewSearch,
                'senior_search' => $searchOptions,
                'show_field'    => $showField,
                'is_index'      => 1,
            ]);
        });
        event('system.crud');
        return $this->success('保存成功');
    }

    /**
     * 事件列表.
     * @param int $id
     * @return mixed
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     * @email 136327134@qq.com
     * @date 2024/3/18
     */
    #[Get('event/list/{id?}', '触发器列表')]
    public function eventList(SystemCrudEventService $service, $id = 0)
    {
        $where = [
            'name'    => $this->request->get('name', ''),
            'crud_id' => $this->request->get('crud_id', ''),
            'cate_id' => $this->request->get('cate_id', ''),
        ];

        if ($id) {
            $where['crud_id'] = $id;
        }
        $order = $this->request->get('id_order_dy') ? 'desc' : 'asc';

        return $this->success($service->getList($where, ['id', 'crud_id', 'name', 'event', 'action', 'sort', 'updated_at', 'status'], ['sort' => 'desc', 'id' => $order]));
    }

    /**
     * 保存事件.
     * @return mixed
     * @email 136327134@qq.com
     * @date 2024/2/29
     */
    #[Post('event/save', '触发器保存')]
    public function saveEvent(SystemCrudEventService $service)
    {
        [$crudId, $name, $event] = $this->request->postMore([
            ['crud_id', ''],
            ['name', ''],
            ['event', ''],
        ], true);
        if (! $crudId) {
            return $this->fail('缺少参数');
        }
        if (! $this->service->count(['id' => $crudId])) {
            return $this->fail('没有查询到实体');
        }
        if (! $name) {
            return $this->fail('请输入事件名称');
        }
        if (! $event) {
            return $this->fail('请选择事件类型');
        }

        $eventAll = array_column(SystemCrudService::EVENT_TYPE, 'value');
        if (! in_array($event, $eventAll)) {
            return $this->fail('选择的事件类型不存在');
        }

        if (in_array($event, [
            CrudEventEnum::EVENT_AUTH_APPROVE,
            CrudEventEnum::EVENT_AUTO_REVOKE_APPROVE,
        ]) && $this->service->value($crudId, 'crud_id')) {
            return $this->fail('从表不允许创建审批触发器');
        }

        $res = $service->create([
            'crud_id'                         => $crudId,
            'name'                            => $name,
            'event'                           => $event,
            'status'                          => 1,
            'additional_search_boolean'       => 1,
            'aggregate_target_search_boolean' => 1,
            'aggregate_data_search_boolean'   => 1,
        ]);
        event('system.crud');
        return $this->success('添加成功', ['id' => $res->id]);
    }

    /**
     * 修改事件.
     * @return mixed
     * @throws BindingResolutionException
     * @email 136327134@qq.com
     * @date 2024/2/29
     */
    #[Put('event/update/{id}', '触发器修改')]
    public function updateEvent(SystemCrudEventRequest $request, SystemCrudEventService $service, $id)
    {
        if (! $id) {
            return $this->fail('缺少参数');
        }
        $data = $request->postMore([
            ['name', ''],
            ['timer', 0],
            ['timer_type', 0],
            ['action', []],
            ['target_crud_id', 0],
            ['curl_id', 0],
            ['send_type', 0],
            ['send_user', []],
            ['notify_type', ''],
            ['sms_template_id', ''],
            ['system_status', ''],
            ['sms_status', ''],
            ['work_webhook_url', ''],
            ['work_webhook_status', ''],
            ['ding_webhook_url', ''],
            ['ding_webhook_status', ''],
            ['other_webhook_url', ''],
            ['other_webhook_status', ''],
            ['update_field_options', ''],
            ['sort', 0],
            ['additional_search', []],
            ['additional_search_boolean', 0],
            ['field_options', []],
            ['crud_approve_id', 0],
            ['template', ''],
            ['options', []], // 日程配置
            ['timer_options', []], // 时间配置
            ['aggregate_target_search', []],
            ['aggregate_target_search_boolean', 0],
            ['aggregate_data_search', []],
            ['aggregate_data_field', []],
            ['aggregate_data_search_boolean', 0],
            ['aggregate_field_rule', []],
        ]);
        if (! $data['action']) {
            return $this->fail('请选择执行动作');
        }
        $eventInfo = $service->get($id);
        if (! $eventInfo) {
            return $this->fail('没有查询到事件');
        }
        $data['event']   = $eventInfo->event;
        $data['crud_id'] = $eventInfo->crud_id;
        if (in_array($data['event'], [CrudEventEnum::EVENT_FIELD_UPDATE, CrudEventEnum::EVENT_AUTO_CREATE])
            && ! $data['target_crud_id']) {
            return $this->fail('请选择目标实体');
        }
        if ($data['event'] === CrudEventEnum::EVENT_SEND_NOTICE) {
            if (! $data['template']) {
                return $this->fail('请输入通知内容');
            }
        }
        if ($data['event'] === CrudEventEnum::EVENT_DATA_CHECK) {
            if (! $data['template']) {
                return $this->fail('请输入校验失败模板内容');
            }
        }
        if (! in_array($data['event'], [
            CrudEventEnum::EVENT_GROUP_AGGREGATE,
            CrudEventEnum::EVENT_FIELD_AGGREGATE,
            CrudEventEnum::EVENT_DATA_CHECK,
            CrudEventEnum::EVENT_SEND_NOTICE,
            CrudEventEnum::EVENT_AUTH_APPROVE,
            CrudEventEnum::EVENT_AUTO_REVOKE_APPROVE,
            CrudEventEnum::EVENT_TO_DO_SCHEDULE,
            CrudEventEnum::EVENT_GET_DATA,
        ])) {
            if (! $data['target_crud_id']) {
                return $this->fail('请选择目标实体');
            }
            if (! $data['field_options']) {
                return $this->fail('请选择目标字段');
            }
        }
        if (in_array($data['event'], [
            CrudEventEnum::EVENT_AUTH_APPROVE,
            CrudEventEnum::EVENT_AUTO_REVOKE_APPROVE,
        ]) && ! ['crud_approve_id']) {
            return $this->fail('请选择审批流程');
        }

        // 检测获取数据选择的字段是否是一个列下的字段
        if ($data['event'] === CrudEventEnum::EVENT_GET_DATA) {
            $keys = [];
            foreach ($data['field_options'] as $i => $option) {
                if (isset($option['operator']) && in_array($option['operator'], [CrudUpdateEnum::UPDATE_TYPE_SKIP_VALUE, CrudUpdateEnum::UPDATE_TYPE_FIELD])) {
                    if (! str_contains($option['to_form_field_uniqid'], '*')) {
                        return $this->fail('必须选择带有星号的源字段');
                    }
                    [$newk]      = explode('*', $option['to_form_field_uniqid']);
                    $keys[$newk] = $i;
                }
            }
            if (! $keys) {
                return $this->fail('至少选择一个源字段列表数据');
            }
            if (count($keys) >= 2) {
                return $this->fail('选择的源字段必须为同一个数组下的字段');
            }
        }

        // 检测输入的公式是否有误
        if (in_array($data['event'], [CrudEventEnum::EVENT_FIELD_UPDATE, CrudEventEnum::EVENT_AUTO_CREATE])) {
            $expressionLanguage = app()->make(ExpressionLanguage::class);
            foreach ($data['field_options'] as $option) {
                if ($option['operator'] === CrudUpdateEnum::UPDATE_TYPE_FORMULA_VALUE) {
                    preg_match_all('/\{[a-zA-Z0-9\_\.]+\}/', $option['value'], $fields);
                    $fields = $fields[0] ?? [];
                    foreach ($fields as $value) {
                        $field                      = str_replace(['{', '}', '.'], ['', '', '_'], $value);
                        $option['value']            = str_replace($value, $field, $option['value']);
                        $option['template'][$field] = 1;
                    }
                    if (! empty($option['value'])) {
                        try {
                            $expressionLanguage->evaluate($option['value'], $option['template'] ?? []);
                        } catch (\Throwable $e) {
                            return $this->fail('目标字段“' . ($option['field_name'] ?? $option['form_field_uniqid']) . '”输入的公式错误,错误原因:' . $e->getMessage());
                        }
                    }
                }
            }
        }

        $service->updateEvent((int) $id, $data);
        event('system.crud');
        return $this->success('修改成功');
    }

    /**
     * 修改事件状态
     * @return mixed
     * @throws BindingResolutionException
     * @email 136327134@qq.com
     * @date 2024/3/18
     */
    #[Put('event/status/{id}', '触发器状态修改')]
    public function statusEvent(SystemCrudEventService $service, $id)
    {
        if (! $id) {
            return $this->fail('缺少参数');
        }
        $status = (int) $this->request->post('status', 0);
        $service->update($id, ['status' => $status]);

        app()->make(MessageTemplateService::class)->update(['crud_event_id' => $id], ['status' => $status]);

        event('system.crud');

        return $this->success('修改成功');
    }

    /**
     * 删除事件.
     * @return mixed
     * @throws BindingResolutionException
     * @email 136327134@qq.com
     * @date 2024/2/29
     */
    #[Delete('event/del/{id}', '触发器删除')]
    public function deleteEvent(SystemCrudEventService $service, $id)
    {
        if (! $id) {
            return $this->fail('缺少参数');
        }
        $service->delete($id);

        app()->make(MessageTemplateService::class)->delete(['crud_event_id' => $id]);

        event('system.crud');

        return $this->success('删除成功');
    }

    /**
     * 获取分类.
     * @return mixed
     * @email 136327134@qq.com
     * @date 2024/2/29
     */
    #[Get('cate/list', '实体分类列表')]
    public function getCate(SystemCrudCateService $service)
    {
        return $this->success($service->getList([], ['name', 'id'], [
            'sort' => 'desc', 'id' => 'desc',
        ]));
    }

    /**
     * 保存分类.
     * @return mixed
     * @email 136327134@qq.com
     * @date 2024/2/29
     */
    #[Post('cate/save', '实体分类保存')]
    public function saveCate(SystemCrudCateService $service)
    {
        $data = $this->request->postMore([
            ['cate', []],
        ]);

        foreach ($data['cate'] as $item) {
            if (! $item['name']) {
                return $this->fail('请填写应用名称');
            }
            if ($item['id']) {
                $service->update($item['id'], [
                    'name' => $item['name'],
                    'sort' => $item['sort'],
                ]);
            } else {
                $service->create([
                    'name' => $item['name'],
                    'sort' => $item['sort'],
                ]);
            }
        }

        event('system.crud');

        return $this->success('添加成功');
    }

    /**
     * 删除分类.
     * @return mixed
     * @email 136327134@qq.com
     * @date 2024/2/29
     */
    #[Delete('cate/del/{id}', '实体分类删除')]
    public function deleteCate(SystemCrudCateService $service, $id)
    {
        if (! $id) {
            return $this->fail('缺少参数');
        }

        $service->delete($id);

        event('system.crud');

        return $this->success('删除成功');
    }
}
