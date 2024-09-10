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

use App\Http\Dao\Train\PromotionDataDao;
use App\Http\Service\BaseService;
use App\Http\Service\Position\PositionJobService;
use crmeb\interfaces\ResourceServicesInterface;
use crmeb\traits\service\ResourceServiceTrait;
use Illuminate\Contracts\Container\BindingResolutionException;

/**
 * 晋升数据
 * Class PromotionDataService.
 */
class PromotionDataService extends BaseService implements ResourceServicesInterface
{
    use ResourceServiceTrait;

    /**
     * PromotionDataService constructor.
     */
    public function __construct(PromotionDataDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 列表.
     * @param array $sort
     */
    public function getList(array $where = [], array $field = ['*'], $sort = ['sort'], array $with = []): array
    {
        $list               = $this->dao->getList($where, $field, 0, 0, $sort, $with);
        $positionJobService = app()->get(PositionJobService::class);
        foreach ($list as &$item) {
            $item['positions'] = $item['position'] ? $positionJobService->select(['id' => $item['position']], ['id', 'name']) : [];
        }

        return $list;
    }

    /**
     * 保存.
     * @throws BindingResolutionException
     */
    public function save(array $data): mixed
    {
        return $this->dao->create($data);
    }

    /**
     * 修改.
     * @throws BindingResolutionException
     */
    public function update(int $id, array $data): mixed
    {
        return $this->dao->update($id, $data);
    }

    /**
     * 排序.
     * @throws BindingResolutionException
     */
    public function sort(int $pid, array $data): bool
    {
        if (empty($data)) {
            throw $this->exception('参数错误');
        }

        return $this->transaction(function () use ($pid, $data) {
            $sort = range(count($data), 1);
            foreach ($data as $key => $datum) {
                $this->dao->update(['promotion_id' => $pid, 'id' => (int) $datum], ['sort' => $sort[$key] ?? 0]);
            }
            return true;
        });
    }

    public function resourceCreate(array $other = []): array
    {
        // TODO: Implement resourceCreate() method.
    }

    public function resourceEdit(int $id, array $other = []): array
    {
        // TODO: Implement resourceEdit() method.
    }
}
