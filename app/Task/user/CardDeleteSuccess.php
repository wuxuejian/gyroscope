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

namespace App\Task\user;

use App\Http\Service\Frame\FrameAssistService;
use App\Http\Service\System\RoleUserService;
use App\Http\Service\User\UserApplyService;
use App\Http\Service\User\UserEducationService;
use App\Http\Service\User\UserPositionService;
use App\Http\Service\User\UserWorkService;
use App\Task\frame\FrameCensusTask;
use Hhxsv5\LaravelS\Swoole\Task\Task;
use Illuminate\Support\Facades\Log;

/**
 * 企业名片删除成功事件
 * Class CardDeleteSuccess.
 */
class CardDeleteSuccess extends Task
{
    public function __construct(protected int $userId, protected int $cardId, protected string $uid, protected int $entid, protected array $frameIds) {}

    public function handle()
    {
        try {
            // 删除eb_frame_assist关联
            app()->get(FrameAssistService::class)->delete(['user_id' => $this->userId]);
            // 删除eb_enterprise_user_work表数据
            app()->get(UserWorkService::class)->delete(['card_id' => $this->cardId]);
            // 删除eb_enterprise_user_position表数据
            app()->get(UserPositionService::class)->delete(['card_id' => $this->cardId]);
            // 删除eb_enterprise_user_education表数据
            app()->get(UserEducationService::class)->delete(['card_id' => $this->cardId]);
            // 删除enterprise_role_user表数据
            app()->get(RoleUserService::class)->delete(['user_id' => $this->userId]);
            // 删除eb_user_enterprise_apply企业邀请成员信息
            app()->get(UserApplyService::class)->delete(['uid' => $this->uid, 'entid' => $this->entid]);

            Task::deliver(new FrameCensusTask());
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . ':' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTrace()]);
        }
    }
}
