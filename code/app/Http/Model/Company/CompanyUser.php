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

use App\Http\Model\Attendance\AttendanceGroup;
use App\Http\Model\Attendance\AttendanceGroupMember;
use App\Http\Model\Auth\Role;
use App\Http\Model\Auth\RoleUser;
use App\Http\Model\Auth\UserRole;
use App\Http\Model\BaseModel;
use App\Http\Model\Frame\Frame;
use App\Http\Model\Frame\FrameAssist;
use App\Http\Model\Frame\FrameScope;
use App\Http\Model\Position\Job;
use App\Http\Model\User\User;
use App\Http\Model\User\UserEnterprise;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder;

/**
 * 企业用户.
 * @deprecated
 */
class CompanyUser extends BaseModel
{
    use SoftDeletes;

    /**
     * 表名.
     * @var string
     */
    protected $table = 'user_enterprise';

    /**
     * 主键.
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * 名片.
     * @return HasOne
     */
    public function card()
    {
        return $this->hasOne(UserCard::class, 'id', 'card_id');
    }

    /**
     * 用户.
     * @return HasOne
     */
    public function user()
    {
        return $this->hasOne(User::class, 'uid', 'uid');
    }

    /**
     * 关联企业.
     * @return HasOne
     */
    public function enterprise()
    {
        return $this->hasOne(Company::class, 'id', 'entid');
    }

    /**
     * 远程一对一关联部门.
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
        )->where('frame_assist.is_mastart', 1)
            ->select([
                'frame.id',
                'frame.name',
                'frame.user_count',
                'frame_assist.is_mastart',
                'frame_assist.is_admin',
            ]);
    }

    /**
     * 远程一对一关联上级.
     * @return HasManyThrough
     */
    public function super()
    {
        return $this->hasOneThrough(
            UserEnterprise::class,
            FrameAssist::class,
            'user_id',
            'id',
            'id',
            'superior_uid'
        )->where('frame_assist.is_mastart', 1)->where('frame_assist.is_admin', 1);
    }

    /**
     * @return HasMany
     */
    public function frameIds()
    {
        return $this->hasMany(FrameAssist::class, 'user_id', 'id');
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
     * 一对一关联职位.
     * @return HasOne
     */
    public function job()
    {
        return $this->hasOne(Job::class, 'id', 'job');
    }

    /**
     * 成员权限.
     * @return HasOne
     */
    public function rules()
    {
        return $this->hasOne(UserRole::class, 'user_id', 'id');
    }

    /**
     * @return HasOne
     */
    public function assess()
    {
        return $this->hasOne(UserAssess::class, 'test_uid', 'id');
    }

    /**
     * 管理员关联.
     * @return HasManyThrough
     */
    public function roles()
    {
        return $this->hasManyThrough(
            Role::class,
            RoleUser::class,
            'user_id',
            'id',
            'id',
            'role_id'
        );
    }

    /**
     * 一对一关联.
     * @return HasOne
     */
    public function isAdmin()
    {
        return $this->hasOne(FrameAssist::class, 'user_id', 'id')
            ->where('is_mastart', 1);
    }

    /**
     * 设置roles字段.
     * @param mixed $value
     */
    public function setRolesAttribute($value)
    {
        $this->attributes['roles'] = json_encode($value);
    }

    /**
     * 解析roles字段.
     * @param mixed $value
     * @return mixed
     */
    public function getRolesAttribute($value)
    {
        return array_map('intval', $value ? json_decode($value, true) : []);
    }

    /**
     * ids作用域
     * @param Builder $query
     * @param mixed $value
     * @return Builder
     */
    public function scopeIds($query, $value)
    {
        $query->whereIn('id', $value);
    }

    /**
     * ids作用域
     * @param Builder $query
     * @param mixed $value
     * @return Builder
     */
    public function scopeCardId($query, $value)
    {
        if (is_array($value)) {
            $query->whereIn('card_id', $value);
        } elseif ($value !== '') {
            $query->where('card_id', $value);
        }
    }

    /**
     * entid作用域
     * @param Builder $query
     * @param mixed $value
     * @return Builder
     */
    public function scopeEntid($query, $value)
    {
        if ($value !== '') {
            return $query->where('entid', $value);
        }
    }

    /**
     * uid作用域
     * @param Builder $query
     * @param mixed $value
     * @return Builder
     */
    public function scopeUid($query, $value)
    {
        if (is_bool($value)) {
            $value ? $query->where('uid', '<>', '') : $query->where('uid', '');
        } elseif ($value !== '') {
            if (is_array($value)) {
                $query->whereIn('uid', $value);
            } else {
                $query->where('uid', $value);
            }
        }
    }

    /**
     * uid作用域
     * @param Builder $query
     * @param mixed $value
     * @return Builder
     */
    public function scopeId($query, $value)
    {
        if (is_array($value)) {
            $query->whereIn('id', $value);
        } elseif ($value !== '') {
            $query->where('id', $value);
        }
    }

    /**
     * ids作用域
     * @param Builder $query
     * @param mixed $value
     * @return Builder
     */
    public function scopeNotid($query, $value)
    {
        if (is_array($value)) {
            $query->whereNotIn('id', $value);
        } elseif ($value !== '') {
            return $query->where('id', '<>', $value);
        }
    }

    /**
     * path作用域
     * @param Builder $query
     * @param mixed $value
     * @return Builder
     */
    public function scopePath($query, $value)
    {
        if (is_array($value)) {
            $query->where(function ($q) use ($value) {
                foreach ($value as $val) {
                    $q->orWhere('path', 'like', '%/' . $val . '/%');
                }
            });
        } elseif ($value !== '') {
            $query->where('path', 'like', '%/' . $value . '/%');
        }
    }

    /**
     * name作用域
     * @param Builder $query
     * @param mixed $value
     * @return Builder
     */
    public function scopeNameLike($query, $value)
    {
        if ($value !== '') {
            $query->where(function ($q) use ($value) {
                $q->orWhere('name', 'like', '%' . $value . '%')->orWhere('phone', 'like', '%' . $value . '%');
            });
        }
    }

    /**
     * 岗位.
     * @return HasOne
     */
    //    public function job()
    //    {
    //        return $this->hasOne(Job::class, 'id', 'position');
    //    }

    /**
     * 直属上级.
     * @return HasOneThrough
     */
    public function senior()
    {
        return $this->hasOneThrough(self::class, FrameAssist::class, 'user_id', 'id', 'id', 'superior_uid')
            ->where('is_admin', 1)->where('is_mastart', 1);
    }

    /**
     * 管理范围.
     * @return HasManyThrough
     */
    public function scope()
    {
        return $this->hasManyThrough(Frame::class, FrameScope::class, 'uid', 'id', 'id', 'link_id');
    }

    /**
     * uid作用域
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeUidNot($query, $value)
    {
        $query->where('uid', '<>', '');
    }

    /**
     * search作用域
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeSearch($query, $value)
    {
        $query->where('name', 'like', '%' . $value . '%')
            ->orWhere('phone', 'like', '%' . $value . '%');
    }

    /**
     * 远程一对一关联考勤组.
     * @return HasOneThrough
     */
    public function group()
    {
        return $this->hasOneThrough(
            AttendanceGroup::class,
            AttendanceGroupMember::class,
            'member',
            'id',
            'id',
            'group_id'
        );
    }
}
