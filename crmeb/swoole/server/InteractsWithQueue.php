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

namespace crmeb\swoole\server;

use crmeb\swoole\queue\Manager as QueueManager;
use SwooleTW\Http\Server\Manager;

/**
 * Trait InteractsWithQueue.
 */
trait InteractsWithQueue
{
    public function prepareQueue(Manager $manager)
    {
        /** @var QueueManager $queueManager */
        $queueManager = $this->laravel->make(QueueManager::class);

        $queueManager->attachToServer($manager, $this->output);
    }
}
