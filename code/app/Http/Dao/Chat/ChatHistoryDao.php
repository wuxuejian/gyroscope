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
use App\Http\Model\Chat\ChatHistory;
use crmeb\traits\dao\ListSearchTrait;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Class ChatHistoryDao.
 */
class ChatHistoryDao extends BaseDao
{
    use ListSearchTrait;

    /**
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function topUpModel(int $userId)
    {
        return $this->getModel()->where('user_id', $userId)->whereNotNull('top_up');
    }

    /**
     * @return string
     */
    protected function setModel()
    {
        return ChatHistory::class;
    }
}
