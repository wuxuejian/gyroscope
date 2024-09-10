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

use App\Http\Services\system\auth\SystemAdminServices;
use crmeb\interfaces\ApiMiddlewareInterface;
use crmeb\traits\MiddlewareTrait;
use Illuminate\Http\Request;

/**
 * Class CheckRuleAdmin.
 */
class CheckRuleAdmin implements ApiMiddlewareInterface
{
    use MiddlewareTrait;

    /**
     * 前置.
     * @return mixed|void
     */
    public function before(Request $request)
    {
        $uri = $request->route()->uri();
        if ($request->adminInfo('level')) {
            /** @var SystemAdminServices $service */
            $service = app()->get(SystemAdminServices::class);
            $service->checkAuth($uri, $request->adminInfo(), $request->method());
        }
    }

    /**
     * 后置.
     * @return mixed|void
     */
    public function after($response)
    {
        // TODO: Implement after() method.
    }
}
