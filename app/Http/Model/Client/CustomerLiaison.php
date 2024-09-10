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
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 联系人
 * Class CustomerLiaison.
 */
class CustomerLiaison extends BaseModel
{
    use SoftDeletes;

    /**
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * @var string
     */
    protected $table = 'customer_liaison';

    /**
     * eid 作用域
     */
    public function scopeEid($query, $value): void
    {
        if (is_array($value)) {
            $query->whereIn('eid', $value);
        } elseif ($value !== '') {
            $query->where('eid', $value);
        }
    }

    /**
     * id 作用域
     */
    public function scopeId($query, $value): void
    {
        if (is_array($value)) {
            $query->whereIn('id', $value);
        } elseif ($value !== '') {
            $query->where('id', $value);
        }
    }

    /**
     * uid 作用域
     */
    public function scopeUid($query, $value): void
    {
        if (is_array($value)) {
            $query->whereIn('uid', $value);
        } elseif ($value !== '') {
            $query->where('uid', $value);
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

    public function scopeLiaisonTel($query, $value)
    {
        if ($value !== '') {
            $query->where('liaison_tel', 'like', "%{$value}%");
        }
    }

    public function scopeLiaisonName($query, $value)
    {
        if ($value !== '') {
            $query->where('liaison_name', 'like', "%{$value}%");
        }
    }
}
