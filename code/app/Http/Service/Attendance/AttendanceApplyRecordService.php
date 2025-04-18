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

namespace App\Http\Service\Attendance;

use App\Constants\ApproveEnum;
use App\Http\Dao\Attendance\AttendanceApplyRecordDao;
use App\Http\Service\Approve\ApproveApplyService;
use App\Http\Service\Approve\ApproveHolidayTypeService;
use App\Http\Service\BaseService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * 申请记录
 * Class AttendanceApplyRecordService.
 */
class AttendanceApplyRecordService extends BaseService
{
    protected const CACHE_KEY = 'attendance_apply_record';

    public function __construct(AttendanceApplyRecordDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 个人加班统计
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getPersonOvertimeStatistics(string $uuid, array $where): array
    {
        $list                = [];
        $timeTye             = 'hour';
        [$page, $limit]      = $this->getPageValue();
        $where['apply_type'] = ApproveEnum::PERSONNEL_OVERTIME;

        $tz             = config('app.timezone');
        $where['month'] = Carbon::parse($where['month'], $tz)->format('Y-m');
        $where['uid']   = app()->get(AttendanceClockService::class)->getStatisticsUserId($uuid, (int) $where['uid']);
        $statistics     = $this->dao->select($where, ['id', 'uid', 'date_type', 'time_type', 'others', 'work_hours', 'start_time'], [], $page, $limit);
        foreach ($statistics as $item) {
            $dateString = Carbon::parse($item->start_time, $tz)->toDateString();
            $workHours  = $timeTye == $item->time_type ? $item->work_hours : $this->getWorkHours($item->time_type, $timeTye, $item->work_hours);
            $dateType   = $dateString . '_' . $item->others['calc_type'];
            if (isset($list[$dateType])) {
                $list[$dateType]['work_hours'] = bcadd($workHours, $list[$dateType]['work_hours'], 1);
            } else {
                $list[$dateType] = [
                    'id'         => $item->id,
                    'date'       => $dateString,
                    'time_type'  => $timeTye,
                    'work_hours' => $workHours,
                    'date_type'  => $item->date_type,
                    'calc_type'  => $item->others['calc_type'],
                ];
            }
        }
        return array_values($list);
    }

    /**
     * 获取加班人员.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getOverTimeData(string $month, string $dateType, array $uid): array
    {
        $data  = [];
        $where = ['month' => $month, 'date_type' => $dateType, 'uid' => $uid, 'apply_type' => ApproveEnum::PERSONNEL_OVERTIME];
        $this->dao->getCountGroupByUid($where)->each(function ($item) use (&$data) {
            $data[$item->uid] = $item->count;
        });
        return $data;
    }

    /**
     * 个人假勤统计
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getPersonLeaveStatistics(string $uuid, array $where): array
    {
        $list                = [];
        [$page, $limit]      = $this->getPageValue();
        $where['uid']        = app()->get(AttendanceClockService::class)->getStatisticsUserId($uuid, (int) $where['uid']);
        $where['apply_type'] = [ApproveEnum::PERSONNEL_HOLIDAY, ApproveEnum::PERSONNEL_SIGN, ApproveEnum::PERSONNEL_OUT, ApproveEnum::PERSONNEL_TRIP];
        unset($where['status'], $where['user_id']);
        $statistics = $this->dao->select($where, ['id', 'uid', 'apply_type', 'others', 'date_type', 'time_type', 'others', 'work_hours', 'start_time', 'created_at'], [], $page, $limit);

        $getStatus = function ($type, $holidayTypeId) {
            return match ($type) {
                ApproveEnum::PERSONNEL_HOLIDAY => $holidayTypeId,
                ApproveEnum::PERSONNEL_SIGN    => -1,
                ApproveEnum::PERSONNEL_TRIP    => -2,
                ApproveEnum::PERSONNEL_OUT     => -3,
                default                        => 0
            };
        };

        $tz = config('app.timezone');
        foreach ($statistics as $item) {
            $date          = Carbon::parse($item->start_time ?: $item->created_at, $tz)->toDateString();
            $holidayTypeId = $item->others['holiday_type_id'] ?? '';
            $status        = $getStatus($item->apply_type, $holidayTypeId);

            if ($item->apply_type == ApproveEnum::PERSONNEL_SIGN) {
                $workType = $item->others['record_id'] % 2 == 0 ? 0 : 1;
            } else {
                $workType = $item->apply_type == ApproveEnum::PERSONNEL_OVERTIME ? (int) $item->others['calc_type'] : 0;
            }
            $type = $item->apply_type . '_' . $workType . '_' . $holidayTypeId;

            if (! isset($list[$date])) {
                $list[$date] = [
                    'id'      => $item->id,
                    'date'    => $date,
                    'details' => [
                        $type => [
                            'work_hours' => $item->apply_type == ApproveEnum::PERSONNEL_SIGN ? '1' : $item->work_hours,
                            'work_type'  => $workType,
                            'time_type'  => $item->time_type,
                            'status'     => $status,
                        ],
                    ],
                ];
            } else {
                if (isset($list[$date]['details'][$type])) {
                    $list[$date]['details'][$type]['work_hours'] = $item->apply_type == ApproveEnum::PERSONNEL_SIGN
                        ? bcadd($list[$date]['details'][$type]['work_hours'], '1')
                        : bcadd($list[$date]['details'][$type]['work_hours'], $item->work_hours, 1);
                } else {
                    $list[$date]['details'][$type] = [
                        'work_hours' => $item->apply_type == ApproveEnum::PERSONNEL_SIGN ? '1' : $item->work_hours,
                        'work_type'  => $workType,
                        'time_type'  => $item->time_type,
                        'status'     => $status,
                    ];
                }
            }
        }

        foreach ($list as &$item) {
            $item['details'] = array_values($item['details']);
        }
        return array_values($list);
    }

    /**
     * 按月获取假勤次数.
     * @throws BindingResolutionException
     */
    public function getLeaveNumByMonth(array|int $uid, string $month, int $applyType, int $holidayTypeId = 0): int
    {
        $where = ['uid' => $uid, 'month' => $month, 'apply_type' => $applyType];
        if ($holidayTypeId) {
            $where['others->holiday_type_id'] = $holidayTypeId;
        }
        return $this->dao->count($where);
    }

    /**
     * 按时间获取假勤次数.
     * @throws BindingResolutionException
     */
    public function getLeaveNumByTime(array|int $uid, string $time, int $applyType, int $holidayTypeId = 0): int
    {
        $where = ['uid' => $uid, 'apply_type' => $applyType, 'time' => $time];
        if ($holidayTypeId) {
            $where['others->holiday_type_id'] = $holidayTypeId;
        }
        return $this->dao->count($where);
    }

    /**
     * 假勤统计
     * @return array[]
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getPersonLeaveMonthStatistics(array|int $uid, string $month): array
    {
        $data = [
            [
                'num'    => $this->getLeaveNumByMonth($uid, $month, ApproveEnum::PERSONNEL_SIGN),
                'status' => -1,
                'name'   => '申请补卡',
            ],
            [
                'num'    => $this->getLeaveNumByMonth($uid, $month, ApproveEnum::PERSONNEL_TRIP),
                'status' => -2,
                'name'   => '出差',
            ],
            [
                'num'    => $this->getLeaveNumByMonth($uid, $month, ApproveEnum::PERSONNEL_OUT),
                'status' => -3,
                'name'   => '外出',
            ],
        ];
        $holidayTypeList = app()->get(ApproveHolidayTypeService::class)->getTypeList();
        foreach ($holidayTypeList as $item) {
            $data[] = [
                'num'    => $this->getLeaveNumByMonth($uid, $month, ApproveEnum::PERSONNEL_HOLIDAY, $item['id']),
                'status' => $item['id'],
                'name'   => $item['name'],
            ];
        }

        return $data;
    }

    /**
     * 加班统计
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getOvertimeByDateType(array|int $uid, string $month, int $dateType): string
    {
        $where = ['uid' => $uid, 'apply_type' => ApproveEnum::PERSONNEL_OVERTIME, 'date_type' => $dateType, 'month' => $month];
        return $this->getSumByTimeType($where);
    }

    /**
     * 加班次数统计
     * @throws BindingResolutionException
     */
    public function getOvertimeNumByDateType(array|int $uid, string $month, int $dateType): int
    {
        $where = ['uid' => $uid, 'apply_type' => ApproveEnum::PERSONNEL_OVERTIME, 'date_type' => $dateType, 'month' => $month];
        return $this->dao->count($where, ['date_type', 'time_type', 'work_hours']);
    }

    /**
     * 获取假勤人员.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getLeaveData(array $uid, string $month, array $status = []): array
    {
        $data      = [];
        $applyType = [ApproveEnum::PERSONNEL_SIGN, ApproveEnum::PERSONNEL_TRIP, ApproveEnum::PERSONNEL_OUT, ApproveEnum::PERSONNEL_HOLIDAY];
        $this->dao->getCountGroupByUid(['uid' => $uid, 'month' => $month, 'apply_type' => $applyType])->each(function ($item) use (&$data) {
            $data[$item->uid] = $item->count;
        });
        return $data;
    }

    /**
     * 按月统计申请.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getSumByMonth(int $uid, string $month, int $applyType, string $timeType = 'hour'): string
    {
        $where = ['uid' => $uid, 'apply_type' => $applyType, 'month' => $month];
        return Cache::tags([self::CACHE_KEY])->remember(md5(json_encode($where)), (int) sys_config('system_cache_ttl', 3600), function () use ($where, $timeType) {
            return $this->getSumByTimeType($where, $timeType);
        });
    }

    /**
     * 按时间统计申请.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getSumByTime(int $uid, string $time, int $applyType, string $timeType = 'day'): string
    {
        $where = ['uid' => $uid, 'apply_type' => $applyType, 'time' => $time];
        return $this->getSumByTimeType($where, $timeType);
    }

    /**
     * 时间类型统计
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getSumByTimeType(array $where, string $timeType = 'hour'): string
    {
        $time = '0.00';
        $list = $this->dao->setTimeField('start_time')->select($where, ['date_type', 'time_type', 'work_hours']);
        foreach ($list as $item) {
            $workHours = $timeType == $item->time_type ? $item->work_hours : $this->getWorkHours($item->time_type, $timeType, $item->work_hours);
            $time      = bcadd($workHours, $time, 2);
        }

        return $time;
    }

    /**
     * 获取工时.
     */
    public function getWorkHours(string $nowType, string $wantType, string $workHours): string
    {
        return match ($nowType . '_' . $wantType) {
            'hour_day'    => bcdiv($workHours, '24', 2),
            'hour_minute' => bcmul($workHours, '3600', 2),
            'day_hour'    => bcmul($workHours, '24', 2),
            'day_minute'  => bcmul($workHours, '86400', 2),
            'minute_hour' => bcdiv($workHours, '3600', 2),
            'minute_day'  => bcdiv($workHours, '86400', 2),
            default       => $workHours
        };
    }

    /**
     * 按时间获取假勤人/次数.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getLeaveNumByTimeByUid(array|int $uid, int $applyType, string $time, string $timeType = 'date', bool $isDistinct = false): int
    {
        $obj = $this->dao->search(['uid' => $uid, 'apply_type' => $applyType, $timeType => $time]);
        if ($isDistinct) {
            $obj->distinct('uid');
        }
        return $obj->count();
    }

    /**
     * 申请记录.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function createApplyRecord(int $id): void
    {
        $applyInfo = app()->get(ApproveApplyService::class)->get($id, ['id', 'user_id', 'status', 'approve_id'], ['approve', 'content']);

        if (! $applyInfo || $applyInfo->status != 1) {
            return;
        }

        $applyInfo = $applyInfo->toArray();

        $typeField = match ($applyInfo['approve']['types']) {
            ApproveEnum::PERSONNEL_OVERTIME => 'overtimeFrom',
            ApproveEnum::PERSONNEL_OUT      => 'outFrom',
            ApproveEnum::PERSONNEL_HOLIDAY  => 'leaveDuration',
            ApproveEnum::PERSONNEL_TRIP     => 'tripFrom',
            ApproveEnum::PERSONNEL_SIGN     => 'refillFrom',
            default                         => ''
        };
        if (! $typeField) {
            return;
        }
        $this->createRecord($applyInfo['user_id'], $applyInfo);
    }

    /**
     * 保存申请记录.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function createRecord(int $uid, array $apply): mixed
    {
        $tz = config('app.timezone');

        $applyId = $apply['id'];
        $type    = $apply['approve']['types'];

        // other options
        $others = [];

        $duration = 0;
        $timeType = 'day';
        $endTime  = $startTime = '';

        foreach ($apply['content'] as $item) {
            if ($item['symbol'] == 'attendanceExceptionDate') {
                $others['abnormal_id'] = (int) $item['value'];
            }

            if ($item['symbol'] == 'attendanceExceptionRecord') {
                $others['record_id'] = (int) $item['value'];
            }

            if (isset($item['value']['duration'])) {
                $duration = $item['value']['duration'];
            }

            if (isset($item['value']['dateStart'], $item['value']['dateEnd'])) {
                $startObj = Carbon::parse($item['value']['dateStart'], $tz);
                $endObj   = Carbon::parse($item['value']['dateEnd'], $tz);

                if ($item['value']['timeType'] == 'day') {
                    $startTime = $startObj->format('Y-m-d ' . ($item['value']['timeStart'] ? '00:00:00' : '12:00:00'));
                    $endTime   = $endObj->format('Y-m-d ' . ($item['value']['timeEnd'] ? '12:00:01' : '23:59:59'));
                } else {
                    $timeType  = 'hour';
                    $startTime = $startObj->toDateTimeString();
                    $endTime   = $endObj->toDateTimeString();
                }
            }

            if ($type == ApproveEnum::PERSONNEL_OVERTIME && isset($item['content']['title']) && $item['content']['title'] == '加班补贴') {
                $others['calc_type'] = $item['value'] == '调休' ? 1 : 2;
            }

            if ($type == ApproveEnum::PERSONNEL_HOLIDAY && $item['symbol'] == 'holidayType' && $item['types'] == 'select') {
                $others['holiday_type_id'] = (int) $item['value'];
            }
        }

        return $this->transaction(function () use ($uid, $type, $applyId, $startTime, $endTime, $duration, $timeType, $tz, $others) {
            $statisticsService = app()->get(AttendanceStatisticsService::class);
            if ($type == ApproveEnum::PERSONNEL_SIGN && isset($others['abnormal_id'])) {
                $startTime = $statisticsService->value($others['abnormal_id'], 'created_at')->toDateString();
            }

            $res = $this->dao->create([
                'uid'        => $uid,
                'work_hours' => $duration,
                'time_type'  => $timeType,
                'date_type'  => app()->get(AttendanceArrangeService::class)->dayIsRest($uid, $startTime) ? 2 : 1,
                'apply_id'   => $applyId,
                'start_time' => $startTime ?: null,
                'end_time'   => $endTime ?: null,
                'apply_type' => $type,
                'others'     => $others,
            ]);

            // update abnormal attendance
            if (isset($others['record_id']) && $others['record_id'] > 0) {
                --$others['record_id'];
            }

            $statisticsService->updateAbnormalShiftStatus($uid, $type, $startTime, $endTime, $tz, $others);

            if ($res) {
                $tags = [self::CACHE_KEY];

                // save statistics leave duration
                if ($type == ApproveEnum::PERSONNEL_HOLIDAY) {
                    $statisticsService->calcLeaveDurationByTime($uid, $res->id, $others['holiday_type_id'], $startTime, $endTime);
                    $tags[] = AttendanceStatisticsLeaveService::CACHE_KEY;
                }

                Cache::tags($tags)->flush();
            }
            return $res;
        });
    }

    /**
     * 变更考勤记录.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function updateAbnormalShiftStatusByApplyRecord(int $userId, string $compareTime): void
    {
        $tz   = config('app.timezone');
        $list = $this->dao->select(['uid' => $userId, 'compare_time' => $compareTime]);

        $statisticsService = app()->get(AttendanceStatisticsService::class);
        foreach ($list as $item) {
            $statisticsService->updateAbnormalShiftStatus($userId, $item->apply_type, $item->start_time, $item->end_time, $tz, $item->others);
        }
    }

    /**
     * 指定范围内考勤类型数量.
     * @throws BindingResolutionException
     */
    public function getCountByApplyType(int $userId, string $compareTime, int $applyType): int
    {
        return $this->dao->count(['uid' => $userId, 'compare_time' => $compareTime, 'apply_type' => $applyType]);
    }

    /**
     * 更新 请假/出差/外出/加班 考勤数据.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function calcApplyRecordTime(string $date): void
    {
        $type = [ApproveEnum::PERSONNEL_HOLIDAY, ApproveEnum::PERSONNEL_OVERTIME, ApproveEnum::PERSONNEL_OUT, ApproveEnum::PERSONNEL_TRIP];
        $list = $this->dao->select(['compare_time' => $date . ' 00:00:00', 'apply_type' => $type]);
        if ($list->isEmpty()) {
            return;
        }

        $tags = [];
        $tz   = config('app.timezone');

        $statisticsService = app()->get(AttendanceStatisticsService::class);
        foreach ($list as $item) {
            // update abnormal attendance
            $statisticsService->updateAbnormalShiftStatus($item->uid, $item->apply_type, $item->start_time, $item->end_time, $tz, $item->others);

            // save statistics leave duration
            if ($item->apply_type == ApproveEnum::PERSONNEL_HOLIDAY) {
                $statisticsService->calcLeaveDurationByTime($item->uid, $item->id, $item->others['holiday_type_id'], $item->start_time, $item->end_time);
                empty($tags) && $tags[] = AttendanceStatisticsLeaveService::CACHE_KEY;
            }
        }
        $tags && Cache::tags($tags)->flush();
    }
}
