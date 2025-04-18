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

use App\Http\Service\Admin\AdminService;
use App\Http\Service\Config\CompanyConfigService;
use App\Http\Service\Config\SystemConfigService;
use App\Http\Service\System\SystemGroupService;
use crmeb\services\ApiResponseService;
use crmeb\services\ConfigService;
use crmeb\services\EntUserService;
use crmeb\services\GroupDataService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register()
    {
        // 绑定response
        $this->app->singleton('json', ApiResponseService::class);

        $stores  = $this->app->config->get('cache.default', 'file');
        $timeout = $this->app->config->get('cache.stores.' . $stores . '.timeout', 3600);
        // 绑定组合数据
        $this->app->singleton('group_config', function ($app) use ($timeout) {
            return new GroupDataService(SystemGroupService::class, $app->cache, $timeout);
        });
        // 绑定系统config
        $this->app->singleton('config_crmeb', function ($app) use ($timeout) {
            return new ConfigService(SystemConfigService::class, $timeout);
        });
        // 绑定系统config
        $this->app->singleton('ent_config_crmeb', function ($app) use ($timeout) {
            return new ConfigService(CompanyConfigService::class, $app->cache, $timeout);
        });
        // 绑定企业用户
        $this->app->singleton('enterprise_user', function ($app) use ($timeout) {
            return new EntUserService(AdminService::class, $app->cache, $timeout);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot() {}
}
