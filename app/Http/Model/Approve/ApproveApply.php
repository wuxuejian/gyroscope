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

namespace App\Http\Model\Approve;

use App\Http\Model\Admin\Admin;
use App\Http\Model\BaseModel;
use crmeb\traits\model\TimeDataTrait;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder;

/**
 * 申请记录表
 * Class ApproveApply.
 */
class ApproveApply extends BaseModel
{
    use SoftDeletes;
    use TimeDataTrait;

    /**
     * 表名.
     * @var string
     */
    protected $table = 'approve_apply';

    /**
     * 主键.
     * @var string
     */
    protected $primaryKey = 'id';

    protected $hidden = [
        'deleted_at',
    ];

    /**
     * 一对一关联.
     * @return HasOne
     */
    public function approve()
    {
        return $this->hasOne(Approve::class, 'id', 'approve_id');
    }

    /**
     * 一对一关联用户名片.
     * @return HasOne
     */
    public function card()
    {
        return $this->hasOne(Admin::class, 'id', 'user_id')->select(['id', 'name', 'avatar', 'uid', 'phone']);
    }

    /**
     * 一对多关联.
     * @return HasMany
     */
    public function content()
    {
        return $this->hasMany(ApproveContent::class, 'apply_id', 'id');
    }

    /**
     * @return HasMany
     */
    public function users()
    {
        return $this->hasMany(ApproveUser::class, 'apply_id', 'id')->groupBy(['node_id']);
    }

    /**
     * 一对多关联.
     * @return HasMany
     */
    public function form()
    {
        return $this->hasMany(ApproveForm::class, 'approve_id', 'id');
    }

    /**
     * 一对多关联.
     * @return HasMany
     */
    public function reply()
    {
        return $this->hasMany(ApproveReply::class, 'apply_id', 'id');
    }

    /**
     * @return HasOne
     */
    public function rules()
    {
        return $this->hasOne(ApproveRule::class, 'approve_id', 'approve_id');
    }

    /**
     * id作用域
     * @param Builder $query
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
     * entid作用域
     * @param Builder $query
     */
    public function scopeEntid($query, $value)
    {
        if ($value !== '') {
            $query->where('entid', $value);
        }
    }

    /**
     * id作用域
     * @param Builder $query
     */
    public function scopeNotid($query, $value)
    {
        if (is_array($value)) {
            $query->whereNotIn('id', $value);
        }
    }

    /**
     * id作用域
     * @param Builder $query
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
     * user_id作用域
     * @param Builder $query
     */
    public function scopeUserId($query, $value)
    {
        if (is_array($value)) {
            $query->whereIn('user_id', $value);
        } else {
            $query->where('user_id', $value);
        }
    }

    /**
     * id作用域
     * @param Builder $query
     */
    public function scopeNotCardId($query, $value)
    {
        if (is_array($value)) {
            $query->whereNotIn('card_id', $value);
        } elseif ($value !== '') {
            $query->where('card_id', $value);
        }
    }

    /**
     * status作用域
     * @param Builder $query
     */
    public function scopeStatus($query, $value)
    {
        if ($value !== '') {
            $query->where('status', $value);
        }
    }

    /**
     * node_id作用域
     * @param Builder $query
     */
    public function scopeNodeId($query, $value)
    {
        if ($value !== '') {
            $query->where('node_id', $value);
        }
    }

    /**
     * status作用域
     * @param Builder $query
     */
    public function scopeNotStatus($query, $value)
    {
        if ($value !== '') {
            $query->where('status', '<>', $value);
        }
    }

    /**
     * approve_id作用域
     * @param Builder $query
     */
    public function scopeApproveId($query, $value)
    {
        if ($value !== '') {
            $query->where('approve_id', $value);
        }
    }

    /**
     * 职级.
     * @param Builder $query
     * @return mixed
     */
    public function scopeNameLike($query, $value)
    {
        if ($value !== '') {
            return $query->where(function ($q) use ($value) {
                $q->orWhere('name', 'like', '%' . $value . '%')->orWhere('number', 'like', '%' . $value . '%');
            });
        }
    }
}
