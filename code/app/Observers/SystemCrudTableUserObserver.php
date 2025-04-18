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

use App\Http\Model\Crud\SystemCrudTableUser;

class SystemCrudTableUserObserver
{
    /**
     * Handle the SystemCrudTableUser "created" event.
     */
    public function created(SystemCrudTableUser $systemCrudTableUser)
    {
        event('system.crud');
    }

    /**
     * Handle the SystemCrudTableUser "updated" event.
     */
    public function updated(SystemCrudTableUser $systemCrudTableUser)
    {
        event('system.crud');
    }

    /**
     * Handle the SystemCrudTableUser "deleted" event.
     */
    public function deleted(SystemCrudTableUser $systemCrudTableUser)
    {
        event('system.crud');
    }

    /**
     * Handle the SystemCrudTableUser "restored" event.
     */
    public function restored(SystemCrudTableUser $systemCrudTableUser)
    {
        event('system.crud');
    }

    /**
     * Handle the SystemCrudTableUser "force deleted" event.
     */
    public function forceDeleted(SystemCrudTableUser $systemCrudTableUser)
    {
        event('system.crud');
    }
}
