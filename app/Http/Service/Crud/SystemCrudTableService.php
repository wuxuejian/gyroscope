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

namespace App\Http\Service\Crud;

use App\Http\Dao\Crud\SystemCrudTableDao;
use App\Http\Service\BaseService;

/**
 * 实体列表默认
 * Class SystemCrudTableService.
 * @email 136327134@qq.com
 * @date 2024/4/13
 * @mixin SystemCrudTableDao
 */
class SystemCrudTableService extends BaseService
{
    /**
     * SystemCrudTableService constructor.
     */
    public function __construct(SystemCrudTableDao $dao)
    {
        $this->dao = $dao;
    }
}
