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
 * 用户.
 */
final class UserEnum extends Enum
{
    /**
     * 用户状态：未激活.
     */
    public const USER_NOT_ACTIVE = 0;

    /**
     * 用户状态：正常.
     */
    public const USER_NORMAL = 1;

    /**
     * 用户状态：锁定.
     */
    public const USER_LOCKING = 2;

    public const SINGLE_LOGIN = true;

    public const MULTIPORT_LOGIN = false;

    /**
     * 用户状态：待审核.
     */
    private const USER_STATUS_EXAMINE = 0;

    /**
     * 用户性别：男.
     */
    private const USER_GENDER_MALE = 1;

    /**
     * 用户性别：女.
     */
    private const USER_GENDER_FEMALE = 2;

    /**
     * 用户性别：未知.
     */
    private const USER_GENDER_UNKNOWN = 0;
}
