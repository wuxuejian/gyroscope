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

namespace App\Jobs;

use Hhxsv5\LaravelS\Swoole\Timer\CronJob;

/**
 * 考勤定时执行任务
 * Class AttendStatisticsCronJob.
 */
class AttendStatisticsCronJob extends CronJob
{
    /**
     * 频率：每1s运行一次
     */
    public function interval(): int
    {
        return 1000;
    }

    public function run(): void
    {
        AttendStatisticsJob::dispatch(now(config('app.timezone')));
    }
}
