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

use App\Http\Model\BaseModel;
use Carbon\Carbon;
use crmeb\traits\model\TimeDataTrait;

/**
 * 日程提醒表.
 */
class ScheduleRemind extends BaseModel
{
    use TimeDataTrait;

    /**
     * 表名.
     * @var string
     */
    protected $table = 'schedule_remind';

    /**
     * 主键.
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * 重复日期获取器.
     * @param mixed $value
     * @return mixed
     */
    public function getDaysAttribute($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    /**
     * 重复日期修改器.
     * @param mixed $value
     * @return mixed
     */
    public function setDaysAttribute($value)
    {
        $this->attributes['days'] = $value ? json_encode($value) : '';
    }

    public function setEndTimeAttribute($value)
    {
        $this->attributes['end_time'] = $value ? Carbon::parse($value, config('app.timezone'))->endOfDay()->toDateTimeString() : null;
    }

    /**
     * @param mixed $query
     * @param mixed $value
     * @return mixed
     */
    public function scopeUid($query, $value)
    {
        if (is_array($value)) {
            $query->whereIn('uid', $value);
        } elseif ($value !== '') {
            return $query->where('uid', $value);
        }
    }

    /**
     * @param mixed $query
     * @param mixed $value
     * @return mixed
     */
    public function scopeSid($query, $value)
    {
        if (is_array($value)) {
            $query->whereIn('sid', $value);
        } elseif ($value !== '') {
            $query->where('sid', $value);
        }
    }

    /**
     * @param mixed $query
     * @param mixed $value
     * @return mixed
     */
    public function scopeEntid($query, $value)
    {
        if ($value !== '') {
            return $query->where('entid', $value);
        }
    }

    /**
     * @param mixed $query
     * @param mixed $value
     * @return mixed
     */
    public function scopeTypes($query, $value)
    {
        if (is_array($value) && $value) {
            return $query->whereIn('types', $value);
        }
        if ($value !== '') {
            return $query->where('types', $value);
        }
    }

    /**
     * @param mixed $query
     * @param mixed $value
     * @return mixed
     */
    public function scopeContent($query, $value)
    {
        if ($value !== '') {
            return $query->where('content', $value);
        }
    }

    /**
     * @param mixed $query
     * @param mixed $value
     * @return mixed
     */
    public function scopeRemind($query, $value)
    {
        if ($value !== '') {
            return $query->where('remind', $value);
        }
    }

    public function scopeEndTimeNot($query, $value)
    {
        $query->where(function ($query) {
            $query->whereNull('end_time')
                ->orWhere('end_time', '>=', now()->toDateTimeString());
        });
    }

    public function scopePeriodNot($query, $value)
    {
        $query->where('period', '<>', $value);
    }

    public function schedule()
    {
        return $this->hasOne(Schedule::class, 'id', 'sid');
    }
}
