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

namespace App\Http\Model\Attendance;

use App\Http\Model\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

/**
 * 请假工时.
 */
class AttendanceStatisticsLeave extends BaseModel
{
    /**
     * 表名.
     * @var string
     */
    protected $table = 'attendance_statistics_leave';

    /**
     * 主键.
     * @var string
     */
    protected $primaryKey = 'id';

    protected $hidden = [
        'deleted_at',
    ];

    /**
     * created_at作用域
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeDate($query, $value): void
    {
        if ($value !== '') {
            $query->whereDate('created_at', $value);
        }
    }

    public function getDateAttribute(): string
    {
        return date('Y-m-d', strtotime($this->attributes['created_at']));
    }

    /**
     * date作用域
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeMonth($query, $value): void
    {
        if ($value !== '') {
            $query->where(DB::raw("DATE_FORMAT(created_at,'%Y-%m')"), $value);
        }
    }

    /**
     * uid 作用域
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeUid($query, $value): void
    {
        if (is_array($value)) {
            $query->whereIn('uid', $value);
        } elseif ($value !== '') {
            $query->where('uid', $value);
        }
    }

    /**
     * 一对一关联考勤.
     * @return HasOne
     */
    public function statistics()
    {
        return $this->hasOne(AttendanceStatistics::class, 'id', 'statistics_id')->select([
            'attendance_statistics.id',
            'attendance_statistics.required_work_hours',
        ]);
    }
}
