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

namespace App\Http\Model\Schedule;

use App\Http\Model\BaseModel;
use crmeb\traits\model\TimeDataTrait;

/**
 * 日程表.
 */
class ScheduleUser extends BaseModel
{
    use TimeDataTrait;

    public $timestamps = false;

    /**
     * 表名.
     * @var string
     */
    protected $table = 'schedule_user';

    /**
     * 主键.
     * @var string
     */
    protected $primaryKey = 'id';

    public function scopeUid($query, $value)
    {
        if (is_array($value)) {
            $query->whereIn('uid', $value);
        } else {
            $query->where('uid', $value);
        }
    }

    /**
     * schedule_id作用域
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeScheduleId($query, $value)
    {
        $query->where('schedule_id', $value);
        if (is_array($value)) {
            $query->whereIn('schedule_id', $value);
        } elseif ($value !== '') {
            $query->where('schedule_id', $value);
        }
    }
}
