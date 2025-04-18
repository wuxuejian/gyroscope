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

namespace App\Listeners\swoole;

use crmeb\interfaces\ListenerInterface;
use crmeb\socket\Room;
use Hhxsv5\LaravelS\Swoole\Socket\WebSocket;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Swoole\Server;

/**
 * swoole任务事件监听
 * Class SwooleTask.
 */
class SwooleTask implements ListenerInterface
{
    /**
     * @var Server
     */
    protected $server;

    /**
     * 任务id.
     * @var int
     */
    protected $taskId;

    /**
     * 进程id.
     * @var int
     */
    protected $workerId;

    /**
     * Create the event listener.
     */
    public function __construct() {}

    /**
     * Handle the event.
     *
     * @param object $event
     */
    public function handle($event): void
    {
        [$server, $taskId, $srcWorkerId, $data] = func_get_args();
        $this->server                           = $server;
        $this->taskId                           = $taskId;
        $this->workerId                         = $srcWorkerId;

        $data           = is_string($data) ? json_decode($data, true) : $data;
        $data['action'] = $data['action'] ?? '';
        if (Websocket::eventExists($data['action'])) {
            return;
        }

        if ($data['action'] === HttpWebsocket::PUSH_ACTION) {
            return;
        }

        Log::error($data['type']);
        $method = $data['type'] ?? null;

        if (method_exists($this, $method)) {
            $this->{$method}($data['data']);
        }

        $this->server   = null;
        $this->taskId   = null;
        $this->workerId = null;
    }

    /**
     * @throws BindingResolutionException
     */
    public function message(array $data)
    {
        $uid    = is_array($data['uid']) ? $data['uid'] : [$data['uid']];
        $entid  = $data['entid'] ?? 0;
        $except = $data['except'] ?? [];
        /** @var Room $room */
        $room = app()->get(Room::class);
        if (! count($uid) && $data['type'] != 'user') {
            $fds = $room->userFd($data['type']);
            foreach ($fds as $fd) {
                if (! in_array($fd, $except) && $this->server->isEstablished((int) $fd)) {
                    $this->server->push((int) $fd, json_encode($data['data']));
                }
            }
        } else {
            $fdsAll = [];
            foreach ($uid as $id) {
                if (is_array($id)) {
                    $id = $id['to_uid'] ?? 0;
                }
                if (strlen((string) $id) != 32) {
                    $id = uid_to_uuid((int) $id);
                    if (! $id) {
                        continue;
                    }
                }
                $fdsAll = array_merge($fdsAll, $room->userFd($data['type'], (string) $id, (int) $entid));
            }
            if (! empty(array_unique($fdsAll))) {
                foreach (array_unique($fdsAll) as $fd) {
                    if (! in_array($fd, $except) && $this->server->isEstablished((int) $fd)) {
                        $this->server->push((int) $fd, json_encode($data['data']));
                    }
                }
            }
        }
    }

    /**
     * @throws BindingResolutionException
     */
    public function notice($data)
    {
        if (Event::hasListeners($data['event'])) {
            Event::dispatch($data['event'], $data['param']);
        } else {
            Log::error(json_encode($data));
        }
    }
}
