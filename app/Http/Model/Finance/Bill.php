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

namespace App\Http\Model\Finance;

use App\Http\Model\Admin\Admin;
use App\Http\Model\BaseModel;
use App\Http\Model\Client\ClientBill;
use App\Http\Model\Client\Contract;
use App\Http\Model\Client\Customer;
use App\Http\Model\System\Attach;
use crmeb\traits\model\TimeDataTrait;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Query\Builder;

/**
 * 资金流水
 * Class Bill.
 */
class Bill extends BaseModel
{
    use TimeDataTrait;

    /**
     * 表名.
     * @var string
     */
    protected $table = 'bill_list';

    /**
     * 主键.
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * 一对一关联企业用户.
     * @return HasOne
     */
    public function user()
    {
        return $this->hasOne(Admin::class, 'id', 'uid');
    }

    /**
     * 一对一关联财务流水类型.
     * @return HasOne
     */
    public function cate()
    {
        return $this->hasOne(BillCategory::class, 'id', 'cate_id');
    }

    /**
     * types作用域
     * @param Builder $query
     * @return mixed
     */
    public function scopeTypes($query, $value)
    {
        if ($value !== '') {
            return $query->where('types', $value);
        }
    }

    /**
     * types作用域
     * @param Builder $query
     * @return mixed
     */
    public function scopeCateId($query, $value)
    {
        if (is_array($value)) {
            $query->whereIn('cate_id', $value);
        } elseif ($value !== '') {
            $query->where('cate_id', $value);
        }
    }

    /**
     * typeId作用域
     * @param Builder $query
     * @return mixed
     */
    public function scopeTypeId($query, $value)
    {
        if ($value !== '') {
            return $query->where('type_id', $value);
        }
    }

    /**
     * uid作用域
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
     * 附件一对多关联.
     *
     * @return HasMany
     */
    public function attachs()
    {
        return $this->hasMany(Attach::class, 'relation_id', 'link_id')
            ->where('relation_type', 2)->where('relation_id', '>', 0);
    }

    /**
     * 一对一关联付款单.
     * @return HasOne
     */
    public function clientBill()
    {
        return $this->hasOne(ClientBill::class, 'id', 'link_id');
    }

    /**
     * 一对一关联客户.
     * @return HasOneThrough
     */
    public function client()
    {
        return $this->hasOneThrough(Customer::class, ClientBill::class, 'id', 'id', 'link_id', 'eid');
    }

    /**
     * 一对一关联合同.
     * @return HasOneThrough
     */
    public function contract()
    {
        return $this->hasOneThrough(Contract::class, ClientBill::class, 'id', 'id', 'link_id', 'cid');
    }

    /**
     * 模糊查询.
     * @return mixed
     */
    public function scopeNameLike($query, $value)
    {
        return $query->where(function ($q) use ($value) {
            $q->orWhere('num', 'like', '%' . $value . '%')->orWhere('mark', 'like', '%' . $value . '%')->orWhereIn('id', function () use ($value) {
                return Admin::query()->where('name', 'like', '%' . $value . '%')->pluck('id');
            });
        });
    }
}
