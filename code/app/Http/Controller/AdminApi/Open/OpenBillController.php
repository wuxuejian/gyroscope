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

namespace App\Http\Controller\AdminApi\Open;

use App\Http\Controller\AdminApi\AuthController;
use App\Http\Middleware\AuthOpenApi;
use App\Http\Requests\enterprise\client\ClientBillRequest;
use App\Http\Requests\enterprise\client\ClientRemindRequest;
use App\Http\Service\Admin\AdminService;
use App\Http\Service\Client\ClientBillService;
use App\Http\Service\Client\ClientRemindService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Prefix;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Put;
use Spatie\RouteAttributes\Attributes\Delete;
use Spatie\RouteAttributes\Attributes\Resource;

#[Prefix('open/bill')]
#[Middleware([AuthOpenApi::class])]
class OpenBillController extends AuthController
{
    public function __construct(ClientBillService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    /**
     * 回款
     * @param ClientBillRequest $request
     * @param AdminService $adminService
     * @return mixed
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \Illuminate\Validation\ValidationException
     */
    #[Post('/payment', '保存付款接口')]
    public function payment(ClientBillRequest $request, AdminService $adminService): mixed
    {
        $request->scene('payment')->check();
        $uid = (int)$this->request->post('uid', 0);
        if ($uid && !$adminService->exists(['id' => $uid, 'status' => 1])) {
            return $this->fail('业务员不存在');
        }

        $data = $this->request->postMore($this->getRequestFields());
        $res  = $this->service->saveOpenBill($data, 'payment', $uid);
        return $this->success('common.insert.succ', ['id' => $res]);
    }

    /**
     * 续费
     * @param ClientBillRequest $request
     * @param AdminService $adminService
     * @return mixed
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \Illuminate\Validation\ValidationException
     */
    #[Post('/renewal', '保存续费接口')]
    public function renewal(ClientBillRequest $request, AdminService $adminService): mixed
    {
        $request->scene('renewal')->check();
        $uid = (int)$this->request->post('uid', 0);
        if ($uid && !$adminService->exists(['id' => $uid, 'status' => 1])) {
            return $this->fail('业务员不存在');
        }

        $data = $this->request->postMore($this->getRequestFields());
        $res  = $this->service->saveOpenBill($data, 'renewal', $uid);
        return $this->success('common.insert.succ', ['id' => $res]);
    }

    /**
     * 支出
     * @param ClientBillRequest $request
     * @param AdminService $adminService
     * @return mixed
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \Illuminate\Validation\ValidationException
     */
    #[Post('/expend', '保存支出接口')]
    public function expend(ClientBillRequest $request, AdminService $adminService): mixed
    {
        $request->scene('expend')->check();
        $uid = (int)$this->request->post('uid', 0);
        if ($uid && !$adminService->exists(['id' => $uid, 'status' => 1])) {
            return $this->fail('业务员不存在');
        }

        $data = $this->request->postMore($this->getRequestFields());
        $res  = $this->service->saveOpenBill($data, 'expend', $uid);
        return $this->success('common.insert.succ', ['id' => $res]);
    }

    /**
     * 删除账目.
     * @param $id
     * @return mixed
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    #[Delete('/{id}', '删除账目接口')]
    public function destroy($id): mixed
    {
        if (!$id) {
            return $this->fail('缺失账目记录ID！');
        }
        if ($this->service->resourceDelete($id)) {
            return $this->success('删除成功');
        }
        return $this->fail('删除失败');
    }

    /**
     * 保存付款提醒.
     * @param ClientRemindRequest $request
     * @param ClientRemindService $clientRemindService
     * @return mixed
     * @throws BindingResolutionException
     * @throws \Illuminate\Validation\ValidationException
     */
    #[Post('/remind', '保存付款提醒接口')]
    public function remind(ClientRemindRequest $request, ClientRemindService $clientRemindService): mixed
    {
        $request->scene('store')->check();
        $data = $this->request->postMore([
            ['eid', ''],
            ['cid', ''],
            ['num', ''],
            ['mark', ''],
            ['types', 0],
            ['time', ''],
            ['cate_id', ''],
        ]);
        $res  = $clientRemindService->resourceSave($data);
        return $this->success('common.insert.succ', $res ? ['id' => $res['id']] : []);
    }

    /**
     * 设置.
     */
    protected function getRequestClassName(): string
    {
        return ClientBillRequest::class;
    }

    /**
     * 提取字段.
     */
    protected function getRequestFields(): array
    {
        return [
            ['eid', 0],
            ['cid', 0],
            ['cate_id', 0],
            ['num', 0],
            ['types', ''],
            ['mark', ''],
            ['date', ''],
            ['uid', 0],
            ['type_id', 0],
            ['attach', []],
            ['end_date', ''],
            ['bill_cate_id', []],
            ['remind_id', 0],
        ];
    }

}
