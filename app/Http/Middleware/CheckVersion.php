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

use crmeb\exceptions\ApiException;
use crmeb\interfaces\ApiMiddlewareInterface;
use crmeb\traits\MiddlewareTrait;
use Illuminate\Http\Request;

/**
 * 检测App版本号
 * Class CheckEnterprise.
 */
class CheckVersion implements ApiMiddlewareInterface
{
    use MiddlewareTrait;

    /**
     * 前置事件.
     * @return mixed
     */
    public function before(Request $request)
    {
        $platform = $request->header('Form-Type', 'h5');
        if (strtolower($platform) === 'app') {
            $appVersion = $request->header('AppVersion', '');
            if (! $appVersion) {
                throw new ApiException('版本信息不匹配', 410005);
            }
            $version       = getVersion('version_num');
            $appVersion    = explode('.', $appVersion);
            $systemVersion = explode('.', $version);
            if ($appVersion[0] != $systemVersion[0] || $appVersion[1] != $systemVersion[1]) {
                throw new ApiException('版本信息不匹配，请更新App版本至: v' . $version . '.0版本', 410005);
            }
        }
    }

    /**
     * 后置中间件.
     * @return mixed
     */
    public function after($response)
    {
        return $response->withHeaders(['AppVersion' => getVersion('version_num')]);
    }
}
