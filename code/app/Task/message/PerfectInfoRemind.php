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

namespace App\Task\message;

use crmeb\utils\MessageType;
use Hhxsv5\LaravelS\Swoole\Task\Task;
use Illuminate\Support\Facades\Log;

/**
 * 邀请用户完善信息提醒.
 */
class PerfectInfoRemind extends Task
{
    public function __construct(protected $userInfo, protected $entInfo) {}

    public function handle()
    {
        try {
            $task = new MessageSendTask(
                entid: $this->entInfo['id'],
                i: $this->entInfo['id'],
                type: MessageType::PERFECT_USER_INFO,
                toUid: ['to_uid' => $this->userInfo['id'], 'phone' => $this->userInfo['phone']],
                params: [
                    '企业名称' => $this->entInfo['enterprise_name'] ?? '',
                ],
                other: ['entid' => $this->entInfo['id'], 'uid' => $this->userInfo['id']]
            );
            Task::deliver($task);
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . ':' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTrace()]);
        }
    }
}
