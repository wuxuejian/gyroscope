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

namespace App\Http\Model\Attendance;

use App\Http\Model\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 排班周期.
 */
class RosterCycleShift extends BaseModel
{
    use SoftDeletes;

    /**
     * 表名.
     * @var string
     */
    protected $table = 'roster_cycle_shift';

    /**
     * 主键.
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * cycle_id 作用域
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeCycleId($query, $value): void
    {
        if (is_array($value)) {
            $query->whereIn('cycle_id', $value);
        } elseif ($value !== '') {
            $query->where('cycle_id', $value);
        }
    }
}
