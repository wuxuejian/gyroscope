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

namespace App\Http\Model\Attendance;

use App\Http\Model\Admin\Admin;
use App\Http\Model\BaseModel;
use App\Http\Model\Frame\Frame;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

/**
 * 考勤统计.
 */
class AttendanceStatistics extends BaseModel
{
    use SoftDeletes;

    /**
     * 表名.
     * @var string
     */
    protected $table = 'attendance_statistics';

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
     */
    public function scopeDate($query, $value): void
    {
        if ($value !== '') {
            $query->whereDate('created_at', $value);
        }
    }

    public function setShiftDataAttribute($value): void
    {
        $this->attributes['shift_data'] = json_encode($value);
    }

    public function getShiftDataAttribute($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    public function getDateAttribute(): string
    {
        return date('Y-m-d', strtotime($this->attributes['created_at']));
    }

    /**
     * date作用域
     */
    public function scopeMonth($query, $value): void
    {
        if ($value !== '') {
            $query->where(DB::raw("DATE_FORMAT(created_at,'%Y-%m')"), $value);
        }
    }

    /**
     * uid 作用域
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
     * status 作用域
     */
    public function scopeStatus($query, $value): void
    {
        if (is_array($value)) {
            $query->whereIn('one_shift_status', $value)
                ->orWhereIn('two_shift_status', $value)
                ->orWhereIn('three_shift_status', $value)
                ->orWhereIn('four_shift_status', $value);
        } elseif ($value !== '') {
            $query->where('one_shift_status', $value)
                ->orWhere('two_shift_status', $value)
                ->orWhere('three_shift_status', $value)
                ->orWhere('four_shift_status', $value);
        }
    }

    /**
     * location_status 作用域
     */
    public function scopeLocationStatus($query, $value): void
    {
        if (is_array($value)) {
            $query->whereIn('one_shift_location_status', $value)
                ->orWhereIn('two_shift_location_status', $value)
                ->orWhereIn('three_shift_location_status', $value)
                ->orWhereIn('four_shift_location_status', $value);
        } elseif ($value !== '') {
            $query->where('one_shift_location_status', $value)
                ->orWhere('two_shift_location_status', $value)
                ->orWhere('three_shift_location_status', $value)
                ->orWhere('four_shift_location_status', $value);
        }
    }

    /**
     * location_status 作用域
     */
    public function scopeLocationStatusLt($query, $value): void
    {
        if ($value !== '') {
            $query->where('one_shift_location_status', '<', $value)
                ->orWhere('two_shift_location_status', '<', $value)
                ->orWhere('three_shift_location_status', '<', $value)
                ->orWhere('four_shift_location_status', '<', $value);
        }
    }

    /**
     * location_status 作用域
     */
    public function scopeLocationStatusGt($query, $value): void
    {
        if ($value !== '') {
            $query->where('one_shift_location_status', '>', $value)
                ->orWhere('two_shift_location_status', '>', $value)
                ->orWhere('three_shift_location_status', '>', $value)
                ->orWhere('four_shift_location_status', '>', $value);
        }
    }

    /**
     * 一对一远程关联用户.
     * @return HasOne
     */
    public function card()
    {
        return $this->hasOne(Admin::class, 'id', 'uid')->select(['id', 'uid', 'name', 'avatar', 'phone']);
    }

    /**
     * status 作用域
     */
    public function scopeStatusGt($query, $value): void
    {
        $query->where('one_shift_status', '>', $value)
            ->orWhere('two_shift_status', '>', $value)
            ->orWhere('three_shift_status', '>', $value)
            ->orWhere('four_shift_status', '>', $value);
    }

    /**
     * status 作用域
     */
    public function scopeStatusLt($query, $value): void
    {
        $query->where('one_shift_status', '<', $value)
            ->orWhere('two_shift_status', '<', $value)
            ->orWhere('three_shift_status', '<', $value)
            ->orWhere('four_shift_status', '<', $value);
    }

    /**
     * shift_id 作用域
     */
    public function scopeShiftIdGt($query, $value): void
    {
        if ($value !== '') {
            $query->where('shift_id', '>', $value);
        }
    }

    /**
     * 一对一关联考勤组.
     * @return HasOne
     */
    public function group()
    {
        return $this->hasOne(AttendanceGroup::class, 'id', 'group_id')->select(['attendance_group.id', 'attendance_group.name']);
    }

    /**
     * 一对一关联部门.
     *
     * @return HasOne
     */
    public function frame()
    {
        return $this->hasOne(Frame::class, 'id', 'frame_id')->select(['id', 'name']);
    }

    /**
     * created_at 作用域
     */
    public function scopeGtDate($query, $value): void
    {
        if ($value !== '') {
            $query->whereDate('created_at', '>', $value);
        }
    }

    /**
     *  abnormal_status 作用域
     */
    public function scopeAbnormalStatus($query, $value): void
    {
        if ($value !== '') {
            $query->where(function ($query) use ($value) {
                $query->where('one_shift_status', '>', $value)
                    ->orWhere('two_shift_status', '>', $value)
                    ->orWhere('three_shift_status', '>', $value)
                    ->orWhere('four_shift_status', '>', $value);
            })->orWhere(function ($query) {
                $query->where('one_shift_location_status', 2)
                    ->orWhere('two_shift_location_status', 2)
                    ->orWhere('three_shift_location_status', 2)
                    ->orWhere('four_shift_location_status', 2);
            });
        }
    }
}
