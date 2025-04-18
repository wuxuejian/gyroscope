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

namespace App\Http\Dao\Crud;

use App\Http\Dao\BaseDao;
use App\Http\Model\Crud\SystemCrudEventLog;

/**
 * 触发器日志
 * Class SystemCrudEventLogDao.
 * @email 136327134@qq.com
 * @date 2024/3/14
 */
class SystemCrudEventLogDao extends BaseDao
{
    /**
     * @email 136327134@qq.com
     * @date 2024/3/14
     * @return string
     */
    protected function setModel()
    {
        return SystemCrudEventLog::class;
    }
}
