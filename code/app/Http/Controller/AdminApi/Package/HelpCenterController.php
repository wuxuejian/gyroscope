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

namespace App\Http\Controller\AdminApi\Package;

use App\Http\Controller\AdminApi\AuthController;
use App\Http\Service\Package\HelpCenterService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\SimpleCache\InvalidArgumentException;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Prefix;

/**
 * 帮助中心.
 */
#[Prefix('ent/helps')]
#[Middleware(['auth.admin', 'ent.auth', 'ent.log'])]
class HelpCenterController extends AuthController
{
    public function __construct(HelpCenterService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    /**
     * 结果页搜索.
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws InvalidArgumentException
     */
    #[Get('aggregate', name: '结果页查询')]
    public function aggregateSearch(): mixed
    {
        $where = $this->request->getMore([
            ['word', ''],
            ['type', ''],
            ['page', ''],
            ['limit', ''],
        ]);
        return $this->success($this->service->aggregateSearch($where), tips: 0);
    }
}
