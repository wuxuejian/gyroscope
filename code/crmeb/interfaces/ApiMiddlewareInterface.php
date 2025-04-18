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

namespace crmeb\interfaces;

use Illuminate\Http\Request;

/**
 * 中间件接口
 * Interface MiddlewareInterface.
 */
interface ApiMiddlewareInterface
{
    /**
     * 前置.
     * @return mixed
     */
    public function before(Request $request);

    /**
     * 执行调度.
     * @param mixed ...$args
     * @return mixed
     */
    public function handle(Request $request, \Closure $next, ...$args);

    /**
     * 后置.
     * @param mixed $response
     * @return mixed
     */
    public function after($response);
}
