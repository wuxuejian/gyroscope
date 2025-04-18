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

final class CompanyEnum extends Enum
{
    /**
     * 企业状态：正常.
     */
    private const COMPANY_STATUS_NORMAL = 1;

    /**
     * 企业状态：待审核.
     */
    private const COMPANY_STATUS_EXAMINE = 0;

    /**
     * 企业状态：锁定.
     */
    private const COMPANY_STATUS_LOCKING = -1;
}
