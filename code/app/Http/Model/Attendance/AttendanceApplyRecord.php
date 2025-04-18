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

/**
 * 审批记录.
 */
class AttendanceApplyRecord extends BaseModel
{
    /**
     * 表名.
     * @var string
     */
    protected $table = 'attendance_apply_record';

    /**
     * 主键.
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * ID作用域
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeId($query, $value): void
    {
        if (is_array($value)) {
            $query->whereIn('id', $value);
        } elseif ($value !== '') {
            $query->where('id', $value);
        }
    }

    /**
     * created_at作用域
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeDate($query, $value): void
    {
        if ($value !== '') {
            $query->whereDate('start_time', $value);
        }
    }

    /**
     * date作用域
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeMonth($query, $value): void
    {
        [$year,$month] = explode('-', $value);
        $query->whereMonth('start_time', $month)->whereYear('start_time', $year);
    }

    /**
     * date_type 作用域
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeDateType($query, $value): void
    {
        if (is_array($value)) {
            $query->whereIn('date_type', $value);
        } elseif ($value !== '') {
            $query->where('date_type', $value);
        }
    }

    /**
     * uid 作用域
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeUId($query, $value): void
    {
        if (is_array($value)) {
            $query->whereIn('uid', $value);
        } elseif ($value !== '') {
            $query->where('uid', $value);
        }
    }

    /**
     * apply_type 作用域
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeApplyType($query, $value): void
    {
        if (is_array($value)) {
            $query->whereIn('apply_type', $value);
        } elseif ($value !== '') {
            $query->where('apply_type', $value);
        }
    }

    /**
     * type_unique 作用域
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeTypeUnique($query, $value): void
    {
        if ($value !== '') {
            $query->where('others->type_unique', $value);
        }
    }

    /**
     * calc_type 作用域
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeCalcType($query, $value): void
    {
        if ($value !== '') {
            $query->where('others->calc_type', $value);
        }
    }

    /**
     * 其他数据.
     * @param mixed $value
     */
    public function setOthersAttribute($value): void
    {
        $this->attributes['others'] = json_encode($value);
    }

    /**
     * 其他数据.
     * @param mixed $value
     * @return array|mixed
     */
    public function getOthersAttribute($value): mixed
    {
        return $value ? json_decode($value, true) : [];
    }
}
