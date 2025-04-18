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
use App\Http\Model\Chat\ChatModels;
use crmeb\traits\dao\ListSearchTrait;

/**
 * ai模型
 * Class ChatModelsDao.
 */
class ChatModelsDao extends BaseDao
{
    use ListSearchTrait;

    public function search($where, ?bool $authWhere = null)
    {
        if (isset($where['name'])) {
            $name = $where['name'];
            unset($where['name']);
        }
        if (isset($where['uids'])) {
            $uids = $where['uids'];
            unset($where['uids']);
        }
        return parent::search($where, $authWhere)
            ->when(isset($name) && $name !== '', fn ($q) => $q->where('name', 'like', '%' . $name . '%'))
            ->when(isset($uids) && $uids !== '', fn ($q) => $q->whereIn('uid', $uids));
    }

    protected function setModel(): string
    {
        return ChatModels::class;
    }
}
