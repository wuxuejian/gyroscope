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

namespace App\Constants;

use MyCLabs\Enum\Enum;

/**
 * 汇报.
 */
final class DailyEnum extends Enum
{
    /**
     * 汇报状态：删除.
     */
    public const DAILY_DELETE = -1;

    /**
     * 汇报状态：未创建.
     */
    public const DAILY_NOT_SUB = 0;

    /**
     * 汇报状态：已创建.
     */
    public const DAILY_SUB = 1;

    /**
     * 数据关联通知.
     */
    public const LINK_NOTICE = [
        NoticeEnum::DAILY_SHOW_REMIND_TYPE,
        NoticeEnum::DAILY_UPDATE_REMIND_TYPE,
    ];

    /**
     * 无数据关联通知.
     */
    public const Not_Link_Notice = [
        NoticeEnum::DAILY_REMIND_TYPE,
    ];

    /**
     * 汇报类型.
     */
    public static function getDailyTypeName(int $types): string
    {
        return match ($types) {
            1       => '周报',
            2       => '月报',
            3       => '汇报',
            default => '日报',
        };
    }
}
