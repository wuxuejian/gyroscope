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

use App\Http\Contract\System\LogInterface;
use App\Http\Controller\AdminApi\AuthController;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Prefix;

/**
 * 日志.
 */
#[Prefix('ent/system/log')]
#[Middleware(['auth.admin', 'ent.auth', 'ent.log'])]
class LogController extends AuthController
{
    public function __construct(LogInterface $services)
    {
        parent::__construct();
        $this->service = $services;
    }

    /**
     * 获取列表.
     * @return mixed
     */
    #[Get('/', '系统日志列表')]
    public function index()
    {
        $where = $this->request->getMore([
            ['user_name', ''],
            ['path', ''],
            ['time', ''],
            ['event_name', ''],
            ['entid', 1],
        ]);
        return $this->success($this->service->getLogPageList($where));
    }
}
