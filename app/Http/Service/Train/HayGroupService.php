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

namespace App\Http\Service\Train;

use App\Http\Contract\Company\HayGroupInterface;
use App\Http\Dao\Train\HayGroupDao;
use App\Http\Dao\Train\HayGroupDataDao;
use App\Http\Service\BaseService;
use Illuminate\Contracts\Container\BindingResolutionException;

/**
 * 评估数据
 * Class HayGroupService.
 */
class HayGroupService extends BaseService implements HayGroupInterface
{
    protected HayGroupDataDao $dataDao;

    /**
     * HayGroupService constructor.
     */
    public function __construct(HayGroupDao $dao, HayGroupDataDao $dataDao)
    {
        $this->dao     = $dao;
        $this->dataDao = $dataDao;
    }

    /**
     * 列表.
     * @param array $sort
     */
    public function getList(array $where = [], array $field = ['id', 'name', 'uid', 'created_at', 'updated_at'], $sort = 'created_at', array $with = ['positions', 'card']): array
    {
        return parent::getList($where, $field, $sort, $with);
    }

    /**
     * 保存.
     * @throws BindingResolutionException
     */
    public function save(array $data): mixed
    {
        $userId = uuid_to_uid($this->uuId(false));
        return $this->transaction(function () use ($userId, $data) {
            $group = $this->dao->create(['name' => $data['name'], 'uid' => $userId]);
            if (! $group) {
                throw $this->exception('保存失败');
            }
            foreach ($data['list'] as $item) {
                $this->dataDao->create(array_merge($item, ['uid' => $userId, 'group_id' => $group->id]));
            }
            return $group;
        });
    }

    /**
     * 修改.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function update(int $id, array $data): mixed
    {
        $userId = uuid_to_uid($this->uuId(false));
        $info   = $this->dao->get(['id' => $id, 'uid' => $userId]);
        if (! $info) {
            throw $this->exception(__('common.operation.noExists'));
        }

        if ($info->uid !== $userId) {
            throw $this->exception('没有权限操作');
        }

        return $this->transaction(function () use ($id, $userId, $data) {
            $this->dao->update(['id' => $id], ['name' => $data['name']]);
            $this->dataDao->delete(['group_id' => $id]);
            $sort = range(count($data), 1);
            foreach ($data['list'] as $key => $item) {
                unset($item['id'], $item['position'], $item['card']);
                $upData = ['sort' => $sort[$key] ?? 0, 'group_id' => $id, 'uid' => $userId];
                $this->dataDao->create(array_merge($item, $upData));
            }
            return true;
        });
    }

    /**
     * 删除.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function delete(int $id): bool
    {
        $userId = uuid_to_uid($this->uuId(false));
        return $this->transaction(function () use ($id, $userId) {
            return $this->dao->delete(['id' => $id, 'uid' => $userId])
                && $this->dataDao->delete(['group_id' => $id, 'uid' => $userId]);
        });
    }

    /**
     * 数据列表.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getDataList(int $id, array $field = ['*'], array $with = ['position', 'card']): array
    {
        $where = ['group_id' => $id, 'uid' => uuid_to_uid($this->uuId(false))];
        return $this->dataDao->setDefaultSort('created_at')->select($where, $field, $with)->toArray();
    }

    /**
     * 历史数据.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getHistoryList(int $id, array $field = ['*'], string $sort = 'created_at', array $with = ['position', 'card']): array
    {
        $where          = ['col1' => $id];
        [$page, $limit] = $this->getPageValue();
        $list           = $this->dataDao->getList($where, $field, $page, $limit, $sort, $with);
        $count          = $this->dataDao->count($where);
        return $this->listData($list, $count);
    }

    /**
     * 检测是否存在某职位.
     * @throws BindingResolutionException
     */
    public function checkPositionExist(int $positionId): bool
    {
        return $this->dataDao->exists(['col1' => $positionId]);
    }
}
