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
 * 项目管理：任务状态
 */
final class TaskStatusEnum extends Enum
{
    /**
     * 任务状态：未处理.
     */
    public const UNTREATED = 0;

    /**
     * 任务状态：进行中.
     */
    public const IN_PROGRESS = 1;

    /**
     * 任务状态：已解决.
     */
    public const RESOLVED = 2;

    /**
     * 任务状态：已验收.
     */
    public const ACCEPTED = 3;

    /**
     * 任务状态：已拒绝.
     */
    public const REJECTED = 4;

    /**
     * 任务状态：已关闭.
     */
    public const CLOSED = 5;

    public static function getStatusText(int $status): string
    {
        return match ($status) {
            0       => '未处理',
            1       => '进行中',
            2       => '已解决',
            3       => '已验收',
            4       => '已拒绝',
            5       => '已关闭',
            default => '未知',
        };
    }
}
