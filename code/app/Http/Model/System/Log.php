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

namespace App\Http\Model\System;

use App\Http\Model\BaseModel;
use crmeb\traits\model\TimeDataTrait;
use Illuminate\Database\Query\Builder;

/**
 * Class Log.
 */
class Log extends BaseModel
{
    use TimeDataTrait;

    /**
     * 表名.
     * @var string
     */
    protected $table = 'enterprise_log';

    /**
     * 主键.
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * 用户名作用域
     * @param Builder $query
     * @param mixed $value
     * @return mixed
     */
    public function scopeUserName($query, $value)
    {
        if ($value) {
            return $query->where('user_name', 'like', '%' . $value . '%');
        }
    }
}
