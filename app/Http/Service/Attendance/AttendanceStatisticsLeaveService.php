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

use App\Http\Dao\Attendance\AttendanceStatisticsLeaveDao;
use App\Http\Service\BaseService;
use Carbon\Carbon;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Cache;

/**
 * 考勤请假工时
 * Class AttendanceStatisticsLeaveService.
 */
class AttendanceStatisticsLeaveService extends BaseService
{
    public const CACHE_KEY = 'attendance_statistics_leave';

    public function __construct(AttendanceStatisticsLeaveDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 创建.
     * @throws BindingResolutionException
     */
    public function createLeaveRecord(int $statisticsId, int $uid, int $holidayTypeId, string $duration, int $applyRecordId, Carbon $createAt): bool
    {
        $res = $this->dao->create([
            'statistics_id'   => $statisticsId,
            'uid'             => $uid,
            'holiday_type_id' => $holidayTypeId,
            'leave_duration'  => $duration,
            'apply_record_id' => $applyRecordId,
            'created_at'      => $createAt,
            'updated_at'      => $createAt,
        ]);

        if ($res) {
            Cache::tags([self::CACHE_KEY])->flush();
        }
        return (bool) $res;
    }

    /**
     * 按月度获取请假时长
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getMonthLeaveDurationByHolidayTypeId(int $uid, string $month, int $holidayTypeId, int $durationType = 1): string
    {
        $key = md5(json_encode(['uid' => $uid, 'month' => $month, 'holiday_type_id' => $holidayTypeId, 'duration_type' => $durationType]));
        return Cache::tags([self::CACHE_KEY])->remember($key, (int) sys_config('system_cache_ttl', 3600), function () use ($uid, $month, $holidayTypeId, $durationType) {
            if ($durationType == 1) {
                return sprintf('%.2f', $this->dao->sum(['uid' => $uid, 'month' => $month, 'holiday_type_id' => $holidayTypeId], 'leave_duration'));
            }
            return $this->getDurationByHolidayType($uid, $month, $holidayTypeId);
        });
    }

    /**
     * 获取请假天数.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getDurationByHolidayType(int $uid, string $month, int $holidayTypeId): string
    {
        $duration = '0.00';
        $list     = $this->dao->select(['uid' => $uid, 'month' => $month, 'holiday_type_id' => $holidayTypeId], ['id', 'statistics_id', 'leave_duration'], ['statistics']);
        foreach ($list as $item) {
            if ($item?->statistics?->required_work_hours) {
                $duration = bcadd($duration, bcdiv((string) $item->leave_duration, (string) $item->statistics->required_work_hours, 2), 2);
            }
        }

        return $duration;
    }

    /**
     * 按天获取请假时长
     * @throws BindingResolutionException
     */
    public function getLeaveDurationByDate(int $uid, string $time, string $type = 'date'): string
    {
        $key = md5(json_encode(['uid' => $uid, 'time' => $time, 'type' => $type]));
        return Cache::tags([self::CACHE_KEY])->remember($key, (int) sys_config('system_cache_ttl', 3600), function () use ($uid, $time, $type) {
            return sprintf('%.2f', $this->dao->sum(['uid' => $uid, $type => $time], 'leave_duration'));
        });
    }

    /**
     * 根据月份获取假期类型.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getHolidayTypeIdByMonth(string $month, array|int $uid): array
    {
        return $this->dao->getHolidayTypeIds(['month' => $month, 'uid' => $uid]);
    }
}
