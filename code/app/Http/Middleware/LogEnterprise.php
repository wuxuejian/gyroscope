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

namespace App\Http\Middleware;

use App\Http\Service\System\LogService;
use crmeb\interfaces\ApiMiddlewareInterface;
use crmeb\traits\MiddlewareTrait;
use Illuminate\Http\Request;

/**
 * Class LogEnterprise.
 */
class LogEnterprise implements ApiMiddlewareInterface
{
    use MiddlewareTrait;

    /**
     * @return mixed|void
     */
    public function before(Request $request)
    {
        try {
            app()->get(LogService::class)->createLog(auth('admin')->user()->uid, 1, auth('admin')->user()->name, 'system');
        } catch (\Throwable $e) {
        }
    }

    public function after($response)
    {
        // TODO: Implement after() method.
    }
}
