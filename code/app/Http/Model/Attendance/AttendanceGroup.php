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

use App\Http\Model\Admin\Admin;
use App\Http\Model\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 考勤组.
 */
class AttendanceGroup extends BaseModel
{
    use SoftDeletes;

    /**
     * 表名.
     * @var string
     */
    protected $table = 'attendance_group';

    /**
     * 主键.
     * @var string
     */
    protected $primaryKey = 'id';

    protected $hidden = [
        'deleted_at',
    ];

    /**
     * 格式化repair_type字段.
     * @param mixed $value
     */
    public function setRepairTypeAttribute($value): void
    {
        $this->attributes['repair_type'] = is_array($value) ? json_encode($value) : $value;
    }

    /**
     * repair_type字段获取器.
     * @param mixed $value
     * @return int[]
     */
    public function getRepairTypeAttribute($value): array
    {
        return $value ? array_map('intval', json_decode($value, true)) : [];
    }

    /**
     * 一对多关联考勤人员.
     * @return HasManyThrough
     */
    public function members()
    {
        return $this->hasManyThrough(
            Admin::class,
            AttendanceGroupMember::class,
            'group_id',
            'id',
            'id',
            'member'
        )->where('attendance_group_member.type', 0)->select(['admin.id', 'admin.name', 'admin.avatar']);
    }

    /**
     * 一对多关联考勤人员.
     * @return HasManyThrough
     */
    public function admin()
    {
        return $this->hasManyThrough(
            Admin::class,
            AttendanceGroupMember::class,
            'group_id',
            'id',
            'id',
            'member'
        )->where('attendance_group_member.type', 2)->select(['admin.id', 'admin.name', 'admin.avatar']);
    }

    /**
     * 一对多关联考勤人员.
     * @return HasOne
     */
    public function user()
    {
        return $this->hasOne(
            Admin::class,
            'id',
            'uid',
        )->select(['admin.id', 'admin.name', 'admin.avatar']);
    }

    /**
     * 一对多关联考勤班次
     * @return HasManyThrough
     */
    public function shifts()
    {
        return $this->hasManyThrough(
            AttendanceShift::class,
            AttendanceGroupShift::class,
            'group_id',
            'id',
            'id',
            'shift_id'
        )->select(['attendance_shift.id', 'attendance_shift.name', 'attendance_shift.color'])->with('times');
    }

    /**
     * 一对多关联无需考勤人员.
     * @return HasManyThrough
     */
    public function filters()
    {
        return $this->hasManyThrough(
            Admin::class,
            AttendanceGroupMember::class,
            'group_id',
            'id',
            'id',
            'member'
        )->where('attendance_group_member.type', 1)->select(['admin.id', 'admin.name', 'admin.avatar']);
    }

    /**
     * 一对多关联考勤负责人.
     * @return HasManyThrough
     */
    public function admins()
    {
        return $this->hasManyThrough(
            Admin::class,
            AttendanceGroupMember::class,
            'group_id',
            'id',
            'id',
            'member'
        )->where('attendance_group_member.type', 2)->select(['admin.id', 'admin.name', 'admin.avatar', 'admin.uid', 'admin.phone']);
    }

    /**
     * ID 作用域
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeNotId($query, $value): void
    {
        $query->where('id', '<>', $value);
    }

    /**
     * ID 作用域
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
}
