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

namespace App\Http\Controller\AdminApi\Module;

use App\Http\Controller\AdminApi\AuthController;
use App\Http\Middleware\AuthCrud;
use App\Http\Model\Crud\SystemCrud;
use App\Http\Requests\ApiRequest;
use App\Http\Service\Crud\CrudModuleService;
use App\Http\Service\Crud\SystemCrudSeniorSearchService;
use App\Http\Service\Crud\SystemCrudService;
use App\Http\Service\Crud\SystemCrudTableUserService;
use crmeb\exceptions\ApiException;
use crmeb\traits\SearchTrait;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Spatie\RouteAttributes\Attributes\Delete;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Prefix;
use Spatie\RouteAttributes\Attributes\Put;

/**
 * Class ModuleController.
 * @email 136327134@qq.com
 * @date 2024/3/1
 */
#[Prefix('ent/crud/module')]
#[Middleware(['auth.admin', 'ent.auth', 'ent.log'])]
class ModuleController extends AuthController
{
    use SearchTrait;

    public function __construct(CrudModuleService $service)
    {
        parent::__construct();
        $this->service = $service;
        $this->middleware([AuthCrud::class])->except([
            'getAssociationField', 'getAssociationList',
            'saveUserTable', 'getCrudInfo',
            'delSeniorSearch', 'getSeniorSearchList',
            'saveSeniorSearch', 'sortSeniorSearch',
        ]);
    }

    /**
     * 获取一对一关联展示字段.
     * @return mixed
     * @throws BindingResolutionException
     * @email 136327134@qq.com
     * @date 2024/3/12
     */
    #[Get('association_field/{id}', '一对一关联展示字段')]
    public function getAssociationField($id)
    {
        if (! $id) {
            return $this->fail('缺少参数');
        }
        return $this->success($this->service->getAssociationField((int) $id));
    }

    /**
     * 获取一对一关联展示列表.
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws BindingResolutionException
     * @email 136327134@qq.com
     * @date 2024/3/12
     */
    #[Get('association_list/{id}', '一对一关联展示列表')]
    public function getAssociationList($id)
    {
        if (! $id) {
            return $this->fail('缺少参数');
        }
        $keyword = $this->request->get('keyword', '');
        return $this->success($this->service->getAssociationList((int) $id, (string) $keyword));
    }

    /**
     * 保存用户相关视图信息和表格展示信息.
     * @return mixed
     * @throws \ReflectionException
     * @throws BindingResolutionException
     * @email 136327134@qq.com
     * @date 2024/3/9
     */
    #[Post('{name}/crud', '保存用户相关视图信息和表格展示信息')]
    public function saveUserTable(SystemCrudTableUserService $service, $name)
    {
        $crudInfo = $this->checkCrud($name);

        [$seniorSearch, $showField, $options] = $this->request->postMore([
            ['senior_search', []],
            ['show_field', []],
            ['options', []],
        ], true);

        $service->saveUserTable($crudInfo->id, auth('admin')->id(), (array) $seniorSearch, (array) $showField, (array) $options);

        return $this->success('保存成功');
    }

    /**
     * 获取视图搜索列表.
     * @param mixed $name
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws BindingResolutionException
     */
    #[Get('{name}/senior/list', '获取视图搜索列表')]
    public function getSeniorSearchList(SystemCrudSeniorSearchService $service, $name)
    {
        $crudInfo = $this->checkCrud($name);
        $title    = $this->request->get('title', '');
        $system   = $this->request->get('system', 0);
        return $this->success($service->getSeniorSearchList($crudInfo, $title, $system ? 0 : auth('admin')->id()));
    }

    /**
     * 保存视图信息.
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws BindingResolutionException
     */
    #[Post('{name}/senior/save', '保存视图信息')]
    public function saveSeniorSearch(ApiRequest $request, SystemCrudSeniorSearchService $service, $name)
    {
        $crudInfo = $this->checkCrud($name);

        $id     = $this->request->get('id', '');
        $system = $this->request->get('system', 0);
        $data   = $request->postMore([
            ['sort', 0],
            ['senior_title', ''],
            ['senior_search', []],
            ['senior_type', 0],
            ['search_boolean', 0],
        ]);

        $data['crud_id'] = $crudInfo->id;

        if (! $data['senior_title']) {
            return $this->fail('缺少视图标题');
        }
        //        if (!$data['senior_search']) {
        //            return $this->fail('缺少视图搜索条件');
        //        }

        if ($id) {
            $service->update($id, $data);
        } else {
            if (! $system) {
                $data['user_id'] = auth('admin')->id();
            }
            if ($service->count() >= 100) {
                return $this->fail('视图搜索条件最多添加100个');
            }
            $service->create($data);
        }

        return $this->success($id ? '修改成功' : '添加成功');
    }

    /**
     * 排序视图.
     * @return mixed
     * @throws BindingResolutionException
     */
    #[Post('{name}/senior/sort', '排序视图')]
    public function sortSeniorSearch(SystemCrudSeniorSearchService $service, $name)
    {
        $crudInfo = $this->checkCrud($name);

        $ids = $this->request->post('id', []);
        if (! $ids) {
            return $this->fail('缺少参数');
        }

        $sort = $service->max(['crud_id' => $crudInfo->id], 'sort');
        $sort = is_null($sort) ? count($ids) + 1 : $sort;
        $sort = $sort < 0 ? count($ids) : $sort;
        foreach ($ids as $id) {
            $service->update($id, ['sort' => $sort]);
            --$sort;
        }
        return $this->success('修改成功');
    }

    /**
     * 删除视图.
     * @return mixed
     * @throws BindingResolutionException
     */
    #[Delete('{name}/senior/del/{id}', '删除视图')]
    public function delSeniorSearch(SystemCrudSeniorSearchService $service, $name, $id)
    {
        $this->checkCrud($name);

        if (! $id) {
            return $this->fail('缺少参数');
        }

        $service->delete($id);

        return $this->success('删除成功');
    }

    /**
     * 获取实体列表字段展示和搜索字段展示.
     * @param int $id
     * @return mixed
     * @throws \ReflectionException
     * @throws BindingResolutionException
     * @email 136327134@qq.com
     * @date 2024/3/12
     */
    #[Get('{name}/crud/info/{id?}', '获取实体列表字段展示和搜索字段展示')]
    public function getCrudInfo($name, $id = 0)
    {
        $crudInfo = $this->checkCrud($name);
        return $this->success($this->service->getCrudInfo($crudInfo, auth('admin')->id(), (int) $id));
    }

    /**
     * 列表展示.
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     * @throws BindingResolutionException
     * @email 136327134@qq.com
     * @date 2024/3/9
     */
    #[Post('{name}/list', '低代码列表')]
    public function index(Request $request, $name)
    {
        $crudInfo = $this->checkCrud($name);

        $this->withScopeFrame('user_id');

        $systemUserId                     = $request->post('system_user_id', []);
        $defaultWhere['show_search_type'] = $request->post('show_search_type', '');
        $defaultWhere['user_id']          = $request->post('user_id', []);
        $defaultWhere['uid']              = auth('admin')->id();
        $postOrderBy                      = $request->post('order_by', []);
        $viewSearch                       = $request->post('view_search', []);
        $viewSearchBoolean                = $request->post('view_search_boolean', 0);
        $keywordDefault                   = $request->post('keyword_default');
        $crudValue                        = $request->post('crud_value', 0);
        $crudId                           = $request->post('crud_id', 0);

        if ($systemUserId && $request->post('scope_frame') === 'all') {
            $defaultWhere['user_id'] = array_merge($defaultWhere['user_id'], $systemUserId);
        }

        $orderBy = [];
        if ($postOrderBy) {
            $orderByField = $this->service->getOrderByField($crudInfo->id);
            foreach ($orderByField as $item) {
                if (isset($postOrderBy[$item['field_name_en']])) {
                    $orderBy[$item['field_name_en']] = $postOrderBy[$item['field_name_en']] ? 'desc' : 'asc';
                }
            }
        }

        if (isset($postOrderBy['default_field_name_en'])) {
            $orderBy = [];
        }

        $viewNewSearch = [];
        foreach ($viewSearch as $search) {
            if ($search['form_field_uniqid']) {
                $viewNewSearch[] = [
                    'field_name' => $search['form_field_uniqid'],
                    'operator'   => $search['operator'],
                    'value'      => $search['value'] ?? '',
                ];
            }
        }
        $viewSearch = $viewNewSearch;

        return $this->success($this->service->getModuleList($request, $crudInfo, $defaultWhere, $orderBy, (array) $viewSearch, (string) $keywordDefault, (int) $viewSearchBoolean, (int) $crudId, (int) $crudValue));
    }

    /**
     * 获取新建页面的表单信息.
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     * @throws BindingResolutionException
     * @email 136327134@qq.com
     * @date 2024/3/12
     */
    #[Get('{name}/create', '低代码创建表单')]
    public function create(Request $request, $name)
    {
        $crudInfo  = $this->checkCrud($name);
        $crudId    = $request->get('crud_id', 0);
        $crudValue = $request->get('crud_value', 0);
        $id        = $request->get('id', 0);
        return $this->success($this->service->getCreateForm($crudInfo, (int) $crudId, (int) $crudValue, (int) $id));
    }

    /**
     * 保存数据.
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     * @throws BindingResolutionException
     * @email 136327134@qq.com
     * @date 2024/3/13
     */
    #[Post('{name}/save', '低代码保存数据')]
    public function save(Request $request, $name)
    {
        $crudInfo = $this->checkCrud($name);
        $crudList = $this->service->getFormField($crudInfo->id);

        if (in_array($crudInfo->table_name_en, app()->get(SystemCrudService::class)->notAllowOperateTable())) {
            return $this->fail('系统默认数据不允许创建');
        }

        $data = $validatorData = $rule = $message = [];

        $crudId    = $request->post('crud_id', 0);
        $crudValue = $request->post('crud_value', 0);

        foreach ($crudList as $item) {
            $fieldName = $item['crud']['id'] === $crudInfo->id ?
                $item['field_name_en'] :
                $item['crud']['table_name_en'] . '.' . $item['field_name_en'];

            $default     = '';
            $association = false;
            if ($item['association_crud_id']) {
                $default     = [];
                $association = true;
            } elseif (in_array($item['field_name_en'], ['user_id', 'update_user_id', 'frame_id', 'owner_user_id'])) {
                $default     = [];
                $association = true;
            }

            $postValue = $request->post(str_replace('.', '@', $fieldName), $default);
            if ($association) {
                $value = $postValue['id'] ?? 0;
            } else {
                $value = $postValue;
            }

            [$itemMsg, $ruleData] = $this->service->getValidationRule($item, $fieldName);

            if ($ruleData) {
                $validatorFieldName                 = str_replace('.', '_', $fieldName);
                $validatorData[$validatorFieldName] = $value;
                $rule[$validatorFieldName]          = implode('|', $ruleData);
                $message                            = array_merge($message, $itemMsg);
            }

            $data[$fieldName] = [
                'value' => $value,
                'post'  => $postValue,
                'item'  => $item,
            ];
        }

        if ($validatorData && $rule && $message) {
            $validator = Validator::make($validatorData, $rule, $message);

            if ($validator->fails()) {
                // 验证失败的处理逻辑
                return $this->fail($validator->errors()->first());
            }
        }

        $this->service->saveModule($crudInfo, $data, [
            'uid' => auth('admin')->id(),
        ], (int) $crudId, (int) $crudValue);

        return $this->success('添加成功');
    }

    /**
     * 更新数据.
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     * @throws BindingResolutionException
     * @email 136327134@qq.com
     * @date 2024/3/13
     */
    #[Put('{name}/update/{id}', '低代码更新数据')]
    public function update(Request $request, $name, $id)
    {
        $crudInfo = $this->checkCrud($name);
        if (in_array($crudInfo->table_name_en, app()->get(SystemCrudService::class)->notAllowOperateTable())) {
            return $this->fail('系统默认数据不允许更新');
        }
        if (! $id) {
            return $this->fail('缺少参数');
        }
        $crudList = $this->service->getFormField($crudInfo->id);
        $data     = $validatorData = $rule = $message = [];
        foreach ($crudList as $item) {
            $fieldName = $item['crud']['id'] === $crudInfo->id ?
                $item['field_name_en'] :
                $item['crud']['table_name_en'] . '.' . $item['field_name_en'];
            $default     = '';
            $association = false;
            if ($item['association_crud_id']) {
                $default     = [];
                $association = true;
            } elseif (in_array($item['field_name_en'], ['user_id', 'update_user_id', 'frame_id', 'owner_user_id'])) {
                $default     = [];
                $association = true;
            }
            $postValue = $request->post(str_replace('.', '@', $fieldName), $default);
            if ($association) {
                $value = $postValue['id'] ?? 0;
            } else {
                $value = $postValue;
            }
            [$itemMsg, $ruleData] = $this->service->getValidationRule($item, $fieldName);
            if ($ruleData) {
                $validatorFieldName                 = str_replace('.', '_', $fieldName);
                $validatorData[$validatorFieldName] = $value;
                $rule[$validatorFieldName]          = implode('|', $ruleData);
                $message                            = array_merge($message, $itemMsg);
            }
            $data[$fieldName] = [
                'value' => $value,
                'post'  => $postValue,
                'item'  => $item,
            ];
        }
        if ($validatorData && $rule && $message) {
            $validator = Validator::make($validatorData, $rule, $message);

            if ($validator->fails()) {
                // 验证失败的处理逻辑
                return $this->fail($validator->errors()->first());
            }
        }
        $this->service->updateModule($crudInfo, (int) $id, $data, [
            'uid' => auth('admin')->id(),
        ]);
        return $this->success('修改成功');
    }

    /**
     * 获取实体数据.
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     * @throws BindingResolutionException
     * @email 136327134@qq.com
     * @date 2024/3/14
     */
    #[Get('{name}/find/{id}', '低代码获取实体数据')]
    public function find($name, $id)
    {
        $crudInfo = $this->checkCrud($name);
        if (! $id) {
            return $this->fail('缺少参数');
        }
        return $this->success($this->service->getFindModule($crudInfo, (int) $id, auth('admin')->id()));
    }

    /**
     * 删除数据.
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     * @throws BindingResolutionException
     * @email 136327134@qq.com
     * @date 2024/3/14
     */
    #[Delete('{name}/delete/{id}', '低代码删除数据')]
    public function delete($name, $id)
    {
        $crudInfo = $this->checkCrud($name);

        if (in_array($crudInfo->table_name_en, app()->get(SystemCrudService::class)->notAllowOperateTable())) {
            return $this->fail('系统默认数据不允许删除');
        }

        if (! $id) {
            return $this->fail('缺少参数');
        }

        $this->service->deleteModule($crudInfo, (int) $id);

        return $this->success('删除成功');
    }

    /**
     * 批量删除.
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     * @throws BindingResolutionException
     * @email 136327134@qq.com
     * @date 2024/4/7
     */
    #[Delete('{name}/batchdelete', '低代码批量删除')]
    public function batchDelete($name)
    {
        $crudInfo = $this->checkCrud($name);

        if (in_array($crudInfo->table_name_en, app()->get(SystemCrudService::class)->notAllowOperateTable())) {
            return $this->fail('系统默认数据不允许删除');
        }

        $ids = $this->request->post('ids', []);
        if (! $ids) {
            return $this->fail('缺少参数');
        }

        $this->service->batchDeleteModule($crudInfo, $ids);

        return $this->success('删除成功');
    }

    /**
     * 检测实体.
     * @return array|Model|SystemCrud
     * @throws BindingResolutionException
     * @email 136327134@qq.com
     * @date 2024/3/9
     */
    protected function checkCrud($name)
    {
        $crudInfo = app()->make(SystemCrudService::class)->get(
            where: ['table_name_en' => $name],
            with: [
                'field' => fn ($q) => $q
                    ->select(['crud_id', 'field_name_en', 'is_main', 'field_name', 'form_value', 'field_type', 'is_default', 'is_uniqid']),
            ]
        );
        if (! $crudInfo) {
            throw new ApiException('没有查询到实体信息');
        }

        return $crudInfo;
    }
}
