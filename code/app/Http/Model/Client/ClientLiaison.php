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

/**
 * 客户联系人
 * Class ClientLiaison.
 */
class ClientLiaison extends BaseModel
{
    /**
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * @var string
     */
    protected $table = 'client_liaison';

    public function scopeEid($query, $value)
    {
        if (is_array($value)) {
            $query->whereIn('eid', $value);
        } elseif ($value !== '') {
            $query->where('eid', $value);
        }
    }

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
     * name作用域
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeNameLike($query, $value)
    {
        if ($value !== '') {
            $query->where('name', 'like', '%' . $value . '%');
        }
    }

    /**
     * tel作用域
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeTelLike($query, $value)
    {
        if ($value !== '') {
            $query->where('tel', 'like', '%' . $value . '%');
        }
    }

    /**
     * email作用域
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeEmailLike($query, $value)
    {
        if ($value !== '') {
            $query->where('email', 'like', '%' . $value . '%');
        }
    }
}
