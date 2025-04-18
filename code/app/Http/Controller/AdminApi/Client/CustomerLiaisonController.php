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

namespace App\Http\Controller\AdminApi\Client;

use App\Constants\CustomEnum\LiaisonEnum;
use App\Http\Controller\AdminApi\AuthController;
use App\Http\Service\Client\CustomerLiaisonService;
use App\Http\Service\Config\FormService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Prefix;
use Spatie\RouteAttributes\Attributes\Resource;

/**
 * 联系人管理
 * Class CustomerLiaisonController.
 */
#[Prefix('ent/client/liaisons')]
#[Resource('/', false, except: ['show'], names: [
    'index'   => '联系人列表',
    'create'  => '联系人新增表单',
    'store'   => '保存联系人',
    'edit'    => '联系人修改表单',
    'update'  => '修改联系人',
    'destroy' => '删除联系人',
], parameters: ['' => 'id'])]
#[Middleware(['auth.admin', 'ent.auth', 'ent.log'])]
class CustomerLiaisonController extends AuthController
{
    public function __construct(CustomerLiaisonService $services)
    {
        parent::__construct();
        $this->service = $services;
    }

    /**
     * 列表数据.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function index(): mixed
    {
        $where = $this->request->getMore($this->service->getSearchField());
        $types = (int) $this->request->get('types', 117);
        if (in_array($types, LiaisonEnum::LIAISON_TYPE)) {
            $types = LiaisonEnum::CUSTOMER_VIEWER_LIAISON;
        }
        return $this->success($this->service->getListByType($types, $where));
    }

    /**
     * 创建表单.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function create(FormService $service): mixed
    {
        return $this->success($service->getFormDataWithType(LiaisonEnum::LIAISON));
    }

    /**
     * 保存.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function store(FormService $service): mixed
    {
        $eid = (int) $this->request->post('eid', 0);
        if ($eid < 1) {
            return $this->fail('客户数据异常');
        }

        $data = $this->request->postMore($service->getRequestFields(LiaisonEnum::LIAISON));
        $res  = $this->service->saveLiaison($data, $eid, $this->uuid);
        return $this->success('common.insert.succ', ['id' => $res->id]);
    }

    /**
     * 修改.
     * @param mixed $id
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function update($id, FormService $service): mixed
    {
        if (! $id) {
            return $this->fail($this->message['update']['empty']);
        }
        $data = $this->request->postMore($service->getRequestFields(LiaisonEnum::LIAISON));
        $this->service->updateLiaison($data, (int) $id);
        return $this->success(__('common.update.succ'));
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
            return $this->fail('common.empty.attrs');
        }
        $this->service->deleteLiaison((int) $id, $this->uuid);
        return $this->success('common.delete.succ');
    }

    /**
     * 详情.
     * @param mixed $id
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function info($id): mixed
    {
        if (! $id) {
            return $this->fail($this->message['update']['empty']);
        }

        return $this->success($this->service->getInfo((int) $id, $this->uuid));
    }

    /**
     * 修改表单.
     * @param mixed $id
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function edit($id): mixed
    {
        if (! $id) {
            return $this->fail($this->message['update']['empty']);
        }

        return $this->success($this->service->getEditInfo((int) $id, $this->uuid));
    }
}
