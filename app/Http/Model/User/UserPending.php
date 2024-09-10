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

namespace App\Http\Model\User;

use App\Http\Model\BaseModel;
use Illuminate\Database\Query\Builder;

/**
 * 用户待办
 * Class UserPending.
 */
class UserPending extends BaseModel
{
    /**
     * 表名.
     * @var string
     */
    protected $table = 'user_pending';

    /**
     * 主键.
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * status作用域
     * @param Builder $query
     * @return mixed
     */
    public function scopeStatus($query, $value)
    {
        if ($value !== '') {
            return $query->where('status', $value);
        }
    }
}
