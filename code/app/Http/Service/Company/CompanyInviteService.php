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

namespace App\Http\Service\Company;

use App\Http\Dao\Company\CompanyInviteDao;
use App\Http\Service\BaseService;

/**
 * 企业邀请.
 */
class CompanyInviteService extends BaseService
{
    public function __construct(CompanyInviteDao $dao)
    {
        $this->dao = $dao;
    }

    public function getInfo(string $uniqued)
    {
        if (! $uniqued) {
            throw $this->exception('');
        }
    }
}
