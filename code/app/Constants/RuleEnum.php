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

/**
 * 角色.
 */
final class RuleEnum
{
    /**
     * 创始人.
     */
    public const FOUNDER = 0;

    /**
     * 无企业用户.
     */
    public const INITIAL_USER = 1;

    /**
     * 初入企业用户.
     */
    public const INITIAL_COMPANY = 2;

    public const ROLE_TYPE = [
        self::FOUNDER         => '企业超级角色(创始人)',
        self::INITIAL_USER    => '初始角色(无企业)',
        self::INITIAL_COMPANY => '初始角色(有企业)',
    ];

    // 财务管理员
    public const FINANCE_TYPE = 'finance';

    // 人事管理
    public const PERSONNEL_TYPE = 'personnel';

    // 行政管理
    public const ADMINISTRATION_TYPE = 'administration';

    public const ENTERPRISE_TYPE = 'enterprise';

    public const USER_TYPE = 'user';

    /**
     * 数据范围：直属下级.
     */
    public const DATA_SUB = 5;

    /**
     * 数据范围：全部.
     */
    public const DATA_ALL = 4;

    /**
     * 数据范围：指定部门.
     */
    public const DATA_APPOINT = 3;

    /**
     * 数据范围：当前部门.
     */
    public const DATA_CURRENT = 2;

    /**
     * 数据范围：仅本人.
     */
    public const DATA_SELF = 1;
}
