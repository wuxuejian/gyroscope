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
use App\Http\Model\Chat\ChatApplications;
use crmeb\traits\dao\JoinSearchTrait;
use crmeb\traits\dao\ListSearchTrait;
use Illuminate\Contracts\Container\BindingResolutionException;

/**
 * ai模型
 * Class ChatModelsDao.
 */
class ChatApplicationsDao extends BaseDao
{
    use ListSearchTrait;
    use JoinSearchTrait;

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

    /**
     * 获取当前用户的应用列表.
     * @return mixed
     * @throws BindingResolutionException
     */
    public function getUserAppList(int $uid)
    {
        return $this->getJoinModel('id', 'app_id', '=', 'left')->where($this->getFiled('status'), 1)
            ->when(fn ($q) => $q->where(fn ($qy) => $qy->where($this->getFiled('user_id', $this->aliasB), $uid)->orWhere($this->getFiled('uid'), $uid)))
            ->select($this->getFileds(['name', 'id', 'info', 'pic', 'prologue_text', 'prologue_list', 'status']))
            ->groupBy($this->getFiled('id'))->orderBy($this->getFiled('sort'), 'desc')->get()->toArray();
    }

    protected function setModel(): string
    {
        return ChatApplications::class;
    }

    protected function setModelB(): string
    {
        return ChatAppAuth::class;
    }
}
