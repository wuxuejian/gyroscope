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

use App\Http\Model\Crud\SystemCrudField;

/**
 * Class SystemCrudFieldObserver.
 */
class SystemCrudFieldObserver
{
    /**
     * Handle the SystemCrudField "created" event.
     */
    public function created(SystemCrudField $systemCrudField)
    {
        event('system.crud');
    }

    /**
     * Handle the SystemCrudField "updated" event.
     */
    public function updated(SystemCrudField $systemCrudField)
    {
        event('system.crud');
    }

    /**
     * Handle the SystemCrudField "deleted" event.
     */
    public function deleted(SystemCrudField $systemCrudField)
    {
        event('system.crud');
    }

    /**
     * Handle the SystemCrudField "restored" event.
     */
    public function restored(SystemCrudField $systemCrudField)
    {
        event('system.crud');
    }

    /**
     * Handle the SystemCrudField "force deleted" event.
     */
    public function forceDeleted(SystemCrudField $systemCrudField)
    {
        event('system.crud');
    }
}
