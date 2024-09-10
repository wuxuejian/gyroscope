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

namespace App\Http\Dao\Company;

use App\Http\Dao\BaseDao;
use App\Http\Model\Company\UserJobAnalysis;
use crmeb\traits\dao\ListSearchTrait;

/**
 * 企业用户工作分析
 * Class CompanyUserJobAnalysisDao.
 */
class CompanyUserJobAnalysisDao extends BaseDao
{
    use ListSearchTrait;

    /**
     * 设置模型.
     *
     * @return string
     */
    protected function setModel()
    {
        return UserJobAnalysis::class;
    }
}
