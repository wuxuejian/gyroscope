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

namespace App\Http\Dao\Client;

use App\Http\Dao\BaseDao;
use App\Http\Model\Client\CustomerRecord;
use crmeb\traits\dao\BatchSearchTrait;
use crmeb\traits\dao\ListSearchTrait;

/**
 * class CustomerRecordDao.
 */
class CustomerRecordDao extends BaseDao
{
    use ListSearchTrait;
    use BatchSearchTrait;

    protected function setModel(): string
    {
        return CustomerRecord::class;
    }
}
