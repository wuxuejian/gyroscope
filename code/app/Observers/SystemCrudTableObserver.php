<?php

declare(strict_types=1);
/**
 *  +----------------------------------------------------------------------
 *  | 陀螺匠 [ 赋能开发者，助力企业发展 ]
 *  +----------------------------------------------------------------------
 *  | Copyright (c) 2016~2025 https://www.tuoluojiang.com All rights reserved.
 *  +----------------------------------------------------------------------
 *  | Licensed 陀螺匠并不是自由软件，未经许可不能去掉陀螺匠相关版权
 *  +----------------------------------------------------------------------
 *  | Author: 陀螺匠 Team <admin@tuoluojiang.com>
 *  +----------------------------------------------------------------------
 */

namespace App\Observers;

use App\Http\Model\Crud\SystemCrudTable;

class SystemCrudTableObserver
{
    /**
     * Handle the SystemCrudTable "created" event.
     */
    public function created(SystemCrudTable $systemCrudTable)
    {
        event('system.crud');
    }

    /**
     * Handle the SystemCrudTable "updated" event.
     */
    public function updated(SystemCrudTable $systemCrudTable)
    {
        event('system.crud');
    }

    /**
     * Handle the SystemCrudTable "deleted" event.
     */
    public function deleted(SystemCrudTable $systemCrudTable)
    {
        event('system.crud');
    }

    /**
     * Handle the SystemCrudTable "restored" event.
     */
    public function restored(SystemCrudTable $systemCrudTable)
    {
        event('system.crud');
    }

    /**
     * Handle the SystemCrudTable "force deleted" event.
     */
    public function forceDeleted(SystemCrudTable $systemCrudTable)
    {
        event('system.crud');
    }
}
