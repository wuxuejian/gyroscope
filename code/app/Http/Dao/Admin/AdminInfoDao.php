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

namespace App\Http\Dao\Admin;

use App\Http\Dao\BaseDao;
use App\Http\Model\Admin\Admin;
use App\Http\Model\Admin\AdminInfo;
use App\Http\Model\BaseModel;
use crmeb\interfaces\ResourceDaoInterface;
use crmeb\traits\dao\JoinSearchTrait;
use crmeb\traits\dao\ListSearchTrait;
use crmeb\traits\dao\TogetherSearchTrait;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Traits\Conditionable;

class AdminInfoDao extends BaseDao implements ResourceDaoInterface
{
    use ListSearchTrait;
    use TogetherSearchTrait;
    use ListSearchTrait;
    use JoinSearchTrait;

    /**
     * 设置模型.
     *
     * @return BaseModel
     * @throws BindingResolutionException
     */
    public function getModel(bool $need = true)
    {
        if ($need) {
            return $this->getJoinModel('uid', 'uid', '=', 'left');
        }
        return parent::getModel($need);
    }

    /**
     * 插入数据.
     * @return bool
     * @throws BindingResolutionException
     */
    public function insert(array $data)
    {
        return $this->getModel(false)->insert($data);
    }

    /**
     * 搜索.
     * @param array|int|string $where
     * @return BaseModel|Conditionable|mixed
     * @throws \ReflectionException
     * @throws BindingResolutionException
     */
    public function search($where, ?bool $authWhere = null)
    {
        $frameIds = [];
        $name     = '';
        $entid    = 0;
        if (is_array($where)) {
            $frameIds = $where['frame_id'] ?? [];
            $name     = $where['name'] ?? '';
            $entid    = $where['entid'] ?? 0;
            unset($where['frame_id'], $where['name']);
        }
        return parent::search($where, $authWhere)
            ->when($frameIds, fn ($q) => $q->whereIn('id', fn ($query) => $query->from('frame_assist')
                ->whereIn('frame_id', $frameIds)
                ->select(['user_id'])))
            ->when($name !== '', fn ($q) => $q->whereIn('uid', fn ($query) => $query->from('enterprise_user_card')
                ->where('name', 'like', '%' . $name . '%')
                ->where('entid', $entid)
                ->select(['uid'])));
    }

    /**
     * 获取用户和企业关联数据.
     * @return BaseModel[]|Collection
     * @throws BindingResolutionException
     */
    public function userEnt(array $userIds, callable $callback)
    {
        return $this->getModel(false)->whereIn('id', $userIds)->select(['roles', 'id'])->get()->each($callback);
    }

    /**
     * 更新用户和企业关联数据.
     * @return int
     * @throws BindingResolutionException
     */
    public function updateUserEnterprise(array $ids, array $data)
    {
        return $this->getModel(false)->whereIn('id', $ids)->update($data);
    }

    public function validEntUser(array $uid, ?int $entId = null)
    {
        return $this->getModel(false)->where('verify', 1)->where('status', 1)->when($entId, function ($query) use ($entId) {
            $query->where('entid', $entId);
        })->whereIn('uid', $uid)->count();
    }

    /**
     * @param mixed $where
     * @return BaseModel|Conditionable|mixed
     * @throws \ReflectionException
     * @throws BindingResolutionException
     */
    public function selectModel($where, array $field = [], array $with = [])
    {
        return $this->search($where)->when(count($with), function ($query) use ($with) {
            $query->with($with);
        })->when($this->defaultSort, function ($query) {
            if (is_array($this->defaultSort)) {
                foreach ($this->defaultSort as $k => $v) {
                    if (is_numeric($k)) {
                        $query->orderByDesc($v);
                    } else {
                        $query->orderBy($k, $v);
                    }
                }
            } else {
                $query->orderByDesc($this->defaultSort);
            }
        })->select($field ?: '*');
    }

    /**
     * 获取绩效成员列表模型.
     * @return BaseModel|Conditionable|mixed
     * @throws BindingResolutionException
     */
    public function getAssessPlanModel(array $where, int $entid)
    {
        return $this->getModel(false)
            ->join('enterprise_user_card', 'enterprise_user_card.id', '=', 'user_enterprise.card_id')
            ->when(isset($where['ids']), fn ($q) => $q->whereIn('user_enterprise.id', $where['ids']))
            ->when($entid, fn ($q) => $q->where('user_enterprise.entid', $entid))
            ->where('user_enterprise.status', 1)
            ->when(isset($where['notid']), fn ($q) => $q->whereNotIn('user_enterprise.id', $where['notid']))
            ->when(isset($where['frame_id']) && $where['frame_id'], fn ($q) => $q->whereIn(
                'user_enterprise.id',
                fn ($query) => $query->from('frame_assist')
                    ->whereIn('frame_id', $where['frame_id'])
                    ->select(['user_id'])
            ))->when(isset($where['name']) && $where['name'] !== '', fn ($q) => $q->where('enterprise_user_card.name', 'like', '%' . $where['name'] . '%'));
    }

    protected function setModelB(): string
    {
        return Admin::class;
    }

    /**
     * @return mixed|string
     */
    protected function setModel()
    {
        return AdminInfo::class;
    }
}
