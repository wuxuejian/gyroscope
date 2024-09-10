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

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Factory;

/**
 * OA系统自定义服务
 * Class CrmebOaServiceProvider.
 */
class CrmebOaServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register() {}

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        /* @var Factory $validator */
        $validator = $this->app['validator'];
        // Validator extensions
        $validator->extend('captcha_api', function ($attribute, $value, $parameters) {
            return captcha_api_check(strtolower($value), $parameters[0], $parameters[1] ?? 'default');
        });
        // 验证码验证
        $validator->extend('verification_api', function ($attribute, $value, $parameters) {
            return verification_api_check($value, $parameters[0]);
        });
        // 时间比较
        $validator->extend('time_contrast_api', function ($attribute, $value, $parameters) {
            return time_contrast_api_check($value, $parameters[0], isset($parameters[1]) ?: false);
        });
        // 密码确认
        $validator->extend('password_confirm_api', function ($attribute, $value, $parameters) {
            return password_confirm_api_check($value, $parameters[0] ?? null);
        });
    }
}
