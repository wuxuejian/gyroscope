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

use App\Http\Model\BaseModel;
use crmeb\traits\model\TimeDataTrait;
use Illuminate\Database\Query\Builder;

/**
 * 客户合同发票类目
 * Class ClientInvoiceCategory.
 */
class ClientInvoiceCategory extends BaseModel
{
    use TimeDataTrait;

    protected $table = 'client_invoice_category';

    protected $primaryKey = 'id';

    /**
     * id作用域
     * @param Builder $query
     * @param mixed $value
     */
    public function scopeNotid($query, $value)
    {
        if ($value) {
            $query->where('id', '<>', $value);
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
            return $query->where('name', 'like', '%' . $value . '%');
        }
    }
}
