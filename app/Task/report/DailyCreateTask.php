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

namespace App\Task\report;

use App\Constants\ScheduleEnum;
use App\Http\Contract\Schedule\ScheduleInterface;
use App\Http\Service\Attendance\AttendanceArrangeService;
use Hhxsv5\LaravelS\Swoole\Task\Task;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * 汇报日程事件
 * Class DailyCreateSuccess.
 */
class DailyCreateTask extends Task
{
    private ScheduleInterface $schedule;

    /**
     * @var Application|mixed|Repository
     */
    private mixed $timeZone;

    public function __construct(protected $entId, protected $uuid, protected $id, protected $data)
    {
        $this->schedule = app()->get(ScheduleInterface::class);
        $this->timeZone = config('app.timezone');
    }

    public function handle()
    {
        try {
            if (! sys_config('schedule_sync', 0) || $this->data['types'] == 3) {
                return;
            }

            if (empty($this->data['plan'])) {
                return;
            }

            // compare next work day plan
            $diffPlan = array_diff(array_unique($this->data['plan']), $this->schedule->getNextWorkDayPlan($this->uuid));
            if (! $diffPlan) {
                return;
            }

            $time = ' 09:00:00';
            $uid  = uuid_to_uid($this->uuid, $this->entId);
            $save = [
                'remind'      => 1,
                'member'      => [$uid],
                'remind_time' => match ((int) $this->data['types']) {
                    1       => Carbon::parse('+1 weeks', $this->timeZone)->floorWeek()->toDateString() . $time,
                    2       => Carbon::parse('+1 months', $this->timeZone)->firstOfMonth()->toDateString() . $time,
                    default => Carbon::tomorrow($this->timeZone)->toDateString() . $time,
                },
                'all_day'    => 1,
                'cid'        => ScheduleEnum::TYPE_REPORT_RENEW,
                'period'     => 0,
                'rate'       => 1,
                'start_time' => match ((int) $this->data['types']) {
                    1       => Carbon::parse('+1 weeks', $this->timeZone)->floorWeek()->toDateTimeString(),
                    2       => Carbon::parse('+1 months', $this->timeZone)->firstOfMonth()->toDateTimeString(),
                    default => Carbon::tomorrow($this->timeZone)->toDateTimeString(),
                },
                'end_time' => match ((int) $this->data['types']) {
                    1       => Carbon::parse('+1 weeks', $this->timeZone)->endOfWeek()->endOfDay()->toDateTimeString(),
                    2       => Carbon::parse('+1 months', $this->timeZone)->endOfMonth()->endOfDay()->toDateTimeString(),
                    default => Carbon::tomorrow($this->timeZone)->endOfDay()->toDateTimeString(),
                },
                'fail_time' => match ((int) $this->data['types']) {
                    1       => Carbon::parse('+1 weeks', $this->timeZone)->endOfWeek()->endOfDay()->toDateTimeString(),
                    2       => Carbon::parse('+1 months', $this->timeZone)->endOfMonth()->endOfDay()->toDateTimeString(),
                    default => Carbon::tomorrow($this->timeZone)->endOfDay()->toDateTimeString(),
                },
                'link_id' => $this->id,
            ];

            // get daily next work day
            if ($this->data['types'] < 1) {
                $workDay    = app()->get(AttendanceArrangeService::class)->getNextArrangeDayByUid($uid, Carbon::now($this->timeZone)->toDateString());
                $workDayObj = Carbon::parse($workDay, $this->timeZone);

                $save['start_time']  = $workDayObj->startOfDay()->toDateTimeString();
                $save['remind_time'] = $workDayObj->toDateString() . $time;
                $save['end_time']    = $save['fail_time'] = $workDayObj->endOfDay()->toDateTimeString();
            }

            foreach ($diffPlan as $item) {
                $save['content'] = $save['title'] = $item;
                $this->schedule->saveSchedule($uid, $this->entId, $save);
            }
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . ':' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTrace()]);
        }
    }
}
