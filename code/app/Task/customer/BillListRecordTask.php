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

use App\Http\Service\Finance\BillLogService;
use Hhxsv5\LaravelS\Swoole\Task\Task;
use Illuminate\Support\Facades\Log;

/**
 * 收支记账操作记录队列
 * Class BillListRecordTask.
 */
class BillListRecordTask extends Task
{
    public function __construct(protected int $entId, protected int $billListId, protected int $uid, protected int $type, protected array $param) {}

    /**
     * 执行队列.
     */
    public function handle(): void
    {
        try {
            app()->get(BillLogService::class)->saveRecord($this->entId, $this->billListId, $this->uid, $this->type, $this->param);
        } catch (\Throwable $e) {
            Log::error('收支记账记录失败:' . $e->getMessage(), ['trace' => $e->getTrace(), 'file' => $e->getFile(), 'line' => $e->getLine(), 'data' => $this->entId, $this->billListId, $this->uid, $this->type, $this->param]);
        }
    }
}
