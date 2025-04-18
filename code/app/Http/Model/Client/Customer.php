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

namespace App\Http\Model\Client;

use App\Http\Model\Admin\Admin;
use App\Http\Model\BaseModel;
use App\Http\Model\Config\GroupData;
use crmeb\traits\model\TimeDataTrait;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder;

/**
 * 客户
 * Class Customer.
 */
class Customer extends BaseModel
{
    use SoftDeletes;
    use TimeDataTrait;

    /**
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * @var string
     */
    protected $table = 'customer';

    /**
     * 客户标签.
     * @return HasManyThrough
     */
    public function label()
    {
        return $this->hasManyThrough(
            ClientLabel::class,
            ClientLabels::class,
            'eid',
            'id',
            'id',
            'label_id',
        )->select([
            'client_label.id',
            'client_label.name',
        ]);
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
     * 一对多关联联系人.
     * @return HasMany
     */
    public function liaison()
    {
        return $this->hasMany(ClientLiaison::class, 'eid', 'id')->select([
            'client_liaison.name',
            'client_liaison.id',
            'client_liaison.eid',
            'client_liaison.job',
            'client_liaison.gender',
            'client_liaison.tel',
            'client_liaison.mail',
        ]);
    }

    /**
     * 客户来源.
     * @return HasOne
     */
    public function way()
    {
        return $this->hasOne(GroupData::class, 'id', 'source')->select(['value->title as title', 'id']);
    }

    /**
     * 客户来源.
     * @return HasOne
     */
    public function track()
    {
        return $this->hasOne(ClientFollow::class, 'eid', 'id')->select(['eid', 'content', 'created_at'])->orderByDesc('client_follow.created_at');
    }

    /**
     * 客户分类.
     * @return HasOne
     */
    public function cate()
    {
        return $this->hasOne(GroupData::class, 'id', 'cid')->select(['value->title as title', 'id']);
    }

    /**
     * 分类ID作用域
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeCid($query, $value)
    {
        if (is_array($value)) {
            $query->whereIn('cid', $value);
        } elseif ($value !== '') {
            $query->where('cid', $value);
        }
    }

    /**
     * ID作用域
     * @param mixed $query
     * @param mixed $value
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
     * status作用域
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeStatus($query, $value)
    {
        if ($value !== '') {
            $query->where('status', $value);
        }
    }

    /**
     * UID作用域
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeUid($query, $value)
    {
        if (is_array($value)) {
            $query->whereIn('uid', $value);
        } elseif ($value !== '') {
            $query->where('uid', $value);
        }
    }

    /**
     * name作用域
     * @param Builder $query
     * @param mixed $value
     * @return mixed
     */
    public function scopeNameLike($query, $value)
    {
        if ($value) {
            return $query->where('customer_name', 'like', '%' . $value . '%');
        }
    }

    /**
     * email作用域
     * @param Builder $query
     * @param mixed $value
     * @return mixed
     */
    public function scopeEmailLike($query, $value)
    {
        if ($value) {
            return $query->where('email', 'like', '%' . $value . '%');
        }
    }

    /**
     * phone作用域
     * @param Builder $query
     * @param mixed $value
     * @return mixed
     */
    public function scopePhoneLike($query, $value)
    {
        if ($value) {
            return $query->where('phone', 'like', '%' . $value . '%');
        }
    }

    /**
     * label作用域
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeLabelLike($query, $value)
    {
        if (is_array($value)) {
            foreach ($value as $item) {
                $query->orWhere('label', 'like', '%"' . $item . '"%');
            }
        } else {
            return $query->where('label', 'like', '%"' . $value . '"%');
        }
    }

    /**
     * client_no 作用域
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeClientNoLike($query, $value)
    {
        if ($value) {
            return $query->where('client_no', 'like', '%' . $value . '%');
        }
    }

    /**
     * 未完成待办作用域
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeIncompleteScheduleId($query, $value)
    {
        if (is_array($value)) {
            $query->whereIn('id', $value);
        } elseif ($value !== '') {
            return $query->where('id', $value);
        }
    }

    /**
     * not_id作用域
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeNotId($query, $value): void
    {
        if (is_array($value)) {
            $query->whereNotIn('id', $value);
        } elseif ($value !== '') {
            $query->where('id', '<>', $value);
        }
    }

    /**
     * not_uid作用域
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeNotUId($query, $value): void
    {
        if (is_array($value)) {
            $query->whereNotIn('uid', $value);
        } elseif ($value !== '') {
            $query->where('uid', '<>', $value);
        }
    }

    /**
     * customer_status 作用域
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeCustomerStatusLt($query, $value): void
    {
        if ($value !== '') {
            $query->where('customer_status', '<', $value);
        }
    }

    /**
     * 业务员.
     * @return HasOne
     */
    public function salesman()
    {
        return $this->hasOne(Admin::class, 'id', 'uid')->select(['id', 'avatar', 'name', 'phone']);
    }

    /**
     * name 作用域
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeNameEq($query, $value)
    {
        if (is_array($value)) {
            $query->whereIn('customer_name', $value);
        } else {
            $query->where('customer_name', $value);
        }
    }
}
