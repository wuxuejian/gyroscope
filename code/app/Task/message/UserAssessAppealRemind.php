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
use App\Http\Service\Frame\FrameService;
use crmeb\utils\MessageType;
use Hhxsv5\LaravelS\Swoole\Task\Task;
use Illuminate\Support\Facades\Log;

/**
 * 绩效申诉请求
 * Class UserAssessAppealRemind.
 */
class UserAssessAppealRemind extends Task
{
    public function __construct(protected $entId, protected $assessInfo) {}

    public function handle()
    {
        try {
            $service     = app()->get(AdminService::class);
            $adminUserId = app()->get(FrameService::class)->getLevelSuper((int) $this->assessInfo['test_uid'], 2);
            if (! $adminUserId) {
                return;
            }
            $timeStr = match ((int) $this->assessInfo['period']) {
                1       => '周',
                2       => '月',
                3       => '年',
                4       => '季度',
                5       => '半年',
                default => ''
            };
            $userInfo = $service->get((int) $this->assessInfo['test_uid'], with: ['frame']);

            $task = new MessageSendTask(
                entid: $this->entId,
                i: $this->entId,
                type: MessageType::ASSESS_APPEAL_TYPE,
                toUid: ['to_uid' => $adminUserId, 'phone' => $service->value($adminUserId, 'phone')],
                params: [
                    '考核名称'  => $this->assessInfo['name'],
                    '考核类型'  => $timeStr . '考核',
                    '开始时间'  => $this->assessInfo['start_time'],
                    '结束时间'  => $this->assessInfo['end_time'],
                    '申诉人'   => $userInfo['name'] ?? '',
                    '申诉人部门' => $userInfo['frame']['name'] ?? '',
                    '申诉时间'  => date('Y-m-d H:i'),
                ],
                other: ['id' => $this->assessInfo['id']],
                linkId: $this->assessInfo['id'],
                linkStatus: AssessEnum::ASSESS_APPEAL,
            );
            Task::deliver($task);
            Task::deliver(new StatusChangeTask(AssessEnum::LINK_NOTICE, AssessEnum::ASSESS_APPEAL, $this->entId, $this->assessInfo['id']));
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . ':' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTrace()]);
        }
    }
}
