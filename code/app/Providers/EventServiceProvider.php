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

namespace App\Providers;

use App\Listeners\AutoCopy;
use App\Listeners\socket\WebSocketAdmin;
use App\Listeners\socket\WebSocketEnt;
use App\Listeners\socket\WebSocketError;
use App\Listeners\socket\WebSocketUser;
use App\Listeners\swoole\SwooleShutDown;
use App\Listeners\swoole\SwooleStart;
use App\Listeners\swoole\SwooleTask;
use App\Listeners\SystemCrud;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Register any events for your application.
     */
    public function boot()
    {
        // swoole事件
        Event::listen('swoole.start', SwooleStart::class); // swoole 启动
        Event::listen('swoole.task', SwooleTask::class); // swoole 任务
        Event::listen('swoole.shutDown', SwooleShutDown::class); // swoole 停止
        Event::listen('swoole.workerError', WebSocketError::class); // socket 发生错误
        Event::listen('swoole.websocket.user', WebSocketUser::class); // socket 用户事件
        Event::listen('swoole.websocket.admin', WebSocketAdmin::class); // socket 后台事件
        Event::listen('swoole.websocket.ent', WebSocketEnt::class); // socket 企业后台事件
        Event::listen('approve.autoCopy', AutoCopy::class); // 自动抄送
        Event::listen('system.crud', SystemCrud::class); // crud事件
    }

    /**
     * Register the application's event listeners.
     */
    public function register()
    {
        if (file_exists(public_path('install/install.lock'))) {
            $this->booting(function () {
                $events = $this->getEvents();
                foreach ($events as $event => $listeners) {
                    foreach (array_unique($listeners, SORT_REGULAR) as $listener) {
                        Event::listen($event, $listener);
                    }
                }
                foreach ($this->subscribe as $subscriber) {
                    Event::subscribe($subscriber);
                }
                foreach ($this->observers as $model => $observers) {
                    $model::observe($observers);
                }
            });
        }
    }
}
