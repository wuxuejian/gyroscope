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

namespace App\Http\Model\Config;

use App\Http\Model\BaseModel;

/**
 * 云存储
 * Class SystemStorage.
 */
class SystemStorage extends BaseModel
{
    protected $table = 'system_storage';

    protected $primaryKey = 'id';

    public function scopeNameAttr($query, $value)
    {
        $query->where('name', $value);
    }

    /**
     * 类型搜索器.
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeTypeAttr($query, $value)
    {
        if ($value) {
            $query->where('type', $value);
        }
    }

    /**
     * 状态搜索器.
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeStatusAttr($query, $value)
    {
        $query->where('status', $value);
    }
}
