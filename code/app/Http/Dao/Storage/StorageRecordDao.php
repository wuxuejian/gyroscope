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

namespace App\Http\Dao\Storage;

use App\Http\Dao\BaseDao;
use App\Http\Model\Storage\StorageRecord;
use crmeb\traits\dao\ListSearchTrait;
use crmeb\traits\dao\TogetherSearchTrait;

/**
 * 物资记录Dao.
 * Class StorageDao.
 * @method float sum(array $where, string $field, bool $search = false) 求和
 */
class StorageRecordDao extends BaseDao
{
    use ListSearchTrait;
    use TogetherSearchTrait;

    protected function setModel()
    {
        return StorageRecord::class;
    }
}
