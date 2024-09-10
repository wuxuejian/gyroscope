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

namespace crmeb\swoole\laravels;

use crmeb\swoole\server\FileWatcher;
use Hhxsv5\LaravelS\LaravelS as SLaravelS;
use Illuminate\Queue\WorkerOptions;
use Illuminate\Support\Arr;
use Swoole\Http\Server;
use Swoole\Process;
use Swoole\Timer;

/**
 * 重写laravels.
 */
class LaravelS extends SLaravelS
{
    public function __construct(array $svrConf, array $laravelConf)
    {
        parent::__construct($svrConf, $laravelConf);

        $hotReload = $this->conf['hot_reload'] ?? [];
        if (isset($hotReload['enable']) && $hotReload['enable']) {
            $this->addFileWatcherProcess($this->swoole, $hotReload);
        }


        $queue = $this->conf['queue'] ?? [];
        if (isset($queue['enable']) && $queue['enable']) {
            $workerNum = $this->conf['queue_worker_num'] ?? 1;
            for ($i = 1; $i <= $workerNum; $i++) {
                $this->addQueueProcess($this->swoole, array_merge(['basePath' => $this->laravelConf['root_path']], $queue));
            }
        }
    }

    protected function addFileWatcherProcess(Server $swoole, array $config)
    {
        $callback = function () use ($swoole, $config) {
            self::getOutputStyle()->title('Hot update enabled, listening for file changes.');

            $watcher = new FileWatcher(
                Arr::get($config, 'include', []),
                Arr::get($config, 'exclude', []),
                Arr::get($config, 'name', [])
            );

            $watcher->watch(function ($paths) use ($swoole) {
                $swoole->reload();
                foreach ($paths as $path) {
                    self::getOutputStyle()->success('Updated file:' . $path);
                }
            });

            //            Event::wait();
        };

        $process = new Process($callback, false, 0, true);

        $swoole->addProcess($process);
        return $process;
    }

    protected function addQueueProcess(Server $swoole, array $config = []): void
    {
        $callback = function (Process $process) use ($swoole, $config) {

            @cli_set_process_title("swoole queue: worker process");

            $this->initLaravel($this->laravelConf, $swoole);

            $worker = app()->make('queue.worker');
            $option = app()->make(WorkerOptions::class);

            $option->rest     = Arr::get($config, 'rest', 0);
            $option->sleep    = Arr::get($config, 'sleep', 3);
            $option->maxTries = Arr::get($config, 'tries', 0);
            $option->timeout  = Arr::get($config, 'timeout', 60);
            $option->memory   = Arr::get($config, 'memory', 128);

            $queue = env('REDIS_QUEUE', 'CRMEB_OA');
            while (true) {
                $timer = Timer::after($option->timeout * 1000, function () use ($process) {
                    $process->exit();
                });

                $worker->runNextJob(null, $queue, $option);

                Timer::clear($timer);
            }
        };
        $swoole->addProcess(new Process($callback, false, 0));
    }
}
