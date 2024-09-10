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

namespace App\Http\Dao\Attendance;

use App\Http\Dao\BaseDao;
use App\Http\Model\Attendance\AttendanceStatisticsLeave;
use crmeb\traits\dao\ListSearchTrait;
use crmeb\traits\dao\TogetherSearchTrait;
use Illuminate\Contracts\Container\BindingResolutionException;

/**
 * 请假工时Dao
 * Class AttendanceStatisticsLeaveDao.
 */
class AttendanceStatisticsLeaveDao extends BaseDao
{
    use ListSearchTrait;
    use TogetherSearchTrait;

    /**
     * 获取假期类型.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getHolidayTypeIds(array $where): array
    {
        return $this->search($where)->distinct()->select(['holiday_type_id'])->get()->map(function ($item) {
            return $item['holiday_type_id'];
        })->filter()->all();
    }

    /**
     * 设置模型.
     */
    protected function setModel(): string
    {
        return AttendanceStatisticsLeave::class;
    }
}
