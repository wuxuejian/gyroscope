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
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * 客户合同关注
 * Class ClientContractSubscribe.
 */
class ClientContractSubscribe extends BaseModel
{
    use TimeDataTrait;

    /**
     * @var string
     */
    protected $table = 'client_contract_subscribe';

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
     * 用户ID作用域
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
}
