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

namespace App\Http\Controller\AdminApi\Company;

use App\Http\Contract\Company\CompanyInterface;
use App\Http\Controller\AdminApi\AuthController;
use App\Http\Middleware\AuthAdmin;
use App\Http\Middleware\AuthEnterprise;
use App\Http\Middleware\CheckRuleCompany;
use App\Http\Requests\enterprise\EnterpriseRequest;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Validation\ValidationException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Prefix;
use Spatie\RouteAttributes\Attributes\Put;

/**
 * 企业管理.
 */
#[Prefix('ent/company')]
#[Middleware(['auth.admin', 'ent.auth', 'ent.log'])]
class CompanyController extends AuthController
{
    public function __construct(CompanyInterface $services)
    {
        parent::__construct();
        $this->service = $services;
        $this->middleware([AuthAdmin::class, AuthEnterprise::class, CheckRuleCompany::class])->only('index');
    }

    /**
     * 当前登录企业详细信息.
     * @return mixed
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    #[Get('info', '当前企业详情')]
    public function entInfo()
    {
        return $this->success($this->service->getEntAndUserInfo($this->entId));
    }

    /**
     * 修改当前企业.
     * @return mixed
     * @throws BindingResolutionException
     * @throws ValidationException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Put('info', '修改当前企业详情')]
    public function updateEnt(EnterpriseRequest $request)
    {
        $request->scene('update')->check();

        $data = $request->postMore([
            ['logo', ''],
            ['enterprise_name', ''],
            ['province', ''],
            ['city', ''],
            ['area', ''],
            ['address', ''],
            ['phone', ''],
            ['short_name', ''],
        ]);
        if ($this->service->updateEnt($this->entId, $data)) {
            return $this->success('common.update.succ');
        }
        return $this->fail('common.update.fail');
    }

    /**
     * 获取统计数量.
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Get('quantity/{type}', '获取统计数量')]
    public function quantity($type): mixed
    {
        $data = $this->service->getQuantity($type, $this->entId);
        return $this->success($data);
    }

    /**
     * 搜索字段.
     * @return array|\string[][]
     */
    protected function getSearchField(): array
    {
        return [
            ['verify', ''],
            ['uid', $this->uuid],
        ];
    }

    /**
     * 设置.
     */
    protected function getRequestClassName(): string
    {
        return EnterpriseRequest::class . '.create';
    }

    /**
     * 提取字段.
     */
    protected function getRequestFields(): array
    {
        return [
            ['enterprise_name', ''],
            ['province', ''],
            ['city', ''],
            ['area', ''],
            ['address', ''],
            ['phone', ''],
            ['lead', ''],
            ['business_license', ''],
        ];
    }
}
