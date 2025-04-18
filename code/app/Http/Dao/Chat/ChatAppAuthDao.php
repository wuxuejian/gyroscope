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

namespace App\Http\Dao\Chat;

use App\Http\Dao\BaseDao;
use App\Http\Model\Chat\ChatAppAuth;
use crmeb\traits\dao\BatchSearchTrait;
use crmeb\traits\dao\ListSearchTrait;

/**
 * ai模型
 * Class ChatModelsDao.
 */
class ChatAppAuthDao extends BaseDao
{
    use ListSearchTrait;
    use BatchSearchTrait;

    protected function setModel(): string
    {
        return ChatAppAuth::class;
    }
}
