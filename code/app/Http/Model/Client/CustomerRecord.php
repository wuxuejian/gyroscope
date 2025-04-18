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
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * 客户记录
 * Class CustomerRecord.
 */
class CustomerRecord extends BaseModel
{
    /**
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * @var string
     */
    protected $table = 'customer_record';

    /**
     * 创建人.
     * @return HasOne
     */
    public function creator()
    {
        return $this->hasOne(Admin::class, 'id', 'creator_uid')->select(['id', 'avatar', 'name']);
    }

    /**
     * type 作用域
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeType($query, $value): void
    {
        if (is_array($value)) {
            $query->whereIn('type', $value);
        } elseif ($value !== '') {
            $query->where('type', $value);
        }
    }
}
