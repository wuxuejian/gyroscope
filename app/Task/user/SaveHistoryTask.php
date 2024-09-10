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

use App\Http\Service\User\UserEducationHistoryService;
use App\Http\Service\User\UserEducationService;
use App\Http\Service\User\UserWorkHistoryService;
use App\Http\Service\User\UserWorkService;
use Hhxsv5\LaravelS\Swoole\Task\Task;
use Illuminate\Support\Facades\Log;

/**
 * 增加用户教育经历、工作经历.
 */
class SaveHistoryTask extends Task
{
    /**
     * SmsMessageSendJob constructor.
     * @param mixed $uid
     * @param mixed $cardId
     */
    public function __construct(protected $uid, protected $cardId) {}

    /**
     * @return true|void
     */
    public function handle()
    {
        try {
            $education = toArray(app()->get(UserEducationHistoryService::class)->select(['uid' => $this->uid]));
            if ($education) {
                $educatUserServices = app()->get(UserEducationService::class);
                $educatUserServices->delete(['card_id' => $this->cardId]);
                foreach ($education as $value) {
                    unset($value['uid'],$value['resume_id'],$value['id']);
                    $save            = $value;
                    $save['card_id'] = $this->cardId;
                    $educatUserServices->create($save);
                }
            }

            $work = toArray(app()->get(UserWorkHistoryService::class)->select(['uid' => $this->uid]));
            if ($work) {
                $worksUserServices = app()->get(UserWorkService::class);
                $worksUserServices->delete(['card_id' => $this->cardId]);
                foreach ($work as $val) {
                    unset($val['uid'],$val['resume_id'],$val['id']);
                    $saveWork            = $val;
                    $saveWork['card_id'] = $this->cardId;
                    $worksUserServices->create($saveWork);
                }
            }
            return true;
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . ':' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTrace()]);
        }
    }
}
