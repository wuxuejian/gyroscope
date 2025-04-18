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

namespace App\Http\Dao\Access;

use App\Http\Dao\BaseDao;
use App\Http\Model\Assess\AssessTargetCategory;
use crmeb\interfaces\ResourceDaoInterface;
use crmeb\traits\dao\ListSearchTrait;

/**
 * 指标、指标模板分类
 * Class AssessTargetCategoryDao.
 */
class AssessTargetCategoryDao extends BaseDao implements ResourceDaoInterface
{
    use ListSearchTrait;

    /**
     * @return mixed|string
     */
    protected function setModel()
    {
        return AssessTargetCategory::class;
    }
}
