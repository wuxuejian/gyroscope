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

namespace App\Http\Dao\Client;

use App\Http\Dao\BaseDao;
use App\Http\Model\BaseModel;
use App\Http\Model\Client\ClientFollow;
use App\Http\Model\Schedule\Schedule;
use App\Http\Model\Schedule\ScheduleTask;
use crmeb\traits\dao\JoinSearchTrait;
use crmeb\traits\dao\ListSearchTrait;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HigherOrderWhenProxy;

/**
 * 客户追踪记录
 * Class ClientFollowDao.
 */
class ClientFollowDao extends BaseDao
{
    use ListSearchTrait;
    use JoinSearchTrait;

    /**
     * 待办关联查询.
     * @param mixed $where
     * @return BaseModel|HigherOrderWhenProxy|mixed
     * @throws BindingResolutionException
     */
    public function scheduleSearch($where): mixed
    {
        $this->aliasC = app()->get($this->setModelC())->getTable();
        return $this->getJoinModel('id', 'link_id')
            ->join($this->aliasC, $this->aliasB . '.id', '=', $this->aliasC . '.schedultid', 'left')
            ->where($this->getFiled('types'), $where['types'] ?? 1)
            ->where(function (Builder $query) use ($where) {
                $statusField = $this->getFiled('status', $this->aliasC);
                $query->where($statusField, $where['schedule_status'] ?? 0)->orWhereNull($statusField);
            })->when(isset($where['entid']), function ($query) use ($where) {
                $query->where($this->getFiled('entid', $this->aliasB), $where['entid']);
            });
    }

    /**
     * @return mixed|string
     */
    protected function setModel()
    {
        return ClientFollow::class;
    }

    protected function setModelB(): string
    {
        return Schedule::class;
    }

    protected function setModelC(): string
    {
        return ScheduleTask::class;
    }
}
