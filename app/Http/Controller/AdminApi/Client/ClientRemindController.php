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

namespace App\Http\Controller\AdminApi\Client;

use App\Http\Controller\AdminApi\AuthController;
use App\Http\Requests\enterprise\client\ClientRemindRequest;
use App\Http\Service\Client\ClientRemindService;
use crmeb\traits\ResourceControllerTrait;
use Illuminate\Contracts\Container\BindingResolutionException;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Prefix;
use Spatie\RouteAttributes\Attributes\Put;
use Spatie\RouteAttributes\Attributes\Resource;

/**
 * 付费提醒
 * Class ClientRemindController.
 */
#[Prefix('ent/client/remind')]
#[Resource('/', false, except: ['create', 'show', 'edit'], names: [
    'index'   => '获取付款提醒列表',
    'store'   => '保存付款提醒接口',
    'update'  => '修改付款提醒接口',
    'destroy' => '删除付款提醒接口',
], parameters: ['' => 'id'])]
#[Middleware(['auth.admin', 'ent.auth', 'ent.log'])]
class ClientRemindController extends AuthController
{
    use ResourceControllerTrait;

    public function __construct(ClientRemindService $services)
    {
        parent::__construct();
        $this->service = $services;
    }

    /**
     * 修改备注.
     * @return mixed
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    #[Put('mark/{id}', '修改付款提醒备注')]
    public function setMark($id)
    {
        if (! $id) {
            return $this->fail('common.empty.attrs');
        }
        [$mark] = $this->request->postMore([
            ['mark', ''],
        ], true);
        $this->service->setMark($id, $mark);
        return $this->success('common.operation.succ');
    }

    /**
     * 放弃.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    #[Put('abjure/{id}', '放弃付款提醒')]
    public function abjure($id): mixed
    {
        if (! $id) {
            return $this->fail('common.empty.attrs');
        }

        $this->service->updateStatus((int) $id, 1);
        return $this->success('common.operation.succ');
    }

    /**
     * 获取详情.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    #[Get('info/{id}', '获取付款提醒详情')]
    public function info($id): mixed
    {
        if (! $id) {
            return $this->fail('common.empty.attrs');
        }
        return $this->success($this->service->getInfo(['id' => (int) $id]));
    }

    /**
     * 搜索字段.
     * @return array|\string[][]
     */
    protected function getSearchField(): array
    {
        return [
            ['eid', ''],
            ['cid', ''],
        ];
    }

    /**
     * 设置.
     */
    protected function getRequestClassName(): string
    {
        return ClientRemindRequest::class;
    }

    /**
     * 提取字段.
     */
    protected function getRequestFields(): array
    {
        return [
            ['eid', ''],
            ['cid', ''],
            ['num', ''],
            ['mark', ''],
            ['types', 0],
            ['time', ''],
            ['cate_id', ''],
        ];
    }
}
