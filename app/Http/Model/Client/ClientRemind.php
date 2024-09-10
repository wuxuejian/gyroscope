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

namespace App\Http\Model\Client;

use App\Http\Model\Admin\Admin;
use App\Http\Model\BaseModel;
use App\Http\Model\Config\GroupData;
use crmeb\traits\model\TimeDataTrait;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class ClientRemind.
 */
class ClientRemind extends BaseModel
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
    protected $table = 'client_remind';

    /**
     * 一对一远程关联用户.
     * @return HasOne
     */
    public function card()
    {
        return $this->hasOne(Admin::class, 'id', 'user_id')->select(['id', 'uid', 'name', 'avatar', 'phone']);
    }

    /**
     * 一对一关联客户.
     * @return HasOne
     */
    public function client()
    {
        return $this->hasOne(Customer::class, 'id', 'eid')->select([
            'customer.id',
            'customer.customer_name as name',
        ]);
    }

    /**
     * 关联合同.
     * @return mixed
     */
    public function treaty()
    {
        return $this->hasOne(Contract::class, 'id', 'cid')->withTrashed()
            ->select(['contract.contract_name as title', 'contract_price as price', 'start_date', 'end_date', 'id']);
    }

    /**
     * 一对一关联合同.
     * @return HasOne
     */
    public function contract()
    {
        return $this->hasOne(Contract::class, 'id', 'cid')->select([
            'contract.id',
            'contract.contract_name as title',
        ]);
    }

    /**
     * 续费类型.
     * @return HasOne
     */
    public function renew()
    {
        return $this->hasOne(GroupData::class, 'id', 'cate_id')->select(['value->title as title', 'id']);
    }

    public function scopeEid($query, $value)
    {
        if (is_array($value)) {
            $query->whereIn('eid', $value);
        } elseif ($value !== '') {
            $query->where('eid', $value);
        }
    }

    /**
     * 合同ID作用域
     */
    public function scopeCid($query, $value)
    {
        if (is_array($value)) {
            $query->whereIn('cid', $value);
        } elseif ($value !== '') {
            $query->where('cid', $value);
        }
    }

    public function scopeTypes($query, $value)
    {
        if ($value !== '') {
            $query->where('types', $value);
        }
    }

    public function scopeUniqued($query, $value)
    {
        if ($value !== '') {
            $query->where('uniqued', $value);
        }
    }

    /**
     * user_id作用域
     */
    public function scopeUserId($query, $value)
    {
        if (is_array($value)) {
            $query->whereIn('user_id', $value);
        } elseif ($value !== '') {
            $query->where('user_id', $value);
        }
    }

    /**
     * next_period作用域
     */
    public function scopeNextPeriodIt($query, $value)
    {
        if ($value !== '') {
            $query->where('next_period', '<', $value);
        }
    }

    /**
     * bill_id作用域
     */
    public function scopeBillId($query, $value)
    {
        if (is_array($value)) {
            $query->whereIn('bill_id', $value);
        } elseif ($value !== '') {
            $query->where('bill_id', $value);
        }
    }

    /**
     * time作用域
     */
    public function scopeTimeLt($query, $value)
    {
        if ($value !== '') {
            $query->where('time', '<', $value);
        }
    }
}
