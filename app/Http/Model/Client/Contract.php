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

use App\Http\Model\BaseModel;
use crmeb\traits\model\TimeDataTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 合同
 * Class Contract.
 */
class Contract extends BaseModel
{
    use SoftDeletes;
    use TimeDataTrait;

    /**
     * @var string
     */
    protected $table = 'contract';

    /**
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * 一对一关联客户.
     * @return HasOne
     */
    public function client()
    {
        return $this->hasOne(Customer::class, 'id', 'eid')->select([
            'customer.id',
            'customer.customer_name',
        ]);
    }

    /**
     * 客户ID作用域
     */
    public function scopeEid($query, $value)
    {
        if (is_array($value)) {
            $query->whereIn('eid', $value);
        } elseif ($value !== '') {
            $query->where('eid', $value);
        }
    }

    /**
     * 续费状态作用域
     */
    public function scopeRenew($query, $value)
    {
        if ($value !== '') {
            $query->where('renew', $value);
        }
    }

    /**
     * ID作用域
     */
    public function scopeId($query, $value)
    {
        if (is_array($value)) {
            $query->whereIn('id', $value);
        } elseif ($value !== '') {
            $query->where('id', $value);
        }
    }

    public function scopeUid($query, $value)
    {
        if (is_array($value)) {
            $query->whereIn('uid', $value);
        } elseif ($value !== '') {
            $query->where('uid', $value);
        }
    }

    /**
     * created_at作用域
     */
    public function scopeCreatedAt($query, $value)
    {
        $this->setTimeField('created_at')->scopeTime($query, $value);
    }

    /**
     * category_id作用域
     */
    public function scopeCategoryId($query, $value)
    {
        if (is_array($value)) {
            $query->whereIn('category_id', $value);
        } elseif ($value !== '') {
            $query->where('category_id', $value);
        }
    }

    /**
     * start_date作用域
     */
    public function scopeStartDate($query, $value)
    {
        $this->setTimeField('start_date')->scopeTime($query, $value);
    }

    /**
     * end_date作用域
     */
    public function scopeEndDate($query, $value): void
    {
        $this->setTimeField('end_date')->scopeTime($query, $value);
    }

    /**
     * signing_status作用域
     */
    public function scopeSigningStatus(Builder $query, $value): void
    {
        if ($value !== '') {
            $query->where('signing_status', $value);
        }
    }

    /**
     * 状态作用域
     */
    public function scopeStatus($query, $value)
    {
        if ($value == '') {
            return;
        }
        // 0：异常；1：未开始；2：进行中；3：已结束；
        switch ($value) {
            case 0:
                $query->where('is_abnormal', 1);
                break;
            case 1:
                $query->whereDate('start_date', '>', now(config('app.timezone'))->toDateString());
                break;
            case 2:
                $query->whereDate('start_date', '<', now(config('app.timezone'))->toDateString())->whereDate('end_date', '>', now(config('app.timezone'))->toDateString());
                break;
            case 3:
                $query->whereDate('end_date', '<', now(config('app.timezone'))->toDateString());
                break;
        }
    }

    /**
     * 结款状态
     */
    public function scopeAbnormal($query, $value): void
    {
        if ($value !== '') {
            switch ($value) {
                case 1:
                    $query->where('is_abnormal', 1);
                    break;
                case 3:
                    $query->whereDate('start_date', '>', now()->toDateString())->where('is_abnormal', 0);
                    break;
                case 2:
                    $query->whereDate('end_date', '<', now()->toDateString())->where('is_abnormal', 0)->whereNotNull('end_date');
                    break;
                default:
                    $query->where('is_abnormal', 0)->where(function ($query) {
                        $query->where(function ($query) {
                            $query->whereNotNull('end_date')->whereDate('start_date', '<=', now()->toDateString())->whereDate('end_date', '>', now()->toDateString());
                        })->orWhere(function ($query) {
                            $query->whereNull('end_date')->whereDate('start_date', '<=', now()->toDateString());
                        });
                    });
            }
        }
    }

    /**
     * not_id作用域
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
     * 结束时间.
     */
    public function setEndDateAttribute($value): void
    {
        $this->attributes['end_date'] = $value ?: null;
    }

    /**
     * 一对多关联续费.
     * @return HasMany
     */
    public function bills()
    {
        return $this->hasMany(ClientBill::class, 'cid', 'id')->where('types', 1)->select([
            'client_bill.id',
            'client_bill.cid',
            'client_bill.cate_id',
            'client_bill.num',
            'client_bill.date',
        ])->with(['renew'])->limit(3);
    }

    /**
     * signing_status作用域
     */
    public function scopeSigningStatusLt(Builder $query, $value): void
    {
        if ($value !== '') {
            $query->where('signing_status', '<', $value);
        }
    }

    /**
     * 结款状态
     */
    public function scopePayStatus($query, $value)
    {
        if ($value !== '') {
            switch ($value) {
                case 0:
                    $query->where('surplus', '>', 0);
                    break;
                case 1:
                    $query->where('surplus', 0);
                    break;
            }
        }
    }

    /**
     * contract_category 作用域
     */
    public function scopeContractCategory($query, $value): void
    {
        if (is_array($value)) {
            $query->whereIn('contract_category', $value);
        } elseif ($value !== '') {
            $query->where('contract_category', $value);
        }
    }

    /**
     * start_date 作用域
     */
    public function scopeStartDateGt($query, $value): void
    {
        if ($value) {
            $query->whereDate('start_date', '>=', $value);
        }
    }

    /**
     * end_date 作用域
     */
    public function scopeEndDateGt($query, $value): void
    {
        if ($value != '') {
            $query->whereDate('end_date', '>=', $value);
        }
    }

    /**
     * end_date lt 作用域
     */
    public function scopeEndDateLt($query, $value): void
    {
        if ($value != '') {
            $query->whereDate('end_date', '<', $value);
        }
    }

    /**
     * contract_status 作用域
     */
    public function scopeContractStatusLt(Builder $query, $value): void
    {
        if ($value !== '') {
            $query->where('contract_status', '<', $value);
        }
    }

    /**
     * contract_name 作用域
     * @param \Illuminate\Database\Query\Builder $query
     * @return mixed
     */
    public function scopeNameLike($query, $value)
    {
        if ($value) {
            return $query->where('contract_name', 'like', '%' . $value . '%');
        }
    }
}
