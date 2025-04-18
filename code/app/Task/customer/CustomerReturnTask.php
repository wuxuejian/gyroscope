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

use App\Http\Service\Client\CustomerService;
use Hhxsv5\LaravelS\Swoole\Task\Task;
use Illuminate\Support\Facades\Log;

/**
 * 客户退回公海事件
 * Class CustomerReturnTask.
 */
class CustomerReturnTask extends Task
{
    protected int $limit = 50;

    public function __construct() {}

    public function handle(): void
    {
        try {
            $switch = (int) sys_config('return_high_seas_switch');
            if ($switch < 1) {
                return;
            }

            app()->get(CustomerService::class)->autoReturnTimer();
        } catch (\Throwable $e) {
            Log::error('客户自动退回公海失败:' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTrace()]);
        }
    }
}
