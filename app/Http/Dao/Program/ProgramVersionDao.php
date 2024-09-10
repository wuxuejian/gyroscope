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

namespace App\Http\Dao\Program;

use App\Http\Dao\BaseDao;
use App\Http\Model\Program\ProgramVersion;
use crmeb\interfaces\ResourceDaoInterface;
use crmeb\traits\dao\ListSearchTrait;

/**
 * 项目版本
 * Class ProgramVersionDao.
 */
class ProgramVersionDao extends BaseDao implements ResourceDaoInterface
{
    use ListSearchTrait;

    protected function setModel(): string
    {
        return ProgramVersion::class;
    }
}
