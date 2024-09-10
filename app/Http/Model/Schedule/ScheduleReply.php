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

namespace App\Http\Model\Schedule;

use App\Http\Model\Admin\Admin;
use App\Http\Model\BaseModel;
use crmeb\traits\model\TimeDataTrait;

/**
 * 日程表.
 */
class ScheduleReply extends BaseModel
{
    use TimeDataTrait;

    /**
     * 表名.
     * @var string
     */
    protected $table = 'schedule_reply';

    /**
     * 主键.
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * pid作用域
     */
    public function scopePid($query, $value)
    {
        if (is_array($value)) {
            $query->whereIn('pid', $value);
        } elseif ($value !== '') {
            $query->where('pid', $value);
        }
    }

    public function scopeTimeZone($query, $value)
    {
        [$start,$end] = explode(' - ', $value);
        $query->where('start_time', $start)->where('end_time', $end);
    }

    public function from_user()
    {
        return $this->hasOne(Admin::class, 'id', 'uid');
    }

    public function to_user()
    {
        return $this->hasOne(Admin::class, 'id', 'to_uid');
    }
}
