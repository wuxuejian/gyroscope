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

use App\Task\customer\ContractRenewRemindTask;
use Hhxsv5\LaravelS\Swoole\Task\Task;
use Hhxsv5\LaravelS\Swoole\Timer\CronJob;

/**
 * 合同急需续费提醒
 * 合同续费今日到期提醒
 * 合同续费过期提醒
 * Class ContractRenewRemindCronJob.
 */
class ContractRenewRemindCronJob extends CronJob
{
    /**
     * 频率：每30s运行一次
     * @return int
     */
    public function interval()
    {
        return 30000;
    }

    public function run()
    {
        Task::deliver(new ContractRenewRemindTask());
    }
}
