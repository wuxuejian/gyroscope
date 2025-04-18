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
use App\Http\Model\Attach\SystemAttach;
use App\Http\Model\BaseModel;
use App\Http\Model\Config\GroupData;
use App\Http\Model\Frame\Frame;
use App\Http\Model\Frame\FrameAssist;
use crmeb\traits\model\TimeDataTrait;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class ClientBill.
 */
class ClientBill extends BaseModel
{
    use TimeDataTrait;

    /**
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * @var string
     */
    protected $table = 'client_bill';

    /**
     * 合同ID作用域
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
     * 续费类型.
     * @return HasOne
     */
    public function renew()
    {
        return $this->hasOne(GroupData::class, 'id', 'cate_id')->select(['value->title as title', 'id']);
    }

    /**
     * 关联合同.
     * @return HasOne
     */
    public function treaty()
    {
        return $this->hasOne(Contract::class, 'id', 'cid')
            ->select(['contract_name', 'id', 'contract_price', 'start_date', 'end_date', 'contract_no']);
    }

    /**
     * 关联客户.
     * @return HasOne
     */
    public function client()
    {
        return $this->hasOne(Customer::class, 'id', 'eid')->select(['customer_name', 'id']);
    }

    /**
     * 关联合同.
     * @return HasOne
     */
    public function contract()
    {
        return $this->hasOne(Contract::class, 'id', 'cid')->select(['contract_name', 'id']);
    }

    /**
     * 一对一关联用户.
     * @return HasOne
     */
    public function card()
    {
        return $this->hasOne(Admin::class, 'id', 'uid')->select(['id', 'uid', 'name', 'avatar', 'phone']);
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
            'uid',
            'frame_id'
        )->where('frame_assist.is_mastart', 1)
            ->select([
                'frame.id',
                'frame.name',
                'frame.user_count',
                'frame_assist.is_mastart',
            ]);
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
     * ID作用域
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeNotId($query, $value)
    {
        if ($value !== '') {
            $query->where('id', '<>', $value);
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

    public function scopeEid($query, $value)
    {
        if (is_array($value)) {
            $query->whereIn('eid', $value);
        } elseif ($value !== '') {
            $query->where('eid', $value);
        }
    }

    public function scopeEntid($query, $value)
    {
        if (is_array($value)) {
            $query->whereIn('entid', $value);
        } elseif ($value !== '') {
            $query->where('entid', $value);
        }
    }

    public function scopeTypes($query, $value)
    {
        if (is_array($value)) {
            $query->whereIn('types', $value);
        } elseif ($value == -1) {
            $query->whereIn('types', [0, 1]);
        } elseif ($value !== '') {
            $query->where('types', $value);
        }
    }

    /**
     * type_id作用域
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeTypeId($query, $value)
    {
        if ($value) {
            return $query->where('type_id', $value);
        }
    }

    /**
     * 附件一对多关联.
     *
     * @return HasMany
     */
    public function attachs()
    {
        return $this->hasMany(SystemAttach::class, 'relation_id', 'id')->where('relation_type', 2);
    }

    /**
     * date作用域
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeDate($query, $value)
    {
        $this->setTimeField('date')->scopeTime($query, $value);
    }

    /**
     * end_date作用域
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeEndDate($query, $value)
    {
        $this->setTimeField('end_date')->scopeTime($query, $value);
    }

    /**
     * 不包含撤回
     * updated_at作用域
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeNoWithdraw($query, $value)
    {
        if ($value != '') {
            $query->where('status', '>', -1);
        }
    }

    public function scopeUpdatedAt($query, $value)
    {
        $this->setTimeField('updated_at')->scopeTime($query, $value);
    }

    /**
     * mark作用域
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeMarkLike($query, $value)
    {
        if ($value !== '') {
            $query->where('mark', 'like', "%{$value}%");
        }
    }

    /**
     * bill_no作用域
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeBillNoLike($query, $value)
    {
        if ($value !== '') {
            $query->where('bill_no', 'like', "%{$value}%");
        }
    }

    /**
     * created_at作用域
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeCreatedAt($query, $value)
    {
        $this->setTimeField('created_at')->scopeTime($query, $value);
    }

    /**
     * 发票一对一关联.
     * @return HasOne
     */
    public function invoice()
    {
        return $this->hasOne(ClientInvoice::class, 'id', 'invoice_id')->select(['id', 'status']);
    }

    /**
     * invoice_id作用域
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeInvoiceId($query, $value)
    {
        if ($value !== '') {
            $query->where('invoice_id', $value);
        }
    }

    /**
     * status作用域
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeStatus($query, $value)
    {
        if (is_array($value)) {
            $query->whereIn('status', $value);
        } elseif ($value !== '') {
            $query->where('status', $value);
        }
    }

    /**
     * 续费结束时间.
     * @param mixed $value
     */
    public function getEndDateAttribute($value): string
    {
        return $value && ! str_contains($value, '0000-00-00') ? date('Y-m-d', strtotime($value)) : '0000-00-00';
    }

    /**
     * 结束时间作用域
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeEndDateGt($query, $value)
    {
        if ($value !== '') {
            $query->where(function ($query) use ($value) {
                $query->whereNull('end_date')->orWhere('end_date', '>', $value);
            });
        }
    }

    /**
     * 结束时间作用域
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeEndDateIt($query, $value)
    {
        if ($value !== '') {
            $query->where('end_date', '<', $value);
        }
    }

    public function scopeApplyBt($query, $value)
    {
        if ($value !== '') {
            $query->where('apply_id', '>', $value);
        }
    }

    /**
     * 关联业务员.
     * @return HasOne
     */
    public function salesman()
    {
        return $this->hasOne(Admin::class, 'id', 'uid')->select(['id', 'name', 'avatar']);
    }
}
