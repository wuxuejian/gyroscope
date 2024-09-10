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

namespace App\Http\Controller\AdminApi\System;

use App\Http\Controller\AdminApi\AuthController;
use App\Http\Middleware\AuthAdmin;
use App\Http\Middleware\AuthEnterprise;
use App\Http\Middleware\CheckRuleCompany;
use App\Http\Requests\system\SystemAgreementRequest;
use App\Http\Service\Config\SystemAgreementService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Prefix;
use Spatie\RouteAttributes\Attributes\Resource;

/**
 * 协议.
 */
#[Prefix('ent/system/treaty')]
#[Resource('/', false, except: ['create', 'show', 'store', 'destroy'], names: [
    'index'  => '用户协议列表',
    'edit'   => '用户协议详情',
    'update' => '用户协议修改',
], parameters: ['' => 'id'])]
#[Middleware(['auth.admin', 'ent.auth', 'ent.log'])]
class AgreementController extends AuthController
{
    public function __construct(SystemAgreementService $services)
    {
        parent::__construct();
        $this->service = $services;
        $this->middleware([AuthAdmin::class, AuthEnterprise::class, CheckRuleCompany::class]);
    }

    /**
     * 获取协议列表.
     */
    public function index(): mixed
    {
        $where = $this->request->getMore([
            ['title', ''],
            ['ident', ''],
        ]);
        return $this->success($this->service->getList($where));
    }

    /**
     * 协议详情.
     */
    public function edit($id): mixed
    {
        if (! $id) {
            return $this->fail('common.empty.attrs');
        }
        return $this->success($this->service->getInfo((int) $id));
    }

    /**
     * 协议修改.
     * @throws BindingResolutionException
     */
    public function update($id): mixed
    {
        $data = app()->get(SystemAgreementRequest::class)->postMore([
            ['content', ''],
        ]);

        if (! $id) {
            return $this->fail('common.empty.attrs');
        }

        $this->service->update($id, $data);
        return $this->success('common.update.succ');
    }
}
