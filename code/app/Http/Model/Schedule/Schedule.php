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

namespace App\Http\Model\Schedule;

use App\Http\Model\Admin\Admin;
use App\Http\Model\BaseModel;
use Carbon\Carbon;
use crmeb\traits\model\TimeDataTrait;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * 日程表.
 */
class Schedule extends BaseModel
{
    use TimeDataTrait;

    /**
     * 表名.
     * @var string
     */
    protected $table = 'schedule';

    /**
     * 主键.
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * 关联查询相关用户.
     * @return HasManyThrough
     */
    public function user()
    {
        return $this->hasManyThrough(Admin::class, ScheduleUser::class, 'schedule_id', 'id', 'id', 'uid')
            ->where('schedule_user.is_master', 0);
    }

    public function getDaysAttribute($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    public function setDaysAttribute($value)
    {
        $this->attributes['days'] = ! is_array($value) ? $value : json_encode($value);
    }

    public function setFailTimeAttribute($value)
    {
        $this->attributes['fail_time'] = $value ? Carbon::parse($value, config('app.timezone'))->endOfDay()->toDateTimeString() : null;
    }

    /**
     * 关联查询单个完成记录.
     * @return HasOne
     */
    public function taskOne()
    {
        return $this->hasOne(ScheduleTask::class, 'pid', 'id');
    }

    /**
     * 关联查询完成记录.
     * @return HasMany
     */
    public function task()
    {
        return $this->hasMany(ScheduleTask::class, 'pid', 'id');
    }

    /**
     * 关联查询日程类型.
     * @return HasOne
     */
    public function type()
    {
        return $this->hasOne(ScheduleType::class, 'id', 'cid');
    }

    public function scopeId($query, $value)
    {
        if (is_array($value)) {
            $query->whereIn('id', $value);
        } elseif ($value !== '') {
            $query->where('id', $value);
        }
    }

    public function scopeCid($query, $value)
    {
        if (is_array($value)) {
            $query->whereIn('cid', $value);
        } elseif ($value !== '') {
            $query->where('cid', $value);
        }
    }

    public function scopeLinkId($query, $value)
    {
        $query->where('link_id', $value);
    }

    public function master()
    {
        return $this->hasOne(Admin::class, 'id', 'uid');
    }

    public function remind()
    {
        return $this->hasOne(ScheduleRemind::class, 'sid', 'id');
    }

    /**
     * status 作用域
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeStatus($query, $value): void
    {
        if (is_array($value)) {
            $query->whereIn('status', $value);
        } elseif ($value !== '') {
            $query->where('status', $value);
        }
    }
}
