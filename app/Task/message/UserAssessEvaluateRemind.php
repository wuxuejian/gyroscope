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

namespace App\Task\message;

use App\Constants\AssessEnum;
use App\Http\Service\Admin\AdminService;
use crmeb\utils\MessageType;
use Hhxsv5\LaravelS\Swoole\Task\Task;
use Illuminate\Support\Facades\Log;

/**
 * 上级评价发送提醒给自己分数
 * Class UserAssessEvaluateRemind.
 */
class UserAssessEvaluateRemind extends Task
{
    public function __construct(protected $entId, protected $userAssess, protected $name, protected $total) {}

    public function handle()
    {
        try {
            $task = new MessageSendTask(
                entid: $this->entId,
                i: $this->entId,
                type: MessageType::ASSESS_EVALUATE_RESULT_TYPE,
                toUid: ['to_uid' => $this->userAssess['test_uid'], 'phone' => app()->get(AdminService::class)->value($this->userAssess['test_uid'], 'phone')],
                params: [
                    '考核名称'   => $this->name,
                    '上级评价得分' => $this->total,
                ],
                other: ['id' => $this->userAssess['id']],
                linkId: $this->userAssess['id'],
                linkStatus: $this->userAssess['status']
            );
            Task::deliver($task);
            Task::deliver(new StatusChangeTask(AssessEnum::LINK_NOTICE, AssessEnum::ASSESS_VERIFY, $this->entId, $this->userAssess['id']));
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . ':' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTrace()]);
        }
    }
}
