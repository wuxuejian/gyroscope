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

namespace App\Http\Model\Admin;

use App\Http\Model\Company\UserEducation;
use App\Http\Model\Company\UserJobAnalysis;
use App\Http\Model\Company\UserPosition;
use App\Http\Model\Company\UserWork;
use App\Http\Model\Frame\Frame;
use App\Http\Model\Frame\FrameAssist;
use App\Http\Model\Frame\FrameScope;
use App\Http\Model\Position\Job;
use App\Observers\AdminObserver;
use crmeb\interfaces\TimeDataInterface;
use crmeb\traits\model\TimeDataTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as AuthUser;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * 用户表.
 */
class Admin extends AuthUser implements JWTSubject, TimeDataInterface
{
    use SoftDeletes;
    use TimeDataTrait;

    /**
     * 表名.
     *
     * @var string
     */
    protected $table = 'admin';

    /**
     * 主键.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * 字段黑名单.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * @var string[]
     */
    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
        'uid'        => 'string',
    ];

    protected $hidden = ['password', 'deleted_at'];

    /**
     * 密码修改器.
     * @param mixed $value
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = $value ?: password_hash((string) time(), PASSWORD_BCRYPT);
    }

    /**
     * 权限修改器.
     * @param mixed $value
     */
    public function setRolesAttribute($value)
    {
        $this->attributes['roles'] = $value ? (is_array($value) ? json_encode($value) : '') : '';
    }

    /**
     * 权限获取器.
     * @param mixed $value
     */
    public function getRolesAttribute($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    public static function boot()
    {
        parent::boot();
        static::observe(AdminObserver::class);
    }

    /**
     * 设置主键id.
     *
     * @return mixed|string
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * 自定义声明.
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [
            'entId'      => $this->entid,
            'timestamps' => time(),
        ];
    }

    public function info(): HasOne
    {
        return $this->hasOne(AdminInfo::class, 'uid', 'uid');
    }

    public function user_card(): HasOne
    {
        return $this->hasOne(AdminInfo::class, 'id', 'id');
    }

    public function card(): HasOne
    {
        return $this->hasOne(AdminInfo::class, 'id', 'id');
    }

    public function frameIds(): HasMany
    {
        return $this->hasMany(FrameAssist::class, 'user_id', 'id');
    }

    /**
     * 一对一关联.
     */
    public function isAdmin(): HasOne
    {
        return $this->hasOne(FrameAssist::class, 'user_id', 'id');
    }

    /**
     * 状态作用域
     *
     * @param Builder $query
     * @param mixed $value
     *
     * @return mixed
     */
    public function scopeStatus($query, $value)
    {
        $query->where('status', $value);
    }

    /**
     * 性别作用域
     *
     * @param Builder $query
     * @param mixed $value
     *
     * @return mixed
     */
    public function scopeSex($query, $value)
    {
        return $value !== '' ? $query->where('sex', $value) : null;
    }

    public function scopeNotId($query, $value)
    {
        if (is_array($value)) {
            $query->whereNotIn('id', $value);
        } else {
            $query->where('id', '!=', $value);
        }
    }

    /**
     * 手机号作用域
     *
     * @param Builder $query
     * @param mixed $value
     *
     * @return mixed
     */
    public function scopePhoneLike($query, $value)
    {
        $query->where('phone', 'like', "%{$value}%");
    }

    /**
     * 手机号作用域
     * @param Builder $query
     * @param mixed $value
     * @return mixed
     */
    public function scopePhone($query, $value)
    {
        $query->where('phone', $value);
    }

    /**
     * 姓名作用域
     *
     * @param Builder $query
     * @param mixed $value
     *
     * @return mixed
     */
    public function scopeName($query, $value)
    {
        $query->where('name', 'like', "%{$value}%");
    }

    /**
     * UID作用域
     *
     * @param Builder $query
     * @param mixed $value
     *
     * @return mixed
     */
    public function scopeUid($query, $value)
    {
        if (is_array($value)) {
            $query->whereIn('uid', $value);
        } else {
            $query->where('uid', $value);
        }
    }

    /**
     * ID不等于作用域
     *
     * @param Builder $query
     * @param mixed $value
     *
     * @return Builder
     */
    public function scopeNotUid($query, $value)
    {
        return $value ? $query->where('uid', '<>', $value) : null;
    }

    /**
     * 岗位.
     * @return HasOne
     */
    public function job()
    {
        return $this->hasOne(Job::class, 'id', 'job');
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
            'id',
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
     * 远程一对多关联负责部门.
     * @return HasManyThrough
     */
    public function manage_frames()
    {
        return $this->hasManyThrough(
            Frame::class,
            FrameAssist::class,
            'user_id',
            'id',
            'id',
            'frame_id'
        )->select([
            'frame.id',
            'frame.name',
            'frame.user_count',
            'frame_assist.is_mastart',
            'frame_assist.is_admin',
            'frame_assist.superior_uid',
        ])->where('frame_assist.is_admin', 1)->orderByDesc('frame_assist.is_mastart');
    }

    /**
     * 远程一对一关联主部门.
     * @return HasManyThrough
     */
    public function frame()
    {
        return $this->hasOneThrough(
            Frame::class,
            FrameAssist::class,
            'user_id',
            'id',
            'id',
            'frame_id'
        )->select([
            'frame.id',
            'frame.name',
            'frame.user_count',
            'frame_assist.is_mastart',
            'frame_assist.is_admin',
            'frame_assist.superior_uid',
        ])->where('frame_assist.is_mastart', 1);
    }

    /**
     * 远程一对一关联上级.
     * @return HasManyThrough
     */
    public function super()
    {
        return $this->hasOneThrough(
            self::class,
            FrameAssist::class,
            'user_id',
            'id',
            'id',
            'superior_uid'
        );
    }

    /**
     * 工作经历.
     * @return HasMany
     */
    public function works()
    {
        return $this->hasMany(UserWork::class, 'user_id', 'id');
    }

    /**
     * 教育经历.
     * @return HasMany
     */
    public function educations()
    {
        return $this->hasMany(UserEducation::class, 'user_id', 'id');
    }

    /**
     * 任职经历.
     * @return HasMany
     */
    public function positions()
    {
        return $this->hasMany(UserPosition::class, 'user_id', 'id');
    }

    public function scope()
    {
        return $this->hasManyThrough(Frame::class, FrameScope::class, 'uid', 'id', 'id', 'link_id');
    }

    /**
     * id 作用域
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeId($query, $value)
    {
        if (is_array($value)) {
            $query->whereIn('id', $value);
        } else {
            $query->where('id', $value);
        }
    }

    /**
     * name 作用域
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeNameEq($query, $value)
    {
        if (is_array($value)) {
            $query->whereIn('name', $value);
        } else {
            $query->where('name', $value);
        }
    }

    /**
     * 一对一关联用户分析.
     * @return HasOne
     */
    public function jobAnalysis()
    {
        return $this->hasOne(UserJobAnalysis::class, 'uid', 'id');
    }

    /**
     * name作用域
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeNameLike($query, $value): void
    {
        if ($value !== '') {
            $query->where('name', 'like', '%' . $value . '%');
        }
    }

    /**
     * 设置时间.
     *
     * @return string
     */
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
