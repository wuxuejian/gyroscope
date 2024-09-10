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
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * 收支记账操作日志
 * Class BillLog.
 */
class BillLog extends BaseModel
{
    /**
     * @var string
     */
    protected $table = 'client_bill_log';

    /**
     * @var string
     */
    protected $primaryKey = 'id';

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

    /**
     * 一对一关联用户.
     * @return HasOne
     */
    public function card()
    {
        return $this->hasOne(Admin::class, 'id', 'uid')->select(['id', 'name', 'avatar', 'uid', 'phone']);
    }
}
