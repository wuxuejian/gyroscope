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
use App\Constants\AttendanceClockEnum;
use App\Http\Dao\Attendance\AttendanceHandleRecordDao;
use App\Http\Dao\Attendance\AttendanceStatisticsDao;
use App\Http\Service\Admin\AdminInfoService;
use App\Http\Service\Approve\ApproveHolidayTypeService;
use App\Http\Service\BaseService;
use App\Http\Service\Frame\FrameService;
use App\Http\Service\System\RolesService;
use crmeb\utils\Regex;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * 考勤统计
 * Class AttendancePersonnelStatisticsService.
 */
class AttendancePersonnelStatisticsService extends BaseService
{
    private AttendanceHandleRecordDao $handleRecordDao;

    public function __construct(AttendanceStatisticsDao $dao, AttendanceHandleRecordDao $handleRecordDao)
    {
        $this->dao             = $dao;
        $this->handleRecordDao = $handleRecordDao;
    }

    /**
     * 过滤where条件.
     */
    public function filterWhere(string $uuid, array $where, int $type): array
    {
        return Cache::remember(md5(json_encode(['uuid' => $uuid, 'where' => $where, 'type' => $type])), 30, function () use ($uuid, $where, $type) {
            $groupService = app()->get(AttendanceGroupService::class);

            $uid          = (int) uuid_to_uid($uuid);
            $whiteMembers = $groupService->getWhiteListMemberIds();
            if ($type) {
                $userId = 0;
                if (isset($where['user_id'])) {
                    $userId = $where['user_id'];
                    unset($where['user_id']);
                }
                $members = array_unique(array_merge($groupService->getTeamMember($uid), $whiteMembers));
                if ($userId && $uid != $userId && array_diff($userId, $members)) {
                    throw $this->exception('不能查看该员工考勤数据');
                }
                $where['uid'] = $userId ?: $uid;
            } else {
                $userId = [];
                if (isset($where['user_id'])) {
                    $userId = array_filter(array_map('intval', $where['user_id']));
                    unset($where['user_id']);
                }

                $where['uid'] = $uid;

                $members = array_unique(array_merge(app()->get(RolesService::class)->getDataUids($uid), $whiteMembers, [$uid]));
                if ($userId) {
                    $where['uid'] = array_intersect($userId, $members);
                } elseif (isset($where['scope'])) {
                    $frameService = app()->get(FrameService::class);

                    $frameId = intval($where['frame_id'] ?? '');
                    $groupId = intval($where['group_id'] ?? '');
                    $isAdmin = $groupService->isWhiteListAdmin($uid);

                    // 部门人员
                    if ($frameId) {
                        $frameUserIds          = $frameService->getIdsByFrameIds($uid, [$frameId]);
                        ! $isAdmin && $members = array_intersect($frameUserIds, $members);
                    }
                    // 考勤组人员
                    if ($groupId) {
                        $groupUserIds          = $groupService->getMemberIdsById($groupId);
                        ! $isAdmin && $members = array_intersect($groupUserIds, $members);
                    }

                    $departUserIds = app()->get(AdminInfoService::class)->column(['type' => 4], 'id');
                    $where['uid']  = match ((int) $where['scope']) {
                        1       => array_diff($members, $departUserIds),
                        2       => $departUserIds,
                        default => array_merge($members, $departUserIds)
                    };
                }
            }
            return $where;
        });
    }

    /**
     * 每日统计
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getDailyStatistics(string $uuid, array $where, int $type = 0): array
    {
        [$page, $limit] = $this->getPageValue();
        $where          = $this->filterWhere($uuid, $where, $type);
        $list           = $this->dao->getList($where, ['*'], $page, $limit, 'id', ['card', 'frame'], function ($list) {
            $tz            = config('app.timezone');
            $shifts        = AttendanceClockEnum::SHIFT_CLASS;
            $shiftService  = app()->get(AttendanceShiftService::class);
            $recordService = app()->get(AttendanceApplyRecordService::class);
            $leaveService  = app()->get(AttendanceStatisticsLeaveService::class);
            foreach ($list as $item) {
                for ($n = 0; $n < 4; ++$n) {
                    $item[$shifts[$n] . '_shift_normal'] = 0;
                }

                $date                        = $item->created_at->toDateString();
                $item['leave_time']          = $leaveService->getLeaveDurationByDate((int) $item->uid, $date);
                $item['overtime_work_hours'] = $recordService->getSumByTimeType(['uid' => $item->uid, 'over_time' => $date, 'apply_type' => ApproveEnum::PERSONNEL_OVERTIME]);
                if ($item->shift_id < 2) {
                    continue;
                }

                $shiftNum = $item->shift_data['number'] * 2;
                for ($i = 0; $i < $shiftNum; ++$i) {
                    $field  = $shifts[$i];
                    $status = $item->{$field . '_shift_status'};
                    $minute = in_array($status, AttendanceClockEnum::LATE_AND_LEAVE_EARLY)
                        ? $shiftService->getNormalMinutes($item->created_at->toDateTimeString(), $item->shift_data, $status, $i, (string) $item->{$field . '_shift_time'}, $tz) : 0;
                    $minute && $item->{$field . '_shift_normal'} = $minute;
                }
            }
        });
        $count = $this->dao->count($where);
        return $this->listData($list, $count);
    }

    /**
     * 获取处理记录.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function getRecordList(string $uuid, int $statisticsId): array
    {
        $where['uid']   = app()->get(AttendanceGroupService::class)->getTeamMember($uuid);
        [$page, $limit] = $this->getPageValue();
        $where          = ['statistics_id' => $statisticsId];
        $list           = $this->handleRecordDao->getList($where, ['id', 'shift_number', 'result', 'remark', 'source', 'uid', 'created_at'], $page, $limit, 'id', ['card']);
        $count          = $this->handleRecordDao->count($where);
        return $this->listData($list, $count);
    }

    /**
     * 月度统计
     * @param null|mixed $sort
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function getMonthlyStatistics(string $uuid, array $where, array $field = ['id', 'uid', 'frame_id'], $sort = null, array $with = ['card']): array
    {
        if ($where['month'] && ! preg_match(Regex::MONTH_TIME_RULE, $where['month'])) {
            throw $this->exception('考勤时间格式错误');
        }

        $tz      = config('app.timezone');
        $where   = $this->filterWhere($uuid, $where, 0);
        $dateObj = ($where['month'] ? Carbon::parse($where['month'], $tz) : Carbon::now($tz));

        $month          = $dateObj->format('Y-m');
        [$page, $limit] = $this->getPageValue();
        $list           = $this->dao->getStatisticsMemberList($where, $field, $with, $page, $limit, $sort);
        $requiredDays   = app()->get(AttendanceArrangeService::class)->getRequiredDaysByUids(
            array_column($list->toArray(), 'uid'),
            Carbon::parse($dateObj, $tz)->startOfMonth(),
            Carbon::parse($dateObj, $tz)->endOfMonth()
        );

        $frameService  = app()->get(FrameService::class);
        $recordService = app()->get(AttendanceApplyRecordService::class);
        $leaveService  = app()->get(AttendanceStatisticsLeaveService::class);

        // holiday type list with destroy
        $prefetchTypeIds = $leaveService->getHolidayTypeIdByMonth($where['month'], $where['uid']);
        $holidayTypeList = app()->get(ApproveHolidayTypeService::class)->getStatisticsListWithDestroy($prefetchTypeIds);
        foreach ($list as $item) {
            [$absenteeism, $lateCard, $earlyCard, $actualDays] = $this->getLackCardAndAbsenteeismNum($item->uid, $month);
            $item['required_days']                             = $requiredDays[$item->uid] ?? 0;
            $item['actual_days']                               = $actualDays;
            $item['late']                                      = $this->dao->count(['month' => $month, 'uid' => $item->uid, 'status' => AttendanceClockEnum::ALL_LATE]);
            $item['leave_early']                               = $this->dao->count(['month' => $month, 'uid' => $item->uid, 'status' => AttendanceClockEnum::LEAVE_EARLY]);
            $item['late_card']                                 = $lateCard;
            $item['early_card']                                = $earlyCard;
            $item['absenteeism']                               = $absenteeism;
            $item['overtime_hours']                            = $recordService->getSumByMonth($item->uid, $month, ApproveEnum::PERSONNEL_OVERTIME);
            $item['trip_hours']                                = $recordService->getSumByMonth($item->uid, $month, ApproveEnum::PERSONNEL_TRIP);
            $item['out_hours']                                 = $recordService->getSumByMonth($item->uid, $month, ApproveEnum::PERSONNEL_OUT);
            $item['frame']                                     = toArray($frameService->get(['id' => $this->dao->value(['uid' => $item->uid], 'frame_id')], ['id', 'name']));
            $item['group']                                     = $this->dao->value(['uid' => $item->uid], 'group');

            $holidayData = [];
            foreach ($holidayTypeList as $type) {
                $holidayData[] = [
                    'id'       => $type['id'],
                    'duration' => $leaveService->getMonthLeaveDurationByHolidayTypeId($item->uid, $month, $type['id'], $type['duration_type']),
                ];
            }
            $item['holiday_data'] = $holidayData;
        }

        $count = $this->dao->search($where)->distinct('uid')->count();
        return $this->listData($list, $count, ['holiday_type' => $holidayTypeList]);
    }

    /**
     * 更新打卡结果.
     * @throws BindingResolutionException
     */
    public function saveStatisticsResult(string $uuid, int $statisticsId, array $data): mixed
    {
        $uid  = uuid_to_uid($uuid);
        $info = $this->dao->get($statisticsId);
        if (! $info) {
            throw $this->exception('common.operation.noExists');
        }

        return $this->transaction(function () use ($uid, $statisticsId, $data, $info) {
            $shifts = AttendanceClockEnum::SHIFT_CLASS;

            // 内勤卡去掉地点状态
            if ($info->{$shifts[$data['number']] . '_shift_location_status'} < 1) {
                $data['location_status'] = 0;
            }

            $beforeStatus         = $info->{$shifts[$data['number']] . '_shift_status'};
            $beforeLocationStatus = $info->{$shifts[$data['number']] . '_shift_location_status'};

            $number = (int) $data['number'];
            if ($data['status'] != $beforeStatus) {
                if ($info->shift_id > 1) {
                    [$time, $isAfter]                                     = $this->getClockData($info, $number, (int) $data['status']);
                    $info->{$shifts[$data['number']] . '_shift_time'}     = $time ?? null;
                    $info->{$shifts[$data['number']] . '_shift_is_after'} = $isAfter;
                }
                $info->{$shifts[$data['number']] . '_shift_status'} = $data['status'];
            }

            if ($data['location_status'] && $data['location_status'] != $beforeLocationStatus) {
                $info->{$shifts[$data['number']] . '_shift_location_status'} = $data['location_status'];
            }

            $info->actual_work_hours = app()->get(AttendanceShiftService::class)->getActualWorkHours($info, $info->created_at->toDateString());
            $info->save();

            return $this->handleRecordDao->create([
                'statistics_id'          => $statisticsId,
                'shift_number'           => $data['number'],
                'remark'                 => $data['remark'],
                'result'                 => $this->getResultText($data, $beforeStatus, $beforeLocationStatus),
                'source'                 => 0,
                'before_status'          => $beforeStatus,
                'before_location_status' => $beforeLocationStatus,
                'after_status'           => $data['status'],
                'after_location_status'  => $data['location_status'],
                'uid'                    => $uid,
            ]);
        });
    }

    /**
     * 出勤统计
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getAttendanceStatistics(string $uuid, array $where): array
    {
        $where                                                                = $this->filterWhere($uuid, $where, 1);
        [$absenteeism, $lateCard, $earlyCard, $actualDays, $locationAbnormal] = $this->getLackCardAndAbsenteeismNum($where['uid'], $where['time'], false);

        $tz = config('app.timezone');
        if ($where['time'] == 'month') {
            $startObj = Carbon::today($tz)->startOfMonth();
            $endObj   = Carbon::today($tz)->endOfMonth();
        } else {
            [$startTime, $endTime] = explode('-', $where['time']);
            $startObj              = Carbon::parse($startTime, $tz);
            $endObj                = Carbon::parse($endTime, $tz);
        }
        $time = $startObj->format('Y/m/d') . '-' . $endObj->format('Y/m/d');

        $recordService = app()->get(AttendanceApplyRecordService::class);
        $leaveService  = app()->get(AttendanceStatisticsLeaveService::class);

        return [
            'required_days'     => app()->get(AttendanceArrangeService::class)->getRequiredDays($where['uid'], $time, $startObj, $endObj),
            'normal_days'       => $this->dao->count(['uid' => $where['uid'], 'time' => $time, 'status_gt' => 0]),
            'work_hours'        => sprintf('%.1f', $this->dao->avg($where, 'actual_work_hours')),
            'leave_hours'       => $leaveService->getLeaveDurationByDate($where['uid'], $time, 'time'),
            'out_hours'         => $recordService->getSumByTime($where['uid'], $time, ApproveEnum::PERSONNEL_OUT, 'hour'),
            'trip_hours'        => $recordService->getSumByTime($where['uid'], $time, ApproveEnum::PERSONNEL_TRIP),
            'overtime_hours'    => $recordService->getSumByTime($where['uid'], $time, ApproveEnum::PERSONNEL_OVERTIME, 'hour'),
            'sign'              => $recordService->getLeaveNumByTime($where['uid'], $time, ApproveEnum::PERSONNEL_SIGN),
            'late'              => $this->dao->count(array_merge($where, ['status' => AttendanceClockEnum::LATE])),
            'extreme_late'      => $this->dao->count(array_merge($where, ['status' => AttendanceClockEnum::EXTREME_LATE])),
            'early_leave'       => $this->dao->count(array_merge($where, ['status' => AttendanceClockEnum::LEAVE_EARLY])),
            'lack_card'         => $lateCard + $earlyCard,
            'absenteeism'       => $absenteeism,
            'location_abnormal' => $locationAbnormal,
        ];
    }

    /**
     * 获取打卡结果.
     */
    private function getResultText(array $data, int $beforeStatus, int $beforeLocationStatus): string
    {
        if ($beforeStatus == $data['status']
            && ($beforeLocationStatus && $beforeLocationStatus == $data['location_status'])) {
            throw $this->exception('打卡状态不能更新异常');
        }

        $beforeLocationText = AttendanceClockEnum::getLocationStatusText($beforeLocationStatus);
        $afterLocationText  = AttendanceClockEnum::getLocationStatusText((int) $data['location_status']);

        return AttendanceClockEnum::getStatusText($beforeStatus)
               . ($beforeLocationText ? '(' . $beforeLocationText . ')' : '') . ' > '
               . AttendanceClockEnum::getStatusText((int) $data['status'])
               . ($afterLocationText ? '(' . $afterLocationText . ')' : '');
    }

    /**
     * 获取打卡数据.
     * @param mixed $info
     */
    private function getClockData($info, int $number, int $status): array
    {
        $tz      = config('app.timezone');
        $isWork  = in_array($number, [0, 2]);
        $date    = Carbon::parse($info->created_at, $tz)->toDateString();
        $rule    = $info->shift_data['rules'][$number > 1 ? 1 : 0];
        $dateObj = Carbon::parse($date . ' ' . $rule[$isWork ? 'work_hours' : 'off_hours'] . ':00', $tz);
        $isAfter = 0;
        if ($rule[['first', 'second'][$isWork ? 0 : 1] . '_day_after']) {
            $isAfter = 1;
            $dateObj->addDay();
        }

        if ($status == 5) {
            return ['', $isAfter];
        }

        return [(match ($status) {
            2       => $dateObj->addSeconds($rule['late'] + 1),
            3       => $dateObj->addSeconds($rule['extreme_late'] + 1),
            4       => $dateObj->subSeconds($rule['early_leave'] - 1),
            default => $dateObj
        })->toDateTimeString(), $isAfter];
    }

    /**
     * 获取出勤天次/旷工/缺卡数量.
     * @return int[]
     * @throws \ReflectionException
     * @throws BindingResolutionException
     */
    private function getLackCardAndAbsenteeismNum(array|int $uid, string $date, bool $isMonth = true): array
    {
        $actualDays = '0.0';
        $lateCard   = $absenteeism = $earlyCard = $locationAbnormal = 0;
        $shifts     = AttendanceClockEnum::SHIFT_CLASS;
        $where      = ['uid' => $uid];
        if ($isMonth) {
            $where['month'] = $date;
        } else {
            $where['time'] = $date;
        }
        $records = $this->dao->select($where, ['*']);
        foreach ($records as $record) {
            if ($record->shift_id < 2) {
                continue;
            }
            $lateNum  = $absentNum = $earlyNum = $locationAbnormalNum = 0;
            $shiftNum = $record->shift_data['number'] * 2;
            for ($i = 0; $i < $shiftNum; ++$i) {
                $shiftStatus         = $record->{$shifts[$i] . '_shift_status'};
                $shiftLocationStatus = $record->{$shifts[$i] . '_shift_location_status'};
                if ($shiftStatus == AttendanceClockEnum::LACK_CARD) {
                    if (in_array($i, [0, 2])) {
                        ++$lateNum;
                    } else {
                        ++$earlyNum;
                    }
                    ++$absentNum;
                }

                if ($shiftStatus == AttendanceClockEnum::LATE_LACK_CARD) {
                    ++$lateNum;
                }

                if ($shiftStatus == AttendanceClockEnum::EARLY_LACK_CARD) {
                    ++$earlyNum;
                }

                if ($shiftLocationStatus == AttendanceClockEnum::OFFICE_ABNORMAL) {
                    ++$locationAbnormalNum;
                }
            }

            if ($record->actual_work_hours >= 0.1 && $record->required_work_hours >= 0.1) {
                $hours      = bcdiv((string) $record->actual_work_hours, (string) $record->required_work_hours, 2);
                $actualDays = bcadd($actualDays, (string) (floor($hours * pow(10, 1)) / pow(10, 1)), 1);
            }

            if ($absentNum == $shiftNum) {
                ++$absenteeism;
            } else {
                $lateCard += $lateNum;
                $earlyCard += $earlyNum;
                $locationAbnormal += $locationAbnormalNum;
            }
        }
        return [$absenteeism, $lateCard, $earlyCard, $actualDays, $locationAbnormal];
    }
}
