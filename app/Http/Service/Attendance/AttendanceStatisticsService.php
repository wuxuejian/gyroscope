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

namespace App\Http\Service\Attendance;

use App\Constants\ApproveEnum;
use App\Constants\AttendanceClockEnum;
use App\Http\Dao\Attendance\AttendanceStatisticsDao;
use App\Http\Model\BaseModel;
use App\Http\Service\Admin\AdminService;
use App\Http\Service\Approve\ApproveApplyService;
use App\Http\Service\Approve\ApproveService;
use App\Http\Service\BaseService;
use App\Http\Service\Frame\FrameService;
use Carbon\CarbonPeriod;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * 考勤统计
 * Class AttendanceStatisticsService.
 */
class AttendanceStatisticsService extends BaseService
{
    public function __construct(AttendanceStatisticsDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 打卡记录数据.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getUserRecordByDate(int $uid, string $date): mixed
    {
        $info = $this->dao->get(['uid' => $uid, 'date' => $date]);
        if ($info) {
            return $info;
        }

        // 考勤班次
        $shiftService = app()->get(AttendanceShiftService::class);

        $isWhitelist = app()->get(AttendanceGroupService::class)->isWhitelist($uid);
        if (! $isWhitelist) {
            // 考勤班次
            $clockGroup = app()->get(AttendanceClockService::class)->getClockBasicByUid($uid, $date);
            $shift      = $shiftService->getArrangeShiftById($clockGroup['shift_id'], $date);
        } else {
            $shift      = [];
            $clockGroup = ['group_id' => 0, 'group_name' => '', 'shift_id' => 0];
        }
        return (object) [
            'uid'                         => $uid,
            'frame_id'                    => app()->get(FrameService::class)->getFrameIdByUserId($uid),
            'group_id'                    => $clockGroup['group_id'],
            'group'                       => $clockGroup['group_name'],
            'shift_id'                    => $clockGroup['shift_id'],
            'shift_data'                  => $shift,
            'required_work_hours'         => $shiftService->getRequiredAttendanceHours($shift, $date),
            'one_shift_time'              => null,
            'one_shift_is_after'          => 0,
            'one_shift_status'            => 0,
            'one_shift_location_status'   => 0,
            'one_shift_record_id'         => 0,
            'two_shift_time'              => null,
            'two_shift_is_after'          => 0,
            'two_shift_status'            => 0,
            'two_shift_location_status'   => 0,
            'two_shift_record_id'         => 0,
            'three_shift_time'            => null,
            'three_shift_is_after'        => 0,
            'three_shift_status'          => 0,
            'three_shift_location_status' => 0,
            'three_shift_record_id'       => 0,
            'four_shift_time'             => null,
            'four_shift_is_after'         => 0,
            'four_shift_status'           => 0,
            'four_shift_location_status'  => 0,
            'four_shift_record_id'        => 0,
        ];
    }

    /**
     * 打卡数据.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function renewStatistics(int $uid, Carbon $dateObj): mixed
    {
        $date = $dateObj->toDateString();
        $info = $this->dao->get(['uid' => $uid, 'date' => $date]);
        if ($info) {
            return $info;
        }

        return $this->createStatistics($uid, $date);
    }

    /**
     * 核对打卡记录.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function checkClockRecord($info, Carbon $dateObj, int $status, int $clockNumber, $tz): array
    {
        if (! app()->get(AttendanceArrangeService::class)->dayIsRest($info->uid, $dateObj->toDateString()) && $info->shift_id > 1) {
            $isUpdate  = false;
            $shifts    = AttendanceClockEnum::SHIFT_CLASS;
            $isSameDay = now($tz)->dayOfYear == $dateObj->dayOfYear;
            for ($i = 0; $i <= $info->shift_data['number'] * 2 - 1; ++$i) {
                if ($isSameDay
                    && $info->{$shifts[$i] . '_shift_status'} < 1
                    && is_null($info->{$shifts[$i] . '_shift_time'})
                    && $dateObj->timestamp > $this->getClockEndTime((int) $info->uid, $info, $i, $tz)) {
                    $info     = $this->updateShiftStatistics($info, $dateObj, $i, withFreeClock: true);
                    $isUpdate = true;
                }
            }

            if ($isUpdate) {
                [$status, $clockNumber] = $this->getClockNumber($dateObj, $info, $tz);
            }
        }
        return [$info, $status, $clockNumber];
    }

    /**
     * 获取打卡记录.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getClockRecord($info, int $clockNumber, $tz, bool $isRest = false, bool $isReal = false): array
    {
        $list   = [];
        $nowObj = now($tz);
        $shifts = AttendanceClockEnum::SHIFT_CLASS;

        for ($i = 0; $i <= $clockNumber; ++$i) {
            if ($isRest) {
                continue;
            }

            $clockTime = $info->{$shifts[$i] . '_shift_time'};

            $record = [
                'number'          => $i,
                'clock_time'      => $clockTime ? Carbon::parse($clockTime, $tz)->format('H:i') : '',
                'location_status' => $info->{$shifts[$i] . '_shift_location_status'} ?? 0,
                'status'          => $info->{$shifts[$i] . '_shift_status'} ?? 0,
                'record_id'       => $info->{$shifts[$i] . '_shift_record_id'} ?? 0,
                'lat'             => '',
                'lng'             => '',
                'remark'          => '',
                'address'         => '',
                'image'           => [],
            ];

            $record['update_status'] = 0;
            if ($clockTime && $nowObj->timestamp < $this->getClockEndTime((int) $info['uid'], $info, $i, $tz)) {
                $record['update_status'] = 1;
            }
            if ($isReal && $record['status'] < 1 && ! $record['clock_time']) {
                continue;
            }

            $list[] = $record;
        }
        return $list;
    }

    /**
     * 获取打卡记录.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getStatisticsByUid(string $uuid): array
    {
        $tz      = config('app.timezone');
        $dateObj = now($tz);
        $uid     = uuid_to_uid($uuid);
        $info    = $this->renewStatistics($uid, $dateObj);

        $isWhitelist            = app()->get(AttendanceGroupService::class)->isWhitelist($uid);
        [$status, $clockNumber] = $this->getClockNumber($dateObj, $info, $tz, $isWhitelist);
        if (! $isWhitelist) {
            [$info, $status, $clockNumber] = $this->checkClockRecord($info, $dateObj, $status, $clockNumber, $tz);
        }
        $list = $this->getStatisticsList($info, $clockNumber, $tz);

        // 计算班次对应时间
        $timestamp = $this->getClockTime($uid, $info, $clockNumber, $tz);
        return [
            'list'            => $list,
            'abnormal'        => $this->dao->count(['month' => $dateObj->format('Y-m'), 'uid' => $uid, 'abnormal_status' => AttendanceClockEnum::NORMAL]),
            'clock_status'    => $isWhitelist ? 1 : ($status == 0 ? $status : $this->getClockStatus($clockNumber, $dateObj, $info)),
            'clock_timestamp' => $timestamp,
        ];
    }

    /**
     * 最后班次打卡截止时间.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getLastShiftClockTimeObj(int $uid, array $rule, string $date, string $tz = ''): Carbon
    {
        if (app()->get(AttendanceArrangeService::class)->dayIsRest($uid, $date)) {
            $timestamp = $this->getOffWorkObj($rule, $date, $tz)->endOfDay();
        } else {
            // 下个班次提前打卡时间
            $dateObj   = Carbon::parse($date, $tz)->addDay();
            $rule      = $this->renewStatistics($uid, $dateObj)->shift_data['rules'][0];
            $timestamp = $this->getWorkObj($rule, $dateObj->toDateString(), $tz)->subSeconds($rule['early_card']);
        }
        return $timestamp;
    }

    /**
     * 打卡结束时间.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getClockEndTime(int $uid, $info, int $clockNumber, string $tz, bool $withTimestamp = true): mixed
    {
        $endTime        = 0;
        $currentDateObj = Carbon::parse($info->created_at, $tz);
        if ($info->shift_id < 2) {
            $endTime = $currentDateObj->endOfDay();
        } else {
            $date = $currentDateObj->toDateString();

            // 下班打卡截止时间
            $offWorkTimestamp = function (int $uid, array $rule, string $date, string $tz) {
                if ($rule['delay_card']) {
                    return $this->getOffWorkObj($rule, $date, $tz)->addSeconds($rule['delay_card']);
                }
                return $this->getLastShiftClockTimeObj($uid, $rule, $date, $tz);
            };

            $rule = $clockNumber > 1 ? $info->shift_data['rules'][1] : $info->shift_data['rules'][0];
            switch ($clockNumber) {
                case 1:
                    if ($info->shift_data['number'] == 1) {
                        $endTime = $offWorkTimestamp($uid, $rule, $date, $tz);
                    } else {
                        if ($rule['delay_card']) {
                            $endTime = $this->getOffWorkObj($rule, $date, $tz)->addSeconds($rule['delay_card']);
                        } else {
                            $rule    = $info->shift_data['rules'][1];
                            $endTime = $this->getWorkObj($rule, $date, $tz)->subSeconds($rule['early_card']);
                        }
                    }
                    break;
                case 3:
                    $endTime = $offWorkTimestamp($uid, $rule, $date, $tz);
                    break;
                default:
                    $endTime = $this->getWorkObj($rule, $date, $tz)->addSeconds($rule['late_lack_card']);
                    break;
            }
        }
        return $withTimestamp ? $endTime->timestamp : $endTime;
    }

    /**
     * 打卡结束时间.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getClockTime(int $uid, $info, int $clockNumber, string $tz): mixed
    {
        $currentDateObj = Carbon::parse($info->created_at, $tz);
        if ($info->shift_id < 2) {
            return $currentDateObj->endOfDay()->timestamp;
        }

        $date = $currentDateObj->toDateString();
        $rule = $info->shift_data['rules'][$clockNumber > 1 ? 1 : 0];

        $dateObj = now($tz);

        // 提前打卡时间
        if ($dateObj->lt($this->getWorkObj($rule, $date, $tz)->subSeconds($rule['early_card']))) {
            $timestamp = $this->getWorkObj($rule, $date, $tz)->subSeconds($rule['early_card'])->timestamp;
        }

        //  上班时间
        if ($dateObj->gte($this->getWorkObj($rule, $date, $tz)->subSeconds($rule['early_card']))) {
            $timestamp = $this->getWorkObj($rule, $date, $tz)->timestamp;
        }

        // 迟到时间
        if ($dateObj->gte($this->getWorkObj($rule, $date, $tz))) {
            $timestamp = $this->getWorkObj($rule, $date, $tz)->addSeconds($rule['late'])->timestamp;
        }

        // 提前缺卡
        if ($dateObj->gte($this->getWorkObj($rule, $date, $tz)->addSeconds($rule['late']))) {
            $timestamp = $this->getOffWorkObj($rule, $date, $tz)->subSeconds($rule['early_lack_card'])->timestamp;
        }

        // 下班时间
        if ($dateObj->gte($this->getOffWorkObj($rule, $date, $tz)->subSeconds($rule['early_lack_card']))) {
            $timestamp = $this->getOffWorkObj($rule, $date, $tz)->timestamp;
        }

        // 下班结束时间
        if (in_array($clockNumber, [1, 3]) && $dateObj->gte($this->getOffWorkObj($rule, $date, $tz))) {
            $timestamp = $this->getClockEndTime($uid, $info, $clockNumber, $tz);
        }

        return $timestamp ?? 0;
    }

    /**
     * 更新默认考勤.
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function updateDefaultStatistics($info, Carbon $dateObj, array $data): mixed
    {
        $date   = $dateObj->toDateTimeString();
        $shifts = AttendanceClockEnum::SHIFT_CLASS;
        if ($data['update_number'] != '') {
            $clockNumber = (int) $data['update_number'];
            if ($clockNumber == 0 && ! is_null($info->two_shift_time)) {
                throw $this->exception('无法更新打卡');
            }

            $info->{$shifts[$clockNumber] . '_shift_time'}            = $date;
            $info->{$shifts[$clockNumber] . '_shift_record_id'}       = $data['record_id'];
            $info->{$shifts[$clockNumber] . '_shift_location_status'} = $data['location_status'] ?? 0;
        } else {
            $number                                     = is_null($info->one_shift_time) ? 'one' : 'two';
            $info->{$number . '_shift_time'}            = $date;
            $info->{$number . '_shift_record_id'}       = $data['record_id'];
            $info->{$number . '_shift_location_status'} = $data['location_status'] ?? 0;
        }
        $info->actual_work_hours = app()->get(AttendanceShiftService::class)->getActualWorkHours($info, $dateObj->toDateString());
        return $info->save();
    }

    /**
     * 更新考勤数据.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function updateShiftStatistics($info, Carbon $dateObj, int $clockNumber, array $data = [], bool $withFreeClock = false): mixed
    {
        // 当日次日
        $isAfter      = 0;
        $clockStatus  = 1;
        $freeClock    = false;
        $tz           = config('app.timezone');
        $startTimeObj = Carbon::parse($info->created_at, $tz)->endOfDay();
        $dateString   = $startTimeObj->toDateString();
        $shifts       = AttendanceClockEnum::SHIFT_CLASS;
        $rule         = $info->shift_data['rules'][$clockNumber < 2 ? 0 : 1];

        if (isset($data['update_number']) && $data['update_number'] !== '') {
            $clockNumber = (int) $data['update_number'];
            if (in_array($clockNumber, [0, 2])) {
                $locationStatus = $data['location_status'] ?? 0;
                $clockStatus    = $this->getClockStatus($clockNumber, $dateObj, $info);
                if ($data['is_external'] < 1 && $info->{$shifts[$clockNumber] . '_shift_location_status'} == 2 && $locationStatus < 2 && $clockStatus == 1) {
                    $info->{$shifts[$clockNumber] . '_shift_is_after'}        = $dateObj->gt($startTimeObj) ? 1 : 0;
                    $info->{$shifts[$clockNumber] . '_shift_time'}            = $dateObj->toDateTimeString();
                    $info->{$shifts[$clockNumber] . '_shift_record_id'}       = $data['record_id'] ?? 0;
                    $info->{$shifts[$clockNumber] . '_shift_location_status'} = $locationStatus;
                    if (! $info->save()) {
                        throw $this->exception('打卡数据更新异常, 请稍后再试');
                    }
                }

                return $info;
            }
        } else {
            $withFreeClock && $freeClock = in_array($clockNumber, [1, 3]) && $rule['free_clock'] > 0;
        }

        // associated approve
        [$approveFreeClock, $approveLocationStatus] = $this->calcAssociatedApprove($info, $dateString, $clockNumber, $rule, $tz);
        if ($approveFreeClock && ! $freeClock) {
            $freeClock = $approveFreeClock;
        }

        if (isset($data['location_status']) && $data['location_status'] == 2 && $approveLocationStatus == 1) {
            $data['location_status'] = $approveLocationStatus;
        }

        $workObj = $this->{in_array($clockNumber, [0, 2]) ? 'getWorkObj' : 'getOffWorkObj'}($rule, $dateString, $tz);
        // 当前班次免打卡
        if ($freeClock) {
            $info->{$shifts[$clockNumber] . '_shift_time'} = $workObj->toDateTimeString();
        } else {
            $isAfter     = $dateObj->gt($startTimeObj) ? 1 : 0;
            $clockStatus = $this->getClockStatus($clockNumber, $dateObj, $info);
            if ($clockStatus != AttendanceClockEnum::LACK_CARD) {
                $info->{$shifts[$clockNumber] . '_shift_time'} = $dateObj->toDateTimeString();
            }
        }

        $info->{$shifts[$clockNumber] . '_shift_status'}          = $clockStatus;
        $info->{$shifts[$clockNumber] . '_shift_is_after'}        = $isAfter;
        $info->{$shifts[$clockNumber] . '_shift_record_id'}       = $data['record_id'] ?? 0;
        $info->{$shifts[$clockNumber] . '_shift_location_status'} = $data['location_status'] ?? 0;

        $info->actual_work_hours = app()->get(AttendanceShiftService::class)->getActualWorkHours($info, $startTimeObj->toDateString(), $tz);
        if (! $info->save()) {
            throw $this->exception('打卡数据更新异常, 请稍后再试');
        }

        return $info;
    }

    /**
     * 上班打卡时间.
     */
    public function getWorkObj(array $rule, string $date, string $tz): Carbon
    {
        $dateObj = Carbon::parse($date . ' ' . $rule['work_hours'] . ':00', $tz);
        return $rule['first_day_after'] ? $dateObj->addDay() : $dateObj;
    }

    /**
     * 下班打卡时间.
     */
    public function getOffWorkObj(array $rule, string $date, string $tz): Carbon
    {
        $dateObj = Carbon::parse($date . ' ' . $rule['off_hours'] . ':00', $tz);
        return $rule['second_day_after'] ? $dateObj->addDay() : $dateObj;
    }

    /**
     * 获取打卡班次
     */
    public function getClockNumber(Carbon $dateObj, $statistics, string $tz = '', bool $isWhitelist = false): array
    {
        $status = $number = 0;
        $shifts = AttendanceClockEnum::SHIFT_CLASS;
        if ($isWhitelist) {
            $status = 1;
            if (! is_null($statistics->{$shifts[0] . '_shift_time'})) {
                $number = 1;
            }
            return [$status, $number];
        }

        if ($statistics->shift_id < 2) {
            if ($dateObj->lte(Carbon::parse($statistics->created_at, $tz)->endOfDay())) {
                $status = 1;
            }
            if (! is_null($statistics->{$shifts[0] . '_shift_time'})) {
                $number = 1;
            }
            return [$status, $number];
        }

        $tz   = $tz ?: config('app.timezone');
        $rule = $statistics->shift_data['rules'][0];
        $date = Carbon::parse($statistics->created_at, $tz)->toDateString();

        if (is_null($statistics->{$shifts[0] . '_shift_time'}) && $dateObj->gte($this->getWorkObj($rule, $date, $tz)->subSeconds($rule['early_card']))) {
            $status = 1;
            if ($statistics->{$shifts[0] . '_shift_status'} > 1) {
                $number = 1;
            }
        }

        if (! is_null($statistics->{$shifts[0] . '_shift_time'})) {
            $status = 1;
            $number = 1;
        }

        if (is_null($statistics->{$shifts[1] . '_shift_time'}) && $dateObj->gte($this->getOffWorkObj($rule, $date, $tz)->subSeconds($rule['early_lack_card']))) {
            $status = 1;
        }

        if ($statistics->shift_data['number'] == 2) {
            if (! is_null($statistics->{$shifts[1] . '_shift_time'})) {
                $status = 0;
                $number = 2;
            }

            $rule = $statistics->shift_data['rules'][1];
            if (is_null($statistics->{$shifts[2] . '_shift_time'}) && $dateObj->gte($this->getWorkObj($rule, $date, $tz)->subSeconds($rule['early_card']))) {
                $status = 1;
                if ($statistics->{$shifts[2] . '_shift_status'} > 1) {
                    $number = 3;
                }
            }

            if (! is_null($statistics->{$shifts[2] . '_shift_time'})) {
                $status = 0;
                $number = 3;
            }

            if (is_null($statistics->{$shifts[3] . '_shift_time'}) && $dateObj->gte($this->getOffWorkObj($rule, $date, $tz)->subSeconds($rule['early_lack_card']))) {
                $status = 1;
            }
        }

        return [$status, $number];
    }

    /**
     * 核对当前打卡时间.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function checkWorkTime(int $uid, Carbon $dateObj, $info, int $clockNumber, string $tz): void
    {
        if (! $info->shift_data['rules'][$clockNumber < 2 ? 0 : 1]['free_clock'] && $dateObj->timestamp > $this->getClockEndTime($uid, $info, $clockNumber, $tz)) {
            throw $this->exception('无法打卡');
        }
    }

    /**
     * 月报统计
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getMonthReport(string $uuid, string $date, int $type, int $userId): array
    {
        $period   = [];
        $tz       = config('app.timezone');
        $shifts   = AttendanceClockEnum::SHIFT_CLASS;
        $dateObj  = ($date ? Carbon::parse($date, $tz) : now($tz));
        $timeZone = CarbonPeriod::create($dateObj->startOfMonth()->toDateString(), $dateObj->endOfMonth()->toDateString())->toArray();
        foreach ($timeZone as $item) {
            $dateString          = $item->toDateString();
            $period[$dateString] = ['date' => $dateString, 'status' => 0];
        }

        $list = $this->dao->select([
            'month' => $dateObj->format('Y-m'),
            'uid'   => $type ?
                app()->get(AttendanceClockService::class)->getStatisticsUserId($uuid, $userId) :
                app()->get(AttendanceGroupService::class)->getTeamMember($uuid),
        ]);
        foreach ($list as $item) {
            if (array_key_exists($item->date, $period)) {
                if ($item->shift_id > 1) {
                    $status = 1;
                } else {
                    continue;
                }

                for ($i = 0; $i < $item->shift_data['number'] * 2; ++$i) {
                    if ($item->{$shifts[$i] . '_status'} > 1 || $item->{$shifts[$i] . '_shift_status'} > 1) {
                        $status = 2;
                        break;
                    }
                }
                $period[$item->date]['status'] = $status;
            }
        }
        return array_values($period);
    }

    /**
     * 考勤数据.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getStatisticsList($info, int $clockNumber, string $tz, bool $isReal = false): array
    {
        $list      = $this->getClockRecord($info, $clockNumber, $tz, false, $isReal);
        $recordIds = array_filter(array_column($list, 'record_id'));
        if ($recordIds) {
            $records = app()->get(AttendanceClockService::class)->column(['id' => $recordIds], ['lat', 'lng', 'remark', 'image', 'address'], 'id');
        }

        foreach ($list as &$item) {
            if (isset($records[$item['record_id']])) {
                $item = array_merge($item, $records[$item['record_id']]);
            }
            unset($item['record_id']);
        }
        return $list;
    }

    /**
     * 获取打卡记录.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getStatisticsDetail(string $uuid, string $date, int $userId): array
    {
        $tz = config('app.timezone');
        if (! $date) {
            throw $this->exception('时间异常');
        }

        $seconds = 0;
        $dateObj = Carbon::parse($date, $tz);
        $date    = $dateObj->toDateString();
        $info    = $this->getUserRecordByDate(app()->get(AttendanceClockService::class)->getStatisticsUserId($uuid, $userId), $date);
        $list    = $this->getStatisticsList($info, $info->shift_id > 1 ? ($info->shift_data['number'] * 2 - 1) : 1, $tz, true);

        $shifts = AttendanceClockEnum::SHIFT_CLASS;
        if ($info->shift_id > 1) {
            $normal = true;
            for ($i = 0; $i < $info->shift_data['number'] * 2; ++$i) {
                if ($info->{$shifts[$i] . '_shift_status'} > 1 || empty($info->{$shifts[1] . '_shift_time'})) {
                    $normal = false;
                    break;
                }
            }

            if ($normal) {
                for ($i = 0; $i < $info->shift_data['number'] * 2; $i += 2) {
                    $seconds += Carbon::parse($info->{$shifts[$i] . '_shift_time'}, $tz)
                        ->diffInSeconds(Carbon::parse($info->{$shifts[$i + 1] . '_shift_time'}, $tz), false);
                }
            }

            if ($info->shift_data['number'] == 1 && $info->shift_data['rest_time']) {
                $restStartObj = Carbon::parse($date . ' ' . $info->shift_data['rest_start'] . ':00', $tz);
                $restEndObj   = Carbon::parse($date . ' ' . $info->shift_data['rest_end'] . ':00', $tz);
                $seconds -= ($info->shift_data['rest_start_after'] ? $restStartObj->addDay() : $restStartObj)
                    ->diffInHours($info->shift_data['rest_end_after'] ? $restEndObj->addDay() : $restEndObj, false);
            }
        } else {
            if (count($list) == 2) {
                $firstTime  = $info->{$shifts[0] . '_shift_time'};
                $secondTime = $info->{$shifts[1] . '_shift_time'};
                if ($firstTime && $secondTime) {
                    $seconds = Carbon::parse($firstTime, $tz)->diffInSeconds(Carbon::parse($secondTime, $tz), false);
                }
            }
        }

        return ['list' => $list, 'work_hours' => max($seconds, 0)];
    }

    /**
     * 团队统计
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getTeamStatistics(string $uuid, string $date = ''): array
    {
        $uid          = uuid_to_uid($uuid);
        $tz           = config('app.timezone');
        $dateObj      = $date ? Carbon::parse($date, $tz) : now($tz);
        $date         = $dateObj->toDateString();
        $groupService = app()->get(AttendanceGroupService::class);
        $where        = ['date' => $date, 'uid' => $groupService->getTeamMember($uid)];
        $normalWhere  = ['date' => $date, 'uid' => $groupService->getTeamMember($uid, filter: false)];
        $statistics   = [
            'deadline'    => '',
            'total'       => $this->dao->getCountByUid($normalWhere),
            'work_hours'  => sprintf('%.1f', $this->dao->avg($normalWhere, 'actual_work_hours')),
            'leave_early' => $this->dao->getCountByUid(array_merge($where, ['status' => AttendanceClockEnum::LEAVE_EARLY])),
            'late'        => $this->dao->getCountByUid(array_merge($where, ['status' => AttendanceClockEnum::ALL_LATE])),
            'lack_card'   => $this->dao->getCountByUid(array_merge($where, ['status' => AttendanceClockEnum::ALL_LACK_CARD])),
            'abnormal'    => $this->dao->getCountByUid(array_merge($where, ['abnormal_status' => AttendanceClockEnum::NORMAL])),
        ];

        $statistics['normal'] = $statistics['total'] - $statistics['abnormal'];
        return $statistics;
    }

    /**
     * 团队外勤统计
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getTeamExternalStatistics(string $uuid, string $date = ''): array
    {
        $tz             = config('app.timezone');
        $members        = app()->get(AttendanceGroupService::class)->getTeamMember($uuid);
        $dateObj        = $date ? Carbon::parse($date, $tz) : now($tz);
        [$page, $limit] = $this->getPageValue();
        $statistics     = $this->dao->select(
            ['date' => $dateObj->toDateString(), 'uid' => $members, 'location_status_gt' => AttendanceClockEnum::NO_NEED_CLOCK],
            ['*'],
            ['card' => fn ($q) => $q->select(['id', 'name', 'job', 'avatar', 'phone'])->with(['job' => fn ($q) => $q->select(['id', 'name']), 'frame' => fn ($q) => $q->select(['frame.id', 'name'])]),
            ],
            $page,
            $limit
        );

        $list   = [];
        $shifts = AttendanceClockEnum::SHIFT_CLASS;
        foreach ($statistics as $item) {
            $external = [];
            for ($i = 0; $i < $item->shift_data['number'] * 2; ++$i) {
                $status                = $item->{$shifts[$i] . '_shift_location_status'};
                $status && $external[] = ['location_status' => $status, 'type' => in_array($i, [0, 2]) ? 1 : 2];
            }
            $list[] = ['card' => $item->card, 'external' => $external];
        }
        return $list;
    }

    /**
     * 团队上下班明细.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getTeamCommuteDetails(string $uuid, string $date = '', array $status = []): array
    {
        $tz      = config('app.timezone');
        $dateObj = $date ? Carbon::parse($date, $tz) : now($tz);

        [$page, $limit] = $this->getPageValue();
        $statistics     = $this->dao->select(
            ['date' => $dateObj->toDateString(), 'uid' => app()->get(AttendanceGroupService::class)->getTeamMember($uuid)],
            ['*'],
            ['card' => fn ($q) => $q->select(['id', 'name', 'job', 'avatar', 'phone'])->with(['job' => fn ($q) => $q->select(['id', 'name']), 'frame' => fn ($q) => $q->select(['frame.id', 'name'])]),
            ],
            $page,
            $limit
        );

        $list   = [];
        $shifts = AttendanceClockEnum::SHIFT_CLASS;
        foreach ($statistics as $item) {
            $lackNum  = 0;
            $external = $status = $locationStatus = [];
            $shiftNum = $item->shift_id < 2 ? 2 : $item->shift_data['number'] * 2;
            for ($i = 0; $i < $shiftNum; ++$i) {
                $shiftStatus = $item->{$shifts[$i] . '_shift_status'};
                if ($shiftStatus == AttendanceClockEnum::LACK_CARD) {
                    ++$lackNum;
                }
                $shiftStatus > 1 && $status[$shiftStatus]                         = $shiftStatus;
                $shiftLocationStatus                                              = $item->{$shifts[$i] . '_shift_location_status'};
                $shiftLocationStatus > 0 && $locationStatus[$shiftLocationStatus] = $shiftLocationStatus;
                $external                                                         = ['status' => array_values($status), 'location_status' => array_values($locationStatus)];
            }

            $list[] = ['card' => $item->card, 'absenteeism' => ($item->shift_id > 1 && $lackNum == $shiftNum) ? 1 : 0, 'external' => $external];
        }
        return $list;
    }

    /**
     * 团队考勤明细
     * 1：异常；2：迟到；3：严重迟到；4：早退；5：缺卡；6：旷工；7：外勤卡；8：异常外勤；.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getTeamAttendanceStatistics(string $uuid, string $date = '', array $status = []): array
    {
        $tz   = config('app.timezone');
        $date = ($date ? Carbon::parse($date, $tz) : now($tz))->format('Y-m');

        [$page, $limit] = $this->getPageValue();
        $statistics     = $this->dao->getStatisticsMemberList(
            ['month' => $date, 'uid' => app()->get(AttendanceGroupService::class)->getTeamMember($uuid)],
            ['uid'],
            ['card' => fn ($q) => $q->select(['id', 'name', 'job', 'avatar', 'phone'])->with(['job' => fn ($q) => $q->select(['id', 'name']), 'frame' => fn ($q) => $q->select(['frame.id', 'name'])])],
            $page,
            $limit
        );

        $list   = [];
        $shifts = AttendanceClockEnum::SHIFT_CLASS;
        foreach ($statistics as $item) {
            $num          = 0; // 异常数量
            $status       = 0; // 异常状态
            $recordStatus = 0; // 临时状态
            $records      = toArray($this->dao->select(['month' => $date, 'uid' => $item->uid], ['*']));
            foreach ($records as $record) {
                if ($record['shift_id'] < 2) {
                    continue;
                }
                $recordAbnormal = 0; // 当前异常数量
                $recordAbsent   = 0; //  当前缺卡数量
                $shiftNum       = $record['shift_data']['number'] * 2;
                for ($i = 0; $i < $shiftNum; ++$i) {
                    $shiftStatus         = $record[$shifts[$i] . '_shift_status'];
                    $shiftLocationStatus = $record[$shifts[$i] . '_shift_location_status'];
                    if ($shiftStatus > AttendanceClockEnum::NORMAL || $shiftLocationStatus > AttendanceClockEnum::OFFICE_OUTSIDE) {
                        ++$recordAbnormal;
                        if ($shiftStatus == AttendanceClockEnum::LACK_CARD) {
                            ++$recordAbsent;
                        }

                        if ($shiftStatus) {
                            $tmpStatus    = in_array($shiftStatus, AttendanceClockEnum::ALL_LACK_CARD) ? 5 : $shiftStatus;
                            $recordStatus = $recordStatus == 0 ? $tmpStatus : ($recordStatus != $tmpStatus ? 1 : $tmpStatus);
                        }

                        if ($shiftLocationStatus) {
                            $recordStatus = $recordStatus == 0 ? 8 : ($recordStatus != 8 ? 1 : 8);
                        }
                    }
                }

                // 旷工
                if ($recordAbsent == $shiftNum) {
                    $recordStatus = 6;
                    ++$num;
                } else {
                    $num += $recordAbnormal;
                }

                if ($recordStatus && ! $status) {
                    $status = $recordStatus;
                }
            }

            $list[] = ['card' => $item->card, 'num' => $num, 'status' => $status == $recordStatus ? $status : 1];
        }
        return $list;
    }

    /**
     * 团队加班明细.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getTeamOvertimeStatistics(string $uuid, array $where): array
    {
        $list         = [];
        $uid          = app()->get(AttendanceGroupService::class)->getTeamMember($uuid);
        $overTimeData = app()->get(AttendanceApplyRecordService::class)->getOverTimeData($where['month'], $where['date_type'], $uid);
        if (empty($overTimeData)) {
            return $list;
        }

        [$page, $limit] = $this->getPageValue();
        $statistics     = $this->dao->getStatisticsMemberList(
            ['month' => $where['month'], 'uid' => array_keys($overTimeData)],
            ['id', 'uid'],
            ['card' => fn ($q) => $q->select(['id', 'name', 'job', 'avatar', 'phone'])->with(['job' => fn ($q) => $q->select(['id', 'name']), 'frame' => fn ($q) => $q->select(['frame.id', 'name'])])],
            $page,
            $limit
        );

        foreach ($statistics as $item) {
            $list[] = ['card' => $item->card, 'num' => $overTimeData[$item->uid] ?? 0];
        }
        return $list;
    }

    /**
     * 团队假勤明细.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getTeamLeaveStatistics(string $uuid, array $where): array
    {
        $list  = [];
        $tz    = config('app.timezone');
        $month = ($where['month'] ? Carbon::parse($where['month'], $tz) : now($tz))->format('Y-m');

        $uid       = app()->get(AttendanceGroupService::class)->getTeamMember($uuid);
        $LeaveData = app()->get(AttendanceApplyRecordService::class)->getLeaveData($uid, $month, (array) $where['status']);
        if (empty($LeaveData)) {
            return $list;
        }
        [$page, $limit] = $this->getPageValue();
        $statistics     = $this->dao->getStatisticsMemberList(
            ['month' => $month, 'uid' => array_keys($LeaveData)],
            ['id', 'uid'],
            ['card' => fn ($q) => $q->select(['id', 'name', 'job', 'avatar', 'phone'])->with(['job' => fn ($q) => $q->select(['id', 'name']), 'frame' => fn ($q) => $q->select(['frame.id', 'name'])])],
            $page,
            $limit
        );

        foreach ($statistics as $item) {
            $list[] = ['card' => $item->card, 'num' => $LeaveData[$item->uid] ?? 0];
        }
        return $list;
    }

    /**
     * 月统计
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getMonthStatistics(string $uuid, string $date = '', int $type = 0, int $userId = 0): array
    {
        $tz = config('app.timezone');
        if (! $date) {
            $dateObj = now($tz);
        } else {
            $dateObj = Carbon::parse($date, $tz);
        }

        if ($type) {
            [$clockStatistics, $overtimeStatistics, $leaveStatistics] = $this->getPersonMonthStatistics($uuid, $userId, $dateObj->format('Y-m'));
        } else {
            [$clockStatistics, $overtimeStatistics, $leaveStatistics] = $this->getTeamMonthStatistics($uuid, $dateObj->format('Y-m'));
        }

        $clockStatistics['deadline'] = '';
        $clockStatistics['normal']   = $clockStatistics['total'] - $clockStatistics['abnormal'];

        return [
            'clock_statistics'    => $clockStatistics,
            'overtime_statistics' => $overtimeStatistics,
            'leave_statistics'    => $leaveStatistics,
        ];
    }

    /**
     * 个人考勤统计
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getPersonAttendanceStatistics(string $uuid, string $date, array $status = [], int $userId = 0): array
    {
        $tz = config('app.timezone');
        try {
            $dateObj = $date ? Carbon::parse($date, $tz) : now($tz);
        } catch (\Throwable $e) {
            throw $this->exception('日期格式有误');
        }
        $where = [
            'uid'   => app()->get(AttendanceClockService::class)->getStatisticsUserId($uuid, $userId),
            'month' => $dateObj->format('Y-m'),
        ];
        [$page, $limit] = $this->getPageValue();
        $statistics     = $this->dao->select($where, ['*'], [], $page, $limit);

        $list         = [];
        $shifts       = AttendanceClockEnum::SHIFT_CLASS;
        $shiftService = app()->get(AttendanceShiftService::class);
        foreach ($statistics as $item) {
            $locationStatus = 0;

            $details   = [];
            $absentNum = $lateNum = $earlyNum = 0;
            $shiftNum  = ($item->shift_data['number'] ?? 0) * 2;
            for ($i = 0; $i < $shiftNum; ++$i) {
                $shiftStatus         = $item->{$shifts[$i] . '_shift_status'};
                $shiftLocationStatus = $item->{$shifts[$i] . '_shift_location_status'};

                if ($shiftLocationStatus > $locationStatus) {
                    $locationStatus = $shiftLocationStatus;
                }

                if ($shiftStatus == AttendanceClockEnum::LACK_CARD) {
                    ++$absentNum;
                }

                if (in_array($shiftStatus, AttendanceClockEnum::ALL_LACK_CARD)) {
                    $shiftStatus == AttendanceClockEnum::LATE_LACK_CARD && $lateNum++;
                    $shiftStatus == AttendanceClockEnum::EARLY_LACK_CARD && $earlyNum++;

                    if (! isset($details[$shiftStatus])) {
                        $details[$shiftStatus] = [
                            'work_type'  => in_array($i, [0, 2]) ? 1 : 2,
                            'time_type'  => '',
                            'work_hours' => '0.00',
                            'status'     => $shiftStatus,
                        ];
                    }
                }

                if (in_array($shiftStatus, AttendanceClockEnum::LATE_AND_LEAVE_EARLY)) {
                    $minutes = $shiftService->getNormalMinutes(
                        $item->created_at->toDateTimeString(),
                        $item->shift_data,
                        $shiftStatus,
                        $i,
                        (string) $item->{$shifts[$i] . '_shift_time'},
                        $tz
                    );
                    if (! isset($details[$shiftStatus])) {
                        $details[$shiftStatus] = [
                            'work_type'  => 0,
                            'time_type'  => 'minute',
                            'work_hours' => sprintf('%.2f', $minutes),
                            'status'     => $shiftStatus,
                        ];
                    } else {
                        $details[$shiftStatus]['work_hours'] = bcadd((string) $minutes, $details[$shiftStatus]['work_hours'], 2);
                    }
                }
            }

            $list[] = [
                'id'              => $item->id,
                'date'            => $item->created_at->toDateString(),
                'absenteeism'     => $item->shift_id > 1 && $absentNum == $shiftNum ? 1 : 0,
                'location_status' => $locationStatus,
                'details'         => $absentNum != $shiftNum ? array_values($details) : [],
            ];
        }
        return $list;
    }

    /**
     * 生成员工打卡数据.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function generateMemberStatistics(string $date): void
    {
        // attendance group arrange record
        app()->get(AttendanceArrangeService::class)->generateAttendGroupRecord($date);

        // attendance members
        $members = app()->get(AdminService::class)->column(['status' => 1], 'id');

        // default shift id
        $defaultShiftId = app()->get(CalendarConfigService::class)->dayIsRest($date) ? 1 : 2;

        // white list
        $whitelist = app()->get(AttendanceGroupService::class)->getWhiteListMemberIds();

        $dateObj     = Carbon::parse($date, config('app.timezone'));
        $date        = $dateObj->startOfDay()->toDateString();
        $arrangeDate = $dateObj->startOfMonth()->toDateString();

        foreach ($members as $member) {
            $shiftId = in_array($member, $whitelist) ? 0 : $defaultShiftId;

            # generate arrange record
            $this->generateArrange($member, $date, $shiftId, $arrangeDate);

            # generate statistics
            $this->generateStatistics($member, $date);
        }
    }

    /**
     * 考勤班次
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getShiftsByDate(string $date): array
    {
        return $this->dao->search(['date' => $date, 'shift_id_gt' => 1])->groupBy('shift_id')->select(['uid', 'shift_id', 'shift_data'])->get()->toArray();
    }

    /**
     * 获取旷工缺卡人/次数.
     * @return int[]
     * @throws \ReflectionException
     * @throws BindingResolutionException
     */
    public function getLackCardAndAbsenteeismPeopleNum(array|int $uid, string $date, string $timeType = 'date'): array
    {
        $shifts  = AttendanceClockEnum::SHIFT_CLASS;
        $records = $this->dao->select([$timeType => $date, 'uid' => $uid], ['*']);

        $lackCardNum = $absenteeismNum = [];
        foreach ($records as $record) {
            if ($record->shift_id < 2) {
                continue;
            }
            $lackNum  = $absentNum = 0;
            $shiftNum = $record->shift_data['number'] * 2;
            for ($i = 0; $i < $shiftNum; ++$i) {
                $shiftStatus = $record->{$shifts[$i] . '_shift_status'};
                if ($shiftStatus == AttendanceClockEnum::LACK_CARD) {
                    ++$absentNum;
                }

                if (in_array($shiftStatus, AttendanceClockEnum::ALL_LACK_CARD)) {
                    ++$lackNum;
                }
            }

            if ($absentNum == $shiftNum) {
                if (! isset($absenteeismNum[$record->uid])) {
                    $absenteeismNum[$record->uid] = 1;
                } else {
                    ++$absenteeismNum[$record->uid];
                }
            } else {
                if (! isset($lackCardNum[$record->uid])) {
                    $lackCardNum[$record->uid] = $lackNum;
                } else {
                    $lackCardNum[$record->uid] += $lackNum;
                }
            }
        }
        return [count($absenteeismNum), count($lackCardNum), array_sum($absenteeismNum), array_sum($lackCardNum)];
    }

    /**
     * 获取加班人/次数.
     */
    public function getOverTimeStatistics(array|int $members, string $date, string $timeType = 'date'): array
    {
        $overTimeNum = [];
        foreach ($this->select(['uid' => $members, $timeType => $date]) as $record) {
            if (bccomp($record->actual_work_hours, '0.0')
                && bccomp(bcmul(bcsub($record->actual_work_hours, $record->required_work_hours), '3600'), (string) $record->shift_data['overtime'])) {
                if (! isset($overTimeNum[$record->uid])) {
                    $overTimeNum[$record->uid] = 1;
                } else {
                    ++$overTimeNum[$record->uid];
                }
            }
        }
        return [count($overTimeNum), array_sum($overTimeNum)];
    }

    /**
     * 清除白名单考勤数据.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function clearWhitelist(array $members): int
    {
        return $this->dao->delete(['uid' => $members, 'gt_date' => now(config('app.timezone'))->subDay()->toDateString()]);
    }

    /**
     * 审批审批类型.
     * @throws BindingResolutionException
     */
    public function getApproveList(string $uuid, string $date): array
    {
        $approveService = app()->get(ApproveService::class);
        return $approveService->dao->getList([
            'types' => [
                ApproveEnum::PERSONNEL_HOLIDAY,
                ApproveEnum::PERSONNEL_OUT,
                ApproveEnum::PERSONNEL_TRIP,
                ApproveEnum::PERSONNEL_SIGN,
                ApproveEnum::PERSONNEL_OVERTIME,
            ],
        ], ['*'], 0, 0, 'id');
    }

    /**
     * 获取异常日期
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function getAbnormalDateList(int $uid, bool $withRecord = false): array
    {
        $group = app()->get(AttendanceGroupService::class)->getGroupByUid($uid);
        if ($group && ! $this->checkGroupRepairAllowed($uid, $group)) {
            return [];
        }

        $where  = ['uid' => $uid];
        $select = $repairCondition = $shiftStatus = [];

        if ($group) {
            if (in_array(5, $group->repair_type)) {
                $repairCondition['location_status'] = AttendanceClockEnum::OFFICE_ABNORMAL;
            }

            if (in_array(1, $group->repair_type)) {
                $shiftStatus = array_merge($shiftStatus, AttendanceClockEnum::ALL_LACK_CARD);
            }

            if (in_array(2, $group->repair_type)) {
                $shiftStatus = array_merge($shiftStatus, [AttendanceClockEnum::LATE]);
            }

            if (in_array(3, $group->repair_type)) {
                $shiftStatus = array_merge($shiftStatus, [AttendanceClockEnum::EXTREME_LATE]);
            }

            if (in_array(4, $group->repair_type)) {
                $shiftStatus = array_merge($shiftStatus, [AttendanceClockEnum::LEAVE_EARLY]);
            }

            // limit time repair allowed
            if ($group->is_limit_time) {
                $tz     = config('app.timezone');
                $nowObj = now($tz);

                if ($group->limit_time < 1) {
                    $where['date'] = $nowObj->toDateString();
                } else {
                    $where['time'] = $nowObj->subDays($group->limit_time)->startOfDay()->format('Y/m/d') . '-' . now($tz)->startOfDay()->format('Y/m/d');
                }
            }
            if ($shiftStatus) {
                $repairCondition['status'] = $shiftStatus;
            }
        } else {
            $repairCondition = [
                'status'          => array_merge(AttendanceClockEnum::ALL_LACK_CARD, AttendanceClockEnum::LATE_AND_LEAVE_EARLY),
                'location_status' => AttendanceClockEnum::OFFICE_ABNORMAL,
            ];
        }

        $list = $this->dao->select(array_merge($where, ['repair_condition' => $repairCondition]));
        foreach ($list as $item) {
            $option = ['value' => $item->id, 'label' => $item->date];
            if ($withRecord) {
                $option['record'] = $this->getAbnormalRecordListWithApprove($item);
            }
            $select[] = $option;
        }
        return $select;
    }

    /**
     * 获取审批异常打卡记录.
     */
    public function getAbnormalRecordListWithApprove(mixed $abnormal): array
    {
        $select = [];
        $shifts = AttendanceClockEnum::SHIFT_CLASS;
        $rule   = $abnormal->shift_data['rules'][0];
        for ($i = 0; $i <= $abnormal->shift_data['number'] * 2 - 1; ++$i) {
            // no need clock
            if (in_array($i, [1, 3]) && $rule['free_clock'] && $abnormal->{$shifts[$i] . '_shift_status'} == 0) {
                continue;
            }

            if ($abnormal->{$shifts[$i] . '_shift_status'} > 0 && $abnormal->{$shifts[$i] . '_shift_status'} < 2 && $abnormal->{$shifts[$i] . '_shift_location_status'} < 2) {
                continue;
            }

            if ($i > 1) {
                $rule = $abnormal->shift_data['rules'][1];
            }

            $workType = in_array($i, [0, 2]);
            $select[] = ['value' => $i + 1, 'label' => ($workType ? '上班' : '下班') . ' ' . $rule[$workType ? 'work_hours' : 'off_hours']];
        }

        return $select;
    }

    /**
     * 异常时间.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getRecordTimeWithAbnormalId(int $abnormalId, int $recordId): string
    {
        $abnormal = $this->dao->get(['id' => $abnormalId]);
        $shifts   = AttendanceClockEnum::SHIFT_CLASS;
        $rule     = $abnormal->shift_data['rules'][$recordId > 1 ? 1 : 0];

        if (in_array($recordId, [1, 3]) && $rule['free_clock'] && $abnormal->{$shifts[$recordId] . '_shift_status'} == 0) {
            return '';
        }

        if ($abnormal->{$shifts[$recordId] . '_shift_status'} > 0 && $abnormal->{$shifts[$recordId] . '_shift_status'} < 2 && $abnormal->{$shifts[$recordId] . '_shift_location_status'} < 2) {
            return '';
        }

        $workType = in_array($recordId, [0, 2]);
        return ($workType ? '上班' : '下班') . ' ' . $rule[$workType ? 'work_hours' : 'off_hours'];
    }

    /**
     * 获取异常记录.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getAbnormalRecordList(string $uuid, int $id): array
    {
        $info = $this->dao->get(['id' => $id, 'uid' => uuid_to_uid($uuid)]);
        if (! $info) {
            throw $this->exception('暂无可操作记录！');
        }

        $select = [];
        $shifts = AttendanceClockEnum::SHIFT_CLASS;
        $rule   = $info->shift_data['rules'][0];
        for ($i = 0; $i <= $info->shift_data['number'] * 2 - 1; ++$i) {
            // no need clock
            if (in_array($i, [1, 3]) && $rule['free_clock'] && $info->{$shifts[$i] . '_shift_status'} == 0) {
                continue;
            }

            if ($info->{$shifts[$i] . '_shift_status'} > 0 && $info->{$shifts[$i] . '_shift_status'} < 2 && $info->{$shifts[$i] . '_shift_location_status'} < 2) {
                continue;
            }

            if ($i > 1) {
                $rule = $info->shift_data['rules'][1];
            }

            $workType = in_array($i, [0, 2]);
            $select[] = ['shift_number' => $i, 'title' => $workType ? '上班' : '下班', 'time' => $rule[$workType ? 'work_hours' : 'off_hours']];
        }

        return $select;
    }

    /**
     * 更新异常班次
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function updateAbnormalShiftStatus(int $userId, int $type, string $startTime, string $endTime, string $tz, array $others = []): bool
    {
        $abnormalId = $others['abnormal_id'] ?? 0;
        $recordId   = $others['record_id'] ?? 0;
        $where      = $type == ApproveEnum::PERSONNEL_SIGN ? ['id' => $abnormalId] : ['abnormal_status' => AttendanceClockEnum::NORMAL];
        $list       = $this->dao->select(array_merge($where, ['uid' => $userId]));
        if ($list->isEmpty()) {
            return true;
        }

        $isShiftStatus = $isLocationStatus = $isSignStatus = false;

        // out and trip
        if (in_array($type, [ApproveEnum::PERSONNEL_OUT, ApproveEnum::PERSONNEL_TRIP])) {
            $isLocationStatus = true;
        }

        // holiday
        if ($type == ApproveEnum::PERSONNEL_HOLIDAY) {
            $isLocationStatus = $isShiftStatus = true;
        }

        // sign
        if ($type == ApproveEnum::PERSONNEL_SIGN) {
            $isSignStatus = true;
        }

        return $this->transaction(function () use ($startTime, $endTime, $tz, $isShiftStatus, $isLocationStatus, $list, $isSignStatus, $recordId) {
            if (! $isSignStatus) {
                $startObj = Carbon::parse($startTime, $tz);
                $endObj   = Carbon::parse($endTime, $tz);
            }

            $shiftService = app()->get(AttendanceShiftService::class);
            $shifts       = AttendanceClockEnum::SHIFT_CLASS;

            foreach ($list as $item) {
                $isUpdate = false;
                $rule     = $item->shift_data['rules'][0] ?? [];
                if (! $rule) {
                    continue;
                }

                $dateString = Carbon::parse($item->created_at, $tz)->toDateString();

                for ($i = 0; $i <= ($item->shift_data['number'] ?? 1) * 2 - 1; ++$i) {
                    if ($i > 1) {
                        $rule = $item->shift_data['rules'][1];
                    }

                    $workObj = $this->{in_array($i, [0, 2]) ? 'getWorkObj' : 'getOffWorkObj'}($rule, $dateString, $tz);
                    if (! $isSignStatus && ($workObj->lt($startObj) || $workObj->gte($endObj))) {
                        continue;
                    }

                    // clock status
                    if (($isShiftStatus || ($isSignStatus && $i == $recordId)) && $item->{$shifts[$i] . '_shift_status'} > 1) {
                        $isUpdate = true;

                        $item->{$shifts[$i] . '_shift_status'} = 1;
                        $item->{$shifts[$i] . '_shift_time'}   = $workObj->toDateTimeString();
                    }

                    // location status
                    if ((($isShiftStatus || $isLocationStatus) || ($isSignStatus && $i == $recordId)) && $item->{$shifts[$i] . '_shift_location_status'} == 2) {
                        $isUpdate                                                                                               = true;
                        $item->{$shifts[$i] . '_shift_location_status'} == 2 && $item->{$shifts[$i] . '_shift_location_status'} = 1;
                    }
                }

                if (! $isUpdate) {
                    continue;
                }

                // work hours
                $item->actual_work_hours = $shiftService->getActualWorkHours($item, $dateString, $tz);
                $item->save();
            }
            return true;
        });
    }

    /**
     * 首页团队统计
     */
    public function getHomeTeamStatistics(string $uuid): array
    {
        return Cache::remember(md5(json_encode(['uuid' => $uuid, 'type' => 'team'])), 60, function () use ($uuid) {
            $date  = now(config('app.timezone'))->toDateString();
            $where = ['date' => $date, 'uid' => app()->get(AttendanceGroupService::class)->getTeamMember(uuid_to_uid($uuid))];
            return [
                'late'        => $this->dao->getCountByUid(array_merge($where, ['status' => AttendanceClockEnum::ALL_LATE])),
                'leave_early' => $this->dao->getCountByUid(array_merge($where, ['status' => AttendanceClockEnum::LEAVE_EARLY])),
                'lack_card'   => $this->dao->getCountByUid(array_merge($where, ['status' => AttendanceClockEnum::ALL_LACK_CARD])),
            ];
        });
    }

    /**
     * 首页个人统计
     */
    public function getHomePersonStatistics(string $uuid): array
    {
        return Cache::remember(md5(json_encode(['uuid' => $uuid, 'type' => 'person'])), 60, function () use ($uuid) {
            $uid   = uuid_to_uid($uuid);
            $month = now(config('app.timezone'))->format('Y-m');
            $where = ['month' => $month, 'uid' => $uid];
            return [
                'late'        => $this->dao->count(array_merge($where, ['status' => AttendanceClockEnum::ALL_LATE])),
                'leave_early' => $this->dao->count(array_merge($where, ['status' => AttendanceClockEnum::LEAVE_EARLY])),
                'lack_card'   => $this->getLackCardAndAbsenteeismNum($uid, $month)[1] ?? 0,
            ];
        });
    }

    /**
     * 计算考勤请假工时.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function calcLeaveDurationByTime(int $uid, int $applyRecordId, int $holidayTypeId, string $startTime, string $endTime): void
    {
        $tz       = config('app.timezone');
        $startObj = Carbon::parse($startTime, $tz);
        $endObj   = Carbon::parse($endTime, $tz);

        $leaveService = app()->get(AttendanceStatisticsLeaveService::class);

        $list = $this->dao->select(['uid' => $uid, 'holiday_time' => $startObj->eq($endObj) ? $startObj->toDateString() : [$startObj->toDateString(), $endObj->toDateString()]]);

        foreach ($list as $item) {
            if ($item->shift_id < 2) {
                continue;
            }

            $duration   = '0.0';
            $restEndObj = $restStartObj = null;

            $dateString = Carbon::parse($item->created_at, $tz)->toDateString();

            // rest time
            if ($item->shift_data['number'] == 1 && $item->shift_data['rest_time']) {
                $restStartObj = Carbon::parse($dateString . ' ' . $item->shift_data['rest_start'] . ':00', $tz);
                $item->shift_data['rest_start_after'] && $restStartObj->addDay();

                $restEndObj = Carbon::parse($dateString . ' ' . $item->shift_data['rest_end'] . ':00', $tz);
                $item->shift_data['rest_end_after'] && $restEndObj->addDay();
            }

            for ($i = 0; $i <= $item->shift_data['number'] - 1; ++$i) {
                $rule       = $item->shift_data['rules'][$i];
                $workObj    = $this->getWorkObj($rule, $dateString, $tz);
                $offWorkObj = $this->getOffWorkObj($rule, $dateString, $tz);
                if ($endObj->lt($workObj) || $startObj->gt($offWorkObj)) {
                    continue;
                }

                // skip working time
                $skipWork = false;

                // skip off work time
                $skipOffWork = false;

                // comp rest time
                if ($restStartObj && $restEndObj) {
                    if ($startObj->gt($restStartObj)) {
                        $skipWork = true;
                    }

                    if ($endObj->lt($restEndObj)) {
                        $skipOffWork = true;
                    }

                    if (! $skipWork) {
                        $tmpStartObj = $workObj->lt($startObj) ? $startObj : $workObj;
                        $tmpEndObj   = $endObj->gt($restStartObj) ? $restStartObj : $endObj;
                        $duration    = $this->calcLeaveDuration($duration, $tmpStartObj, $tmpEndObj);
                    }

                    if (! $skipOffWork) {
                        if ($skipWork) {
                            $tmpStartObj = $restEndObj->lt($startObj) ? $startObj : $startObj->addSeconds($restEndObj->diffInSeconds($startObj));
                        } else {
                            $tmpStartObj = $restEndObj;
                        }
                        $tmpEndObj = $endObj->gt($offWorkObj) ? $offWorkObj : $endObj;
                        $duration  = $this->calcLeaveDuration($duration, $tmpStartObj, $tmpEndObj);
                    }
                } else {
                    $tmpStartObj = $startObj->gt($workObj) ? $startObj : $workObj;
                    $tmpEndObj   = $endObj->gt($offWorkObj) ? $offWorkObj : $endObj;
                    $duration    = $this->calcLeaveDuration($duration, $tmpStartObj, $tmpEndObj);
                }
            }

            // save leave duration
            if ($duration) {
                $leaveService->createLeaveRecord($item->id, $item->uid, $holidayTypeId, $duration, $applyRecordId, $item->created_at);
            }
        }
    }

    /**
     * 获取打卡状态
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getClockStatus(int $clockNumber, Carbon $dateObj, $info): int
    {
        if ($info->shift_id < 2) {
            return 1;
        }

        $tz     = config('app.timezone');
        $date   = $dateObj->toDateString();
        $status = AttendanceClockEnum::NORMAL;
        $rule   = $info->shift_data['rules'][$clockNumber < 2 ? 0 : 1];
        if (in_array($clockNumber, [0, 2])) {
            $workObj = $this->getWorkObj($rule, $date, $tz);
            $seconds = $workObj->diffInSeconds($dateObj);
            if ($dateObj->gt($workObj)) {
                $status = match (true) {
                    $seconds > $rule['late_lack_card']        => AttendanceClockEnum::LATE_LACK_CARD,
                    $seconds > $rule['extreme_late']          => AttendanceClockEnum::EXTREME_LATE,
                    $rule['late'] && $seconds > $rule['late'] => AttendanceClockEnum::LATE,
                    default                                   => AttendanceClockEnum::NORMAL
                };
            } elseif ($dateObj->lt($workObj)) {
                $status = match (true) {
                    $seconds > $rule['early_card'] => AttendanceClockEnum::LACK_CARD,
                    default                        => AttendanceClockEnum::NORMAL
                };
            }
        } else {
            $offWorkObj = $this->getOffWorkObj($rule, $date, $tz);
            if ($dateObj->lt($offWorkObj)) {
                $seconds = $offWorkObj->diffInSeconds($dateObj);
                $status  = match (true) {
                    $seconds > 0 && $seconds > $rule['early_lack_card']                     => AttendanceClockEnum::EARLY_LACK_CARD,
                    $seconds > 0 && $rule['early_leave'] && $seconds > $rule['early_leave'] => AttendanceClockEnum::LEAVE_EARLY,
                    default                                                                 => AttendanceClockEnum::NORMAL
                };
            }
        }

        if ($dateObj->timestamp > $this->getClockEndTime((int) $info->uid, $info, $clockNumber, $tz)) {
            $status = AttendanceClockEnum::LACK_CARD;
        }
        return $status;
    }

    /**
     * 获取审批免打卡
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function calcAssociatedApprove(mixed $info, string $dateString, int $clockNumber, array $rule, string $tz): array
    {
        $freeClock      = false;
        $locationStatus = 0;
        $recordService  = app()->get(AttendanceApplyRecordService::class);
        $workObj        = $this->{in_array($clockNumber, [0, 2]) ? 'getWorkObj' : 'getOffWorkObj'}($rule, $dateString, $tz);
        $recordList     = $recordService->select(['uid' => $info->uid, 'compare_time' => $workObj->toDateTimeString()], ['apply_type', 'start_time', 'end_time']);
        foreach ($recordList as $item) {
            if ($workObj->gte($item->start_time) && $workObj->lt($item->end_time)) {
                // out and trip
                if (in_array($item->apply_type, [ApproveEnum::PERSONNEL_OUT, ApproveEnum::PERSONNEL_TRIP])) {
                    $locationStatus = 1;
                }

                // holiday
                if ($item->apply_type == ApproveEnum::PERSONNEL_HOLIDAY) {
                    $freeClock      = true;
                    $locationStatus = 1;
                }
            }
        }

        return [$freeClock, $locationStatus];
    }

    /**
     * 创建打卡数据.
     * @return BaseModel|Model
     * @throws \ReflectionException
     * @throws BindingResolutionException
     */
    private function createStatistics(int $uid, string $date): mixed
    {
        $shiftService = app()->get(AttendanceShiftService::class);
        $isWhitelist  = app()->get(AttendanceGroupService::class)->isWhitelist($uid);
        if (! $isWhitelist) {
            // 考勤班次
            $clockGroup = app()->get(AttendanceClockService::class)->getClockBasicByUid($uid, $date);
            $shift      = $shiftService->getArrangeShiftById($clockGroup['shift_id'], $date);
        } else {
            $shift      = [];
            $clockGroup = ['group_id' => 0, 'group_name' => '', 'shift_id' => 0];
        }
        $data = [
            'uid'                 => $uid,
            'frame_id'            => app()->get(FrameService::class)->getFrameIdByUserId($uid),
            'group_id'            => $clockGroup['group_id'],
            'group'               => $clockGroup['group_name'],
            'shift_id'            => $clockGroup['shift_id'],
            'shift_data'          => $shift,
            'required_work_hours' => ! $isWhitelist ? $shiftService->getRequiredAttendanceHours($shift, $date) : '0.0',
            'created_at'          => $date,
        ];

        $res = $this->dao->create($data);
        if (! $res) {
            throw $this->exception('考勤数据异常');
        }
        return $res;
    }

    /**
     * 生成打卡数据.
     */
    private function generateStatistics(int $uid, string $date): void
    {
        try {
            if (! $this->dao->exists(['uid' => $uid, 'date' => $date])) {
                $this->createStatistics($uid, $date);
            }
        } catch (\Throwable $e) {
            Log::error($uid . '打卡数据创建失败:' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine(), 'uid' => $uid]);
        }
    }

    /**
     * 生成排班数据.
     */
    private function generateArrange(int $uid, string $date, int $shiftId, string $arrangeDate): void
    {
        try {
            app()->get(AttendanceArrangeService::class)->generateAttendanceRecordByMember($uid, $shiftId, $date, $arrangeDate);
        } catch (\Throwable $e) {
            Log::error($uid . '默认排班数据创建失败:' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine(), 'uid' => $uid, 'date' => $date]);
        }
    }

    /**
     * 获取旷工缺卡异常数量.
     * @return int[]
     * @throws \ReflectionException
     * @throws BindingResolutionException
     */
    private function getLackCardAndAbsenteeismNum(array|int $uid, string $date): array
    {
        $lackCard = $absenteeism = $locationAbnormal = 0;
        $shifts   = AttendanceClockEnum::SHIFT_CLASS;
        $records  = $this->dao->select(['month' => $date, 'uid' => $uid], ['*']);
        foreach ($records as $record) {
            if ($record->shift_id < 2) {
                continue;
            }
            $lackNum  = $absentNum = 0;
            $shiftNum = $record->shift_data['number'] * 2;
            for ($i = 0; $i < $shiftNum; ++$i) {
                $shiftStatus         = $record->{$shifts[$i] . '_shift_status'};
                $shiftLocationStatus = $record->{$shifts[$i] . '_shift_location_status'};

                if ($shiftStatus == AttendanceClockEnum::LACK_CARD) {
                    ++$absentNum;
                }

                if (in_array($shiftStatus, AttendanceClockEnum::ALL_LACK_CARD)) {
                    ++$lackNum;
                }

                if ($shiftLocationStatus == AttendanceClockEnum::OFFICE_ABNORMAL) {
                    ++$locationAbnormal;
                }
            }

            if ($absentNum == $shiftNum) {
                ++$absenteeism;
            } else {
                $lackCard += $lackNum;
            }
        }
        return [$absenteeism, $lackCard, $locationAbnormal];
    }

    /**
     * 获取团队旷工/缺卡异常人数.
     * @return int[]
     * @throws \ReflectionException
     * @throws BindingResolutionException
     */
    private function getTeamLackCardAndAbsenteeismNum(array|int $uid, string $date): array
    {
        $lackCard = $absenteeism = $locationAbnormal = [];
        $shifts   = AttendanceClockEnum::SHIFT_CLASS;
        $records  = $this->dao->select(['month' => $date, 'uid' => $uid], ['*']);
        foreach ($records as $record) {
            if ($record->shift_id < 2) {
                continue;
            }
            $absentNum = 0;
            $shiftNum  = $record->shift_data['number'] * 2;
            for ($i = 0; $i < $shiftNum; ++$i) {
                $shiftStatus         = $record->{$shifts[$i] . '_shift_status'};
                $shiftLocationStatus = $record->{$shifts[$i] . '_shift_location_status'};

                if ($shiftStatus == AttendanceClockEnum::LACK_CARD) {
                    ++$absentNum;
                }

                if ($shiftLocationStatus == AttendanceClockEnum::OFFICE_ABNORMAL) {
                    $locationAbnormal[$record->uid] = 1;
                }
            }

            if ($absentNum == $shiftNum) {
                $absenteeism[$record->uid] = 1;
            } else {
                $lackCard[$record->uid] = 1;
            }
        }
        return [count($absenteeism), count($lackCard), count($locationAbnormal)];
    }

    /**
     * 获取团队月报打卡统计
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    private function getTeamMonthStatistics(string $uuid, string $month): array
    {
        $uid          = uuid_to_uid($uuid);
        $groupService = app()->get(AttendanceGroupService::class);

        // team member
        $teamMember = $groupService->getTeamMember($uid);
        $where      = ['month' => $month, 'uid' => $teamMember];

        [$absenteeism, $lackCard, $locationAbnormal] = $this->getTeamLackCardAndAbsenteeismNum($teamMember, $month);

        // all member
        $allMember       = $groupService->getTeamMember($uid, filter: false);
        $clockStatistics = [
            'total'             => count($allMember),
            'work_hours'        => sprintf('%.2f', $this->dao->avg(['month' => $month, 'uid' => $allMember], 'actual_work_hours')),
            'late'              => $this->dao->getCountByUid(array_merge($where, ['status' => AttendanceClockEnum::ALL_LATE])),
            'absenteeism'       => $absenteeism,
            'leave_early'       => $this->dao->getCountByUid(array_merge($where, ['status' => AttendanceClockEnum::LEAVE_EARLY])),
            'lack_card'         => $lackCard,
            'location_abnormal' => $locationAbnormal,
            'abnormal'          => $this->dao->getCountByUid(array_merge($where, ['abnormal_status' => AttendanceClockEnum::NORMAL])),
        ];

        $recordService      = app()->get(AttendanceApplyRecordService::class);
        $overtimeStatistics = [
            'work'    => $recordService->getOvertimeNumByDateType($teamMember, $month, 1),
            'rest'    => $recordService->getOvertimeNumByDateType($teamMember, $month, 2),
            'holiday' => 0,
        ];

        return [$clockStatistics, $overtimeStatistics, $recordService->getPersonLeaveMonthStatistics($teamMember, $month)];
    }

    /**
     * 获取个人月报打卡统计
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    private function getPersonMonthStatistics(string $uuid, int $userId, string $month): array
    {
        $uid                                         = app()->get(AttendanceClockService::class)->getStatisticsUserId($uuid, $userId);
        [$absenteeism, $lackCard, $locationAbnormal] = $this->getLackCardAndAbsenteeismNum($uid, $month);
        $where                                       = ['month' => $month, 'uid' => $uid];

        $clockStatistics = [
            'lack_card'         => $lackCard,
            'absenteeism'       => $absenteeism,
            'location_abnormal' => $locationAbnormal,
            'total'             => $this->dao->count($where),
            'work_hours'        => sprintf('%.2f', $this->dao->avg($where, 'actual_work_hours')),
            'late'              => $this->dao->count(array_merge($where, ['status' => AttendanceClockEnum::ALL_LATE])),
            'leave_early'       => $this->dao->count(array_merge($where, ['status' => AttendanceClockEnum::LEAVE_EARLY])),
            'abnormal'          => $this->dao->count(array_merge($where, ['abnormal_status' => AttendanceClockEnum::NORMAL])),
        ];

        $recordService      = app()->get(AttendanceApplyRecordService::class);
        $overtimeStatistics = [
            'work'    => $recordService->getOvertimeByDateType($uid, $month, 1),
            'rest'    => $recordService->getOvertimeByDateType($uid, $month, 2),
            'holiday' => '0.00',
        ];

        return [$clockStatistics, $overtimeStatistics, $recordService->getPersonLeaveMonthStatistics($uid, $month)];
    }

    /**
     * 获取异常天数.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    private function getAbnormalDays(int $uid, string $month): int
    {
        $abnormal = 0;
        $shifts   = AttendanceClockEnum::SHIFT_CLASS;
        $records  = $this->dao->select(['month' => $month, 'uid' => $uid], ['*']);
        foreach ($records as $record) {
            if ($record->shift_id < 2) {
                continue;
            }

            $isRecord = false;
            $shiftNum = $record->shift_data['number'] * 2;
            for ($i = 0; $i < $shiftNum; ++$i) {
                if ($isRecord) {
                    break;
                }
                if ($record->{$shifts[$i] . '_shift_status'} > AttendanceClockEnum::NORMAL) {
                    ! $isRecord && $abnormal++;
                    $isRecord = true;
                }

                if ($record->{$shifts[$i] . '_shift_location_status'} > AttendanceClockEnum::OFFICE_OUTSIDE) {
                    ! $isRecord && $abnormal++;
                    $isRecord = true;
                }
            }
        }

        return $abnormal;
    }

    /**
     * 核对补卡条件.
     * @throws BindingResolutionException
     */
    private function checkGroupRepairAllowed(int $uid, $group): bool
    {
        if (! $group || ! $group->repair_allowed || empty($group->repair_type)) {
            return false;
        }

        if ($group->is_limit_number
            && (! $group->limit_number
                || (app()->get(ApproveApplyService::class)->getApplyNumByTypes($uid, ApproveEnum::PERSONNEL_SIGN) >= $group->limit_number))) {
            return false;
        }

        return true;
    }

    /**
     * 计算请假时长
     */
    private function calcLeaveDuration(string $duration, Carbon $startObj, Carbon $endObj): string
    {
        if ($startObj && $endObj && $endObj->gt($startObj)) {
            $duration = bcadd(bcdiv((string) $endObj->diffInMinutes($startObj), '60', 2), $duration, 2);
        }

        return $duration;
    }
}
