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

use App\Http\Contract\Company\EmployeeTrainInterface;
use App\Http\Controller\AdminApi\AuthController;
use App\Http\Middleware\AuthAdmin;
use App\Http\Middleware\AuthEnterprise;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Prefix;
use Spatie\RouteAttributes\Attributes\Put;

/**
 * 员工培训.
 */
#[Prefix('ent/company/train')]
#[Middleware(['auth.admin', 'ent.auth', 'ent.log'])]
class EmployeeTrainController extends AuthController
{
    public function __construct(EmployeeTrainInterface $services)
    {
        parent::__construct();
        $this->service = $services;
        $this->middleware([AuthAdmin::class, AuthEnterprise::class]);
    }

    /**
     * 详情.
     */
    #[Get('{type}', '员工培训详情')]
    public function info($type): mixed
    {
        $info = $this->service->setTrainType($type)->getInfo();
        return $this->success($info);
    }

    /**
     * 更新培训内容.
     */
    #[Put('{type}', '更新培训内容')]
    public function update($type): mixed
    {
        [$content] = $this->request->postMore(['content', ''], true);
        $this->service->setTrainType($type)->updateTrain((string) $content);
        return $this->success('common.operation.succ');
    }
}
