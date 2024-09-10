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

namespace App\Http\Dao\Finance;

use App\Http\Dao\BaseDao;
use App\Http\Model\Finance\Bill;
use App\Http\Service\Finance\BillCategoryService;
use Carbon\Carbon;
use crmeb\interfaces\ResourceDaoInterface;
use crmeb\traits\dao\BatchSearchTrait;
use crmeb\traits\dao\ListSearchTrait;
use crmeb\traits\dao\TogetherSearchTrait;
use crmeb\utils\Statistics;
use Illuminate\Contracts\Container\BindingResolutionException;

/**
 * 资金流水记录
 * Class BillDao.
 */
class BillDao extends BaseDao implements ResourceDaoInterface
{
    use ListSearchTrait;
    use TogetherSearchTrait;
    use BatchSearchTrait;

    /**
     * 基础查询列表(通用).
     *
     * @param array $where 条件
     * @param array|string[] $field 显示字段
     * @param int $page 页码
     * @param int $limit 展示条数
     * @param null|array|string $sort 排序
     * @param array $with 关联
     *
     * @return array
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getList(array $where, array $field = ['*'], int $page = 0, int $limit = 0, $sort = null, array $with = [])
    {
        return $this->setTimeField('edit_time')->search($where)->select($field)->when($page && $limit, function ($query) use ($page, $limit) {
            $query->forPage($page, $limit);
        })->when($limit, function ($query) use ($limit) {
            $query->limit($limit);
        })->when($sort, function ($query) use ($sort) {
            if (is_array($sort)) {
                foreach ($sort as $k => $v) {
                    if (is_numeric($k)) {
                        $query->orderByDesc($v);
                    } else {
                        $query->orderBy($k, $v);
                    }
                }
            } else {
                $query->orderByDesc($sort);
            }
        })->with($with)->get()->toArray();
    }

    /**
     * 资金记录时间查询.
     *
     * @param mixed $entid
     * @param mixed $cateId
     *
     * @return array
     * @throws BindingResolutionException
     */
    public function getTrend($time, $timeType, $str, $types, $entid, $cateId = [])
    {
        return $this->getModel()->where(function ($query) use ($time, $types) {
            if ($time[0] == $time[1]) {
                $query->whereDate('edit_time', $time[0]);
            } else {
                $query->whereBetween('edit_time', [Carbon::make($time[0])->toDateTimeString(), Carbon::make($time[1])->endOfDay()->toDateTimeString()]);
            }
            $query->where('types', $types);
        })->when($cateId, function ($q) use ($cateId) {
            $q->whereIn('cate_id', $cateId);
        })->where('entid', $entid)->selectRaw("DATE_FORMAT(edit_time,'{$timeType}') as days,{$str} as num")
            ->groupBy('days')->get()->toArray();
    }

    /**
     * 资金记录排行.
     * @return array
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getBillRank($time, $types, $entid, $level = 0, $sum = 0, int $cateId = 0, array $cateSearch = [])
    {
        $cateService = app()->get(BillCategoryService::class);
        $where       = ['types' => $types];
        if ($cateId || ! $level) {
            $where['pid'] = $cateId;
        } else {
            $where['level'] = $level;
        }
        $cateIds = $cateService->getSelfAndChild($where);

        $list = [];
        foreach ($cateIds as $v) {
            if ($cateSearch && ! in_array($v['id'], $cateSearch)) {
                continue;
            }
            $num = $this->getModel()->where(function ($query) use ($time) {
                if ($time[0] == $time[1]) {
                    $query->whereDate('edit_time', $time[0]);
                } else {
                    $query->whereBetween('edit_time', [Carbon::make($time[0])->toDateTimeString(), Carbon::make($time[1])->endOfDay()->toDateTimeString()]);
                }
            })->when($entid, function ($query) use ($entid) {
                $query->where('entid', $entid);
            })->when($v['id'], function ($query) use ($cateService, $cateId, $v, $cateSearch) {
                if ($cateId && $cateId == $v['id']) {
                    $query->where('cate_id', $cateId);
                } else {
                    $ids = $cateSearch ? array_intersect($cateSearch, $cateService->getSubCateIdByCache($v['id'], true)) : $cateService->getSubCateIdByCache($v['id'], true);
                    $query->whereIn('cate_id', $ids);
                }
            })->where('types', $types)->sum('num');
            if ($num) {
                $list[] = [
                    'cate_id' => $v['id'],
                    'name'    => $v['name'],
                    'sum'     => $num,
                ];
            }
            unset($num);
        }
        if ($list) {
            $list  = Statistics::calcRatio($list, 'sum', 'cate_id', 'ratio', (float) $sum);
            $ratio = array_column($list, 'ratio');
            array_multisort($ratio, SORT_DESC, $list);
        }
        return $list;
    }

    /**
     * 资金记录合计
     *
     * @return int|mixed
     * @throws BindingResolutionException
     */
    public function getSum($time, $types, $level = 0, int $entId, int $cateId = 0)
    {
        $cateService = app()->get(BillCategoryService::class);
        return $this->getModel()->where(function ($query) use ($time) {
            if ($time[0] == $time[1]) {
                $query->whereDate('edit_time', $time[0]);
            } else {
                $query->whereBetween('edit_time', [Carbon::make($time[0])->toDateTimeString(), Carbon::make($time[1])->endOfDay()->toDateTimeString()]);
            }
        })->when($entId, function ($query) use ($entId) {
            $query->where('entid', $entId);
        })->when($level, function ($query) use ($level, $cateService) {
            $query->whereIn('cate_id', $cateService->column(['gt_level' => $level], 'id'));
        })->when($cateId || ! $level, function ($query) use ($cateId, $cateService) {
            $query->whereIn('cate_id', $cateService->getSubCateIdByCache($cateId, true));
        })->where('types', $types)->sum('num');
    }

    /**
     * 搜索.
     *
     * @param array|int|string $where
     *
     * @return BaseModel|BuildsQueries|mixed
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function search($where, ?bool $authWhere = null)
    {
        $name  = $where['name_like'] ?? '';
        $uid   = $where['uid_like'] ?? [];
        $entId = $where['entid'] ?? 0;
        if (isset($where['name_like'])) {
            unset($where['name_like']);
        }
        if (isset($where['uid_like'])) {
            unset($where['uid_like']);
        }
        return parent::search($where, $authWhere)->where(function ($query) use ($name, $uid, $entId) {
            $query->when($name, function ($query) use ($name, $uid, $entId) {
                $query->orWhere('num', 'like', '%' . $name . '%')->orWhere('mark', 'like', '%' . $name . '%')
                    ->orWhereIn('uid', $uid)->orWhereIn('cate_id', function ($query) use ($name, $entId) {
                        $query->from('bill_category')->select(['id'])
                            ->where('entid', $entId)->where('name', 'like', '%' . $name . '%');
                    });
            });
        });
    }

    /**
     * @return mixed|string
     */
    protected function setModel()
    {
        return Bill::class;
    }
}
