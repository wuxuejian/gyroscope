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

namespace App\Task\crud;

use Hhxsv5\LaravelS\Swoole\Task\Task;

/**
 *  触发器-自动撤销审批.
 */
class TriggerAutoRevokeTask extends Task
{
    public function __construct(
        protected int $crudId,
        protected string $action,
        protected array $event,
        protected array $eventIds = [],
        protected int $dataId = 0,
        protected array $data = [],
        protected array $scheduleData = []
    ) {}

    public function handle()
    {
        // TODO: Implement handle() method.
    }
}
