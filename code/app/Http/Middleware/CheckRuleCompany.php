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

namespace App\Http\Middleware;

use App\Http\Service\System\RolesService;
use crmeb\interfaces\ApiMiddlewareInterface;
use crmeb\traits\MiddlewareTrait;
use Illuminate\Http\Request;

/**
 * 检查企业用户权限.
 */
class CheckRuleCompany implements ApiMiddlewareInterface
{
    use MiddlewareTrait;

    /**
     * 前置.
     * @return mixed|void
     */
    public function before(Request $request)
    {
        $uri = $request->route()->uri();
        app()->get(RolesService::class)->checkAuth($uri, $request->userInfo(), $request->entInfo() ?: [], $request->method());
    }

    /**
     * 后置.
     * @param mixed $response
     * @return mixed|void
     */
    public function after($response)
    {
        // TODO: Implement after() method.
    }
}
