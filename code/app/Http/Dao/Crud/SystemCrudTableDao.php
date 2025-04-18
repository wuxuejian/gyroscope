<?php

/**
 *  +----------------------------------------------------------------------
 *  | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
 *  +----------------------------------------------------------------------
 *  | Copyright (c) 2016~2024 https://www.crmeb.com All rights reserved.
 *  +----------------------------------------------------------------------
 *  | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
 *  +----------------------------------------------------------------------
 *  | Author: CRMEB Team <admin@crmeb.com>
 *  +----------------------------------------------------------------------.
 */

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

namespace App\Http\Dao\Crud;

use App\Http\Dao\BaseDao;
use App\Http\Model\Crud\SystemCrudTable;
use crmeb\traits\dao\BatchSearchTrait;
use crmeb\traits\dao\TogetherSearchTrait;

/**
 * Class SystemCrudTableDao.
 * @email 136327134@qq.com
 * @date 2024/2/23
 */
class SystemCrudTableDao extends BaseDao
{
    use BatchSearchTrait;
    use TogetherSearchTrait;

    /**
     * @return string
     * @email 136327134@qq.com
     * @date 2024/2/23
     */
    protected function setModel()
    {
        return SystemCrudTable::class;
    }
}
