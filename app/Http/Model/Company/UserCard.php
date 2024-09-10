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

namespace App\Http\Model\Company;

use App\Http\Model\BaseModel;
use App\Http\Model\Frame\Frame;
use App\Http\Model\Frame\FrameAssist;
use App\Http\Model\Frame\FrameScope;
use App\Http\Model\Position\Job;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * 企业用户名片
 * Class UserCard.
 * @deprecated
 */
class UserCard extends BaseModel
{
    use SoftDeletes;

    /**
     * 表名.
     * @var string
     */
    protected $table = 'enterprise_user_card';

    /**
     * 主键.
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * @var string[]
     */
    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    /**
     * 工作经历.
     * @return HasMany
     */
    public function works()
    {
        return $this->hasMany(UserWork::class, 'card_id', 'id');
    }

    /**
     * 教育经历.
     * @return HasMany
     */
    public function educations()
    {
        return $this->hasMany(UserEducation::class, 'card_id', 'id');
    }

    /**
     * 任职经历.
     * @return HasMany
     */
    public function positions()
    {
        return $this->hasMany(UserPosition::class, 'card_id', 'id');
    }

    /**
     * 岗位.
     * @return HasOne
     */
    public function job()
    {
        return $this->hasOne(Job::class, 'id', 'position');
    }

    public function userEnt()
    {
        return $this->hasOne(CompanyUser::class, 'card_id', 'id');
    }

    public function scope()
    {
        return $this->hasManyThrough(Frame::class, FrameScope::class, 'uid', 'id', 'id', 'link_id');
    }

    /**
     * 远程一对一
     * @return HasOneThrough
     */
    public function frame()
    {
        return $this->hasOneThrough(
            Frame::class,
            FrameAssist::class,
            'user_id',
            'id',
            'user_id',
            'frame_id'
        )->select([
            'frame.name',
            'frame.id',
            'frame_assist.user_id',
            'frame_assist.frame_id',
        ])->where('frame_assist.is_mastart', 1);
    }

    /**
     * 远程一对多关联部门.
     * @return HasManyThrough
     */
    public function frames()
    {
        return $this->hasManyThrough(
            Frame::class,
            FrameAssist::class,
            'user_id',
            'id',
            'user_id',
            'frame_id'
        )->select([
            'frame.id',
            'frame.name',
            'frame.user_count',
            'frame_assist.is_mastart',
            'frame_assist.is_admin',
            'frame_assist.superior_uid',
        ])->orderByDesc('frame_assist.is_mastart');
    }

    /**
     * uid作用域
     * @return mixed
     */
    public function scopeUid($query, $val)
    {
        if ($val !== '') {
            if (is_bool($val)) {
                return $val ? $query->where('uid', '!=', '') : $query->where('uid', '');
            }
            return $query->where('uid', $val);
        }
    }

    /**
     * uid作用域
     * @return mixed
     */
    public function scopeEntid($query, $val)
    {
        if ($val !== '') {
            return $query->where('entid', $val);
        }
    }

    /**
     * uid作用域
     * @return mixed
     */
    public function scopeId($query, $val)
    {
        if ($val !== '') {
            return $query->where('id', $val);
        }
    }

    /**
     * NOtId作用域
     * @return mixed
     */
    public function scopeNotId($query, $val)
    {
        if ($val !== '') {
            return $query->where('id', '<>', $val);
        }
    }

    /**
     * id作用域
     * @param mixed $value
     * @return mixed
     */
    public function scopeIds($query, $value)
    {
        if (is_array($value)) {
            return $query->whereIn('id', $value);
        }
        return $query->where('id', $value);
    }

    /**
     * uid作用域
     */
    public function scopeUids($query, $value)
    {
        $query->whereIn('uid', $value);
    }

    /**
     * id作用域
     * @param mixed $value
     * @return mixed
     */
    public function scopeTypes($query, $value)
    {
        if (is_array($value)) {
            return $query->whereIn('type', $value);
        }
        if ($value !== '') {
            return $query->where('type', $value);
        }
    }

    /**
     * id作用域
     * @param mixed $query
     * @return mixed
     */
    public function scopeNotTypes($query, $value)
    {
        if (is_array($value)) {
            return $query->whereNotIn('type', $value);
        }
        if ($value !== '') {
            return $query->where('type', '<>', $value);
        }
    }

    /**
     * 合同名称作用域
     * @param Builder $query
     * @return Builder
     */
    public function scopeNameLike($query, $value)
    {
        return $value !== '' ? $query->where('name', 'LIKE', "%{$value}%") : null;
    }

    /**
     * phone作用域
     * @return mixed
     */
    public function scopePhone($query, $val)
    {
        if ($val !== '') {
            return $query->where('phone', $val);
        }
    }

    /**
     * phone作用域
     * @return mixed
     */
    public function scopePosition($query, $val)
    {
        if ($val !== '') {
            return $query->where('position', $val);
        }
    }

    /**
     * phone作用域
     * @param mixed $value
     * @return mixed
     */
    public function scopeWorkTime($query, $value)
    {
        if ($value !== '') {
            if (str_contains($value, '-')) {
                [$startTime, $endTime] = explode('-', $value);
                $startTime             = str_replace('/', '-', trim($startTime));
                $endTime               = str_replace('/', '-', trim($endTime));
                if (! str_contains($startTime, ':') && ! str_contains($endTime, ':')) {
                    $endDate = Carbon::parse($endTime)->timezone(config('app.timezone'))->addDay()->toDateString();
                    return $query->whereDate('work_time', '>=', $startTime)->whereDate('work_time', '<', $endDate);
                }
                if ($startTime && $endTime && $startTime != $endTime) {
                    return $query->whereBetween('work_time', [$startTime, $endTime]);
                }
                if ($startTime && $endTime && $startTime == $endTime) {
                    return $query->whereBetween('work_time', [$startTime, date('Y-m-d H:i:s', strtotime($endTime) + 86400)]);
                }
                if (! $startTime && $endTime) {
                    return $query->whereTime('work_time', '<', $endTime);
                }
                if ($startTime && ! $endTime) {
                    return $query->whereTime('work_time', '>=', $startTime);
                }
            }
        }
    }

    /**
     * phone作用域
     * @param mixed $value
     * @return mixed
     */
    public function scopeQuitTime($query, $value)
    {
        if ($value !== '') {
            if (str_contains($value, '-')) {
                [$startTime, $endTime] = explode('-', $value);
                $startTime             = str_replace('/', '-', trim($startTime));
                $endTime               = str_replace('/', '-', trim($endTime));
                if (! str_contains($startTime, ':') && ! str_contains($endTime, ':')) {
                    $endDate = Carbon::parse($endTime)->timezone(config('app.timezone'))->addDay()->toDateString();
                    return $query->whereDate('quit_time', '>=', $startTime)->whereDate('quit_time', '<', $endDate);
                }
                if ($startTime && $endTime && $startTime != $endTime) {
                    return $query->whereBetween('quit_time', [$startTime, $endTime]);
                }
                if ($startTime && $endTime && $startTime == $endTime) {
                    return $query->whereBetween('quit_time', [$startTime, date('Y-m-d H:i:s', strtotime($endTime) + 86400)]);
                }
                if (! $startTime && $endTime) {
                    return $query->whereTime('quit_time', '<', $endTime);
                }
                if ($startTime && ! $endTime) {
                    return $query->whereTime('quit_time', '>=', $startTime);
                }
            }
        }
    }

    /**
     * phone作用域
     * @return mixed
     */
    public function scopeSearch($query, $val)
    {
        if ($val !== '') {
            $query->where(function ($q) use ($val) {
                $q->orWhere('name', 'LIKE', "%{$val}%")
                    ->orWhere('phone', 'LIKE', "%{$val}%");
            });
        }
    }
}
