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

namespace App\Http\Controller\AdminApi\Config;

use App\Http\Controller\AdminApi\AuthController;
use App\Http\Middleware\AuthAdmin;
use App\Http\Middleware\AuthEnterprise;
use App\Http\Requests\system\FormCateRequest;
use App\Http\Service\Config\FormService;
use App\Http\Service\Config\SalesmanCustomService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Prefix;
use Spatie\RouteAttributes\Attributes\Put;
use Spatie\RouteAttributes\Attributes\Resource;

/**
 * 自定义表单.
 */
#[Prefix('ent/config/form')]
#[Resource('cate', false, except: ['edit', 'create'], names: [
    'index'   => '自定义表单列表',
    'store'   => '保存自定义表单分组',
    'show'    => '修改自定义表单分组状态',
    'update'  => '修改自定义表单分组',
    'destroy' => '删除自定义表单分组',
], parameters: ['cate' => 'id'])]
#[Middleware(['auth.admin', 'ent.auth', 'ent.log'])]
class FormController extends AuthController
{
    public function __construct(FormService $services)
    {
        parent::__construct();
        $this->service = $services;
        $this->middleware([AuthAdmin::class, AuthEnterprise::class])->only('getSalesmanCustom');
    }

    /**
     * 表单列表.
     * @return array|mixed
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function index(): mixed
    {
        $types = $this->request->get('types', '');
        if (! $types) {
            return $this->fail('common.empty.attrs');
        }
        return $this->success($this->service->getList(['types' => $types]));
    }

    /**
     * 保存分类.
     * @param mixed $types
     * @throws BindingResolutionException
     */
    public function store($types, FormCateRequest $request): mixed
    {
        if (! $types) {
            return $this->fail('common.empty.attrs');
        }
        $data = $request->postMore($this->getCateRequestFields());
        $res  = $this->service->saveCate((int) $types, $data);
        return $this->success('common.insert.succ', ['id' => $res->id]);
    }

    /**
     * 修改分类.
     * @param mixed $id
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function update($id, FormCateRequest $request): mixed
    {
        if (! $id) {
            return $this->fail(__('common.empty.attrs'));
        }

        $data = $request->postMore($this->getCateRequestFields());
        $this->service->updateCate((int) $id, $data);
        return $this->success('common.operation.succ');
    }

    /**
     * 删除.
     * @param mixed $id
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function destroy($id): mixed
    {
        if (! $id) {
            return $this->fail(__('common.empty.attrs'));
        }

        $this->service->deleteCate((int) $id);
        return $this->success('common.delete.succ');
    }

    /**
     * 更新分组状态
     * @param mixed $id
     * @throws BindingResolutionException
     */
    public function show($id): mixed
    {
        if (! $id) {
            return $this->fail(__('common.empty.attrs'));
        }

        $status = $this->request->get('status');
        $this->service->updateStatus((int) $id, (int) $status);
        return $this->success('common.operation.succ');
    }

    /**
     * 保存表单.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    #[Post('data/{types}', '保存表单')]
    public function storeData($types): mixed
    {
        if (! $types) {
            return $this->fail('common.empty.attrs');
        }

        $data = $this->request->post('data', []);
        $this->service->saveData((int) $types, $data);
        return $this->success('common.operation.succ');
    }

    /**
     * 移动分组.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    #[Put('data/move/{types}', '移动分组')]
    public function move($types): mixed
    {
        if (! $types) {
            return $this->fail(__('common.empty.attrs'));
        }

        [$id, $cateId] = $this->request->postMore([
            ['id', 0],
            ['cate_id', 0],
        ], true);

        $this->service->moveData((int) $types, (int) $id, (int) $cateId);
        return $this->success('common.operation.succ');
    }

    /**
     * 获取业务数据字段接口.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    #[Get('data/fields/{customType}', '获取业务数据字段')]
    public function getSalesmanCustom(SalesmanCustomService $service, $customType): mixed
    {
        if (! $customType) {
            return $this->fail(__('common.empty.attrs'));
        }

        return $this->success($service->salesmanCustomField(auth('admin')->id(), (int) $customType));
    }

    /**
     * 保存业务数据.
     * @throws BindingResolutionException
     */
    #[Put('data/fields/{customType}', '保存业务数据字段')]
    public function saveSalesmanCustom(SalesmanCustomService $service, $customType): mixed
    {
        if (! $customType) {
            return $this->fail(__('common.empty.attrs'));
        }

        [$selectType, $data] = $this->request->postMore([
            ['select_type', ''],
            ['data', []],
        ], true);

        $service->saveSalesmanCustomField((int) uuid_to_uid($this->uuid), (int) $customType, $selectType, $data);
        return $this->success('common.update.succ');
    }

    protected function getCateRequestFields(): array
    {
        return [
            ['title', ''],
            ['sort', ''],
            ['status', 1],
        ];
    }
}
