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

use App\Http\Controller\Install;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * This is used by Laravel authentication to redirect users after login.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, etc.
     */
    public function boot()
    {
        $this->configureRateLimiting();

        Route::get('install/index/{step}', [Install::class, 'index']); // 安装程序
        Route::post('install/index/{step}', [Install::class, 'index']); // 安装程序
        $this->routes(function () {
            Route::middleware('web')->namespace($this->namespace)->group(base_path('routes/web.php'));
        });

        Route::fallback(function () {
            $appRequest = request()->getPathInfo();
            if ($appRequest === null) {
                $appName = config('app.ent.name');
            } else {
                $appRequest = str_replace('//', '/', $appRequest);
                $appName    = explode('/', $appRequest)[1] ?? '';
            }
            // 检测是否已安装系统
            if (! file_exists(public_path('install/install.lock'))) {
                return redirect('/install/index/1');
            }
            if ($appName === 'api') {
                return app('json')->make(400, 'common.route.miss');
            }
            if (! sys_config('site_open', 1)) {
                return Response::make('网站已关闭');
            }
            $userAgent = request()->header('User-Agent', 'admin');
            if (str_contains($userAgent, 'Mobi')) {
                // 手机端访问
                return View::file(public_path('work') . DIRECTORY_SEPARATOR . 'index.html');
            }
            // 非手机端访问
            return View::file(public_path('admin') . DIRECTORY_SEPARATOR . 'index.html');
        });
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(180)->by(optional($request->user())->id ?: $request->ip());
        });
    }
}
