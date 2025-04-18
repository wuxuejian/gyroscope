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

use App\Http\Model\Crud\SystemCrud;

class SystemCrudObserver
{
    /**
     * Handle the SystemCrud "created" event.
     */
    public function created(SystemCrud $systemCrud)
    {
        event('system.crud');
    }

    /**
     * Handle the SystemCrud "updated" event.
     */
    public function updated(SystemCrud $systemCrud)
    {
        event('system.crud');
    }

    /**
     * Handle the SystemCrud "deleted" event.
     */
    public function deleted(SystemCrud $systemCrud)
    {
        event('system.crud');
    }

    /**
     * Handle the SystemCrud "restored" event.
     */
    public function restored(SystemCrud $systemCrud)
    {
        event('system.crud');
    }

    /**
     * Handle the SystemCrud "force deleted" event.
     */
    public function forceDeleted(SystemCrud $systemCrud)
    {
        event('system.crud');
    }
}
