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

use App\Http\Model\BaseModel;

/**
 * 考勤提醒.
 */
class AttendanceRemind extends BaseModel
{
    /**
     * 表名.
     * @var string
     */
    protected $table = 'attendance_remind';

    /**
     * 主键.
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * created_at作用域
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
     * 待推送作用域
     */
    public function scopeToBePushed($query, $value): void
    {
        $query->where(function ($query) {
            $query->whereNotNull('one_shift_remind')
                ->where('one_shift_remind_push', 0);
        })->orWhere(function ($query) {
            $query->whereNotNull('two_shift_remind')
                ->where('two_shift_remind_push', 0);
        })->orWhere(function ($query) {
            $query->whereNotNull('three_shift_remind')
                ->where('three_shift_remind_push', 0);
        })->orWhere(function ($query) {
            $query->whereNotNull('four_shift_remind')
                ->where('four_shift_remind_push', 0);
        });
    }

    /**
     * ID 作用域
     */
    public function scopeId($query, $value): void
    {
        if (is_array($value)) {
            $query->whereIn('id', $value);
        } elseif ($value !== '') {
            $query->where('id', $value);
        }
    }
}
