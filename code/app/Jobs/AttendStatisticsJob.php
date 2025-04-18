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

namespace App\Jobs;

use App\Constants\NoticeEnum;
use App\Http\Service\Attendance\AttendanceApplyRecordService;
use App\Http\Service\Attendance\AttendanceRemindService;
use App\Http\Service\Attendance\AttendanceStatisticsService;
use App\Http\Service\Message\MessageService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * 考勤异步执行任务
 */
class AttendStatisticsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private Carbon $carbon) {}

    /**
     * Execute the job.
     */
    public function handle()
    {
        $tz          = config('app.timezone');
        $date        = $this->carbon->toDateString();
        $dateTime    = $this->carbon->toDateTimeString();
        $dayOfWeek   = $this->carbon->dayOfWeek;
        $daysInMonth = $this->carbon->daysInMonth;

        $msgService    = app()->get(MessageService::class);
        $remindService = app()->get(AttendanceRemindService::class);

        try {
            if ($this->carbon->format('s') == '00') {
                $remindService->sendShortRemindMessage($this->carbon);
            }
        } catch (\Throwable $e) {
            Log::error('缺卡提醒推送失败：' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
        }

        try {
            if (Carbon::parse($date . ' 00:00:30', $tz)->equalTo($dateTime)) {
                app()->get(AttendanceStatisticsService::class)->generateMemberStatistics($date);
            }
        } catch (\Throwable $e) {
            Log::error('考勤数据创建失败：' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
        }

        try {
            if (Carbon::parse($date . ' 00:30:00', $tz)->equalTo($dateTime)) {
                $remindService->generateShiftRemind($this->carbon);
            }
        } catch (\Throwable $e) {
            Log::error('考勤推送数据创建失败：' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
        }

        try {
            if (Carbon::parse($date . ' 01:00:00', $tz)->equalTo($dateTime)) {
                app()->get(AttendanceApplyRecordService::class)->calcApplyRecordTime($date);
            }
        } catch (\Throwable $e) {
            Log::error('审批考勤更新失败：' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
        }

        try {
            $dailyMessage = $msgService->getMessageContent(1, NoticeEnum::TEAM_ATTENDANCE_DAILY_REMIND);
            if ($dailyMessage && $dateTime === $this->carbon->format("Y-m-d {$dailyMessage['remind_time']}:00")) {
                $remindService->sendTeamDailyPush(Carbon::now($tz)->subDays(2)->toDateTimeString());
            }
        } catch (\Throwable $e) {
            Log::error('团队日报推送失败：' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
        }

        try {
            if ($dayOfWeek == 1) {
                $weeklyMessage = $msgService->getMessageContent(1, NoticeEnum::TEAM_ATTENDANCE_WEEKLY_REMIND);
                if ($weeklyMessage && $dateTime === $this->carbon->format("Y-m-d {$weeklyMessage['remind_time']}:00")) {
                    $dateObj = Carbon::now($tz)->subWeek();
                    $date    = $dateObj->startOfWeek()->format('Y/m/d 00:00:00') . '-' . $dateObj->endOfWeek()->format('Y/m/d 23:59:59');
                    $remindService->sendTeamWeeklyPush($date);
                }
            }
        } catch (\Throwable $e) {
            Log::error('团队周报推送失败：' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
        }

        try {
            if ($daysInMonth == 1) {
                $monthlyMessage = $msgService->getMessageContent(1, NoticeEnum::TEAM_ATTENDANCE_MONTHLY_REMIND);
                if ($monthlyMessage && $dateTime === $this->carbon->format("Y-m-d {$monthlyMessage['remind_time']}:00")) {
                    $remindService->sendTeamMonthlyPush(Carbon::now($tz)->subMonth()->format('Y-m'));
                }
            }
        } catch (\Throwable $e) {
            Log::error('团队月报推送失败：' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
        }

        try {
            if ($dayOfWeek == 1) {
                $personalWeeklyMessage = $msgService->getMessageContent(1, NoticeEnum::PERSONAL_WEEKLY_REMIND);
                if ($personalWeeklyMessage && $dateTime === $this->carbon->format("Y-m-d {$personalWeeklyMessage['remind_time']}:00")) {
                    $dateObj = Carbon::now($tz)->subWeek();
                    $date    = $dateObj->startOfWeek()->format('Y/m/d 00:00:00') . '-' . $dateObj->endOfWeek()->format('Y/m/d 23:59:59');
                    $remindService->sendPersonalPush($date, NoticeEnum::PERSONAL_WEEKLY_REMIND, 'time');
                }
            }
        } catch (\Throwable $e) {
            Log::error('个人周报推送失败：' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
        }

        try {
            if ($daysInMonth == 1) {
                $personalMonthlyMessage = $msgService->getMessageContent(1, NoticeEnum::PERSONAL_MONTHLY_REMIND);
                if ($personalMonthlyMessage && $dateTime === $this->carbon->format("Y-m-d {$personalMonthlyMessage['remind_time']}:00")) {
                    $remindService->sendPersonalPush(Carbon::now($tz)->subMonth()->format('Y-m'), NoticeEnum::PERSONAL_MONTHLY_REMIND, 'month');
                }
            }
        } catch (\Throwable $e) {
            Log::error('个人月报推送失败：' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
        }
    }
}
