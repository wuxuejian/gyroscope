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

namespace App\Constants\ProgramEnum;

use MyCLabs\Enum\Enum;

/**
 * 项目管理：优先级.
 */
final class TaskPriorityEnum extends Enum
{
    /**
     * 任务优先级：紧急.
     */
    public const URGENT = 1;

    /**
     * 任务优先级：高.
     */
    public const HEIGHT = 2;

    /**
     * 任务优先级：中.
     */
    public const MIDDLE = 3;

    /**
     * 任务优先级：低.
     */
    public const LOW = 4;

    public static function getPriorityText(int $status): string
    {
        return match ($status) {
            0       => '无优先级',
            1       => '紧急',
            2       => '高',
            3       => '中',
            4       => '低',
            default => '未知',
        };
    }
}
