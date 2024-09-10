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
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 *  客户发票操作日志.
 */
class ClientInvoiceLog extends BaseModel
{
    /**
     * @var string
     */
    protected $table = 'client_invoice_log';

    /**
     * @var string
     */
    protected $primaryKey = 'id';

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

    /**
     * 一对一远程关联用户.
     * @return HasOne
     */
    public function card()
    {
        return $this->hasOne(Admin::class, 'id', 'card_id')->select(['id', 'name', 'avatar', 'uid']);
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

    /**
     * operation字段转json.
     */
    public function setOperationAttribute($value)
    {
        $this->attributes['operation'] = json_encode($value);
    }

    /**
     * operation字段转回数组.
     * @return mixed
     */
    public function getOperationAttribute($value)
    {
        return json_decode($value, true);
    }
}
