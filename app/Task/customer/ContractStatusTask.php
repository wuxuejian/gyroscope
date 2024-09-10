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

namespace App\Task\customer;

use App\Http\Service\Client\ContractService;
use Hhxsv5\LaravelS\Swoole\Task\Task;
use Illuminate\Support\Facades\Log;

/**
 * 合同状态事件
 * Class ContractStatusTask.
 */
class ContractStatusTask extends Task
{
    public function __construct() {}

    public function handle(): void
    {
        try {
            if (now(config('app.timezone'))->format('H') == '01') {
                app()->get(ContractService::class)->statusTimer();
            }
        } catch (\Throwable $e) {
            Log::error('合同状态定时任务执行失败：' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
        }
    }
}
