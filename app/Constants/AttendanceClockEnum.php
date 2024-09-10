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
 * 考勤打卡
 */
final class AttendanceClockEnum extends Enum
{
    /**
     * 无需打卡
     */
    public const NO_NEED_CLOCK = 0;

    /**
     * 正常.
     */
    public const NORMAL = 1;

    /**
     * 迟到.
     */
    public const LATE = 2;

    /**
     * 严重迟到.
     */
    public const EXTREME_LATE = 3;

    /**
     * 早退
     */
    public const LEAVE_EARLY = 4;

    /**
     * 晚到缺卡
     */
    public const LATE_LACK_CARD = 5;

    /**
     * 早退缺卡
     */
    public const EARLY_LACK_CARD = 6;

    /**
     * 未打卡缺卡
     */
    public const LACK_CARD = 7;

    /**
     * 内勤.
     */
    public const OFFICE_STAFF = 0;

    /**
     * 外勤.
     */
    public const OFFICE_OUTSIDE = 1;

    /**
     * 地点异常.
     */
    public const OFFICE_ABNORMAL = 2;

    public const SHIFT_CLASS = ['one', 'two', 'three', 'four'];

    /**
     * 所有缺卡
     */
    public const ALL_LACK_CARD = [self::LATE_LACK_CARD, self::EARLY_LACK_CARD, self::LACK_CARD];

    /**
     * 所有迟到.
     */
    public const ALL_LATE = [self::LATE, self::EXTREME_LATE];

    /**
     * 迟到早退
     */
    public const LATE_AND_LEAVE_EARLY = [self::LATE, self::EXTREME_LATE, self::LEAVE_EARLY];

    /**
     * 正常/早退/迟到
     */
    public const SAME_CLOCK = [self::NORMAL, self::LATE, self::EXTREME_LATE, self::LEAVE_EARLY];

    /**
     * 打卡状态文字.
     */
    public static function getStatusText(int $status): string
    {
        return match (true) {
            $status == self::NORMAL                => '正常',
            $status == self::LATE                  => '迟到',
            $status == self::EXTREME_LATE          => '严重迟到',
            $status == self::LEAVE_EARLY           => '早退',
            in_array($status, self::ALL_LACK_CARD) => '缺卡',
            default                                => '--'
        };
    }

    /**
     * 地点状态文字.
     */
    public static function getLocationStatusText(int $status): string
    {
        return match (true) {
            $status == self::OFFICE_OUTSIDE  => '外勤卡',
            $status == self::OFFICE_ABNORMAL => '地点异常',
            default                          => '--'
        };
    }
}
