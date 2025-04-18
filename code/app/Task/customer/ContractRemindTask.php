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
 * 合同提醒事件
 * Class ContractRemindTask.
 */
class ContractRemindTask extends Task
{
    protected int $limit = 50;

    public function __construct() {}

    public function handle(): void
    {
        try {
            $contractService = app()->get(ContractService::class);
            $sumCount        = $contractService->getClientContractCountCache();
            $sumPage         = ceil($sumCount / $this->limit);
            for ($i = 1; $i <= $sumPage; ++$i) {
                $contractService->timer($i, $this->limit);
            }
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . ':' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTrace()]);
        }
    }
}
