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

use App\Constants\CustomEnum\CustomerEnum;
use App\Http\Dao\BaseDao;
use App\Http\Dao\Schedule\ScheduleRemindDao;
use App\Http\Dao\Schedule\ScheduleTaskDao;
use App\Http\Model\BaseModel;
use App\Http\Model\Client\Customer;
use App\Http\Service\Client\ClientSubscribeService;
use App\Http\Service\Client\ContractService;
use App\Http\Service\Client\Custom\CommonService;
use App\Http\Service\Client\CustomerLiaisonService;
use crmeb\traits\dao\BatchSearchTrait;
use crmeb\traits\dao\JoinSearchTrait;
use crmeb\traits\dao\ListSearchTrait;
use crmeb\traits\dao\TogetherSearchTrait;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Concerns\BuildsQueries;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\HigherOrderWhenProxy;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * class CustomerDao.
 */
class CustomerDao extends BaseDao
{
    use CommonService;
    use ListSearchTrait;
    use TogetherSearchTrait;
    use BatchSearchTrait;
    use JoinSearchTrait;

    protected $otherSearch = [
        'statistics_type',
        'types',
        'scope_frame',
        'before_salesman',
        'customer_repeat_check',
        'subscribe_uid',
    ];

    /**
     * 搜索.
     *
     * @param array|int|string $where
     *
     * @return BaseModel|BuildsQueries|mixed
     * @throws \ReflectionException
     * @throws BindingResolutionException
     */
    public function search($where, ?bool $authWhere = null)
    {
        return parent::search($where, $authWhere);
    }

    /**
     * 待办关联查询.
     * @param mixed $where
     * @return BaseModel|HigherOrderWhenProxy|mixed
     * @throws BindingResolutionException
     */
    public function scheduleSearch($where): mixed
    {
        $remindDay    = Carbon::now(config('app.timezone'))->toDateString();
        $this->aliasC = app()->get($this->setModelC())->getTable();
        $this->aliasD = app()->get($this->setModelD())->getTable();
        return $this->getJoinModel('id', 'eid')
            ->join($this->aliasC, $this->aliasB . '.uniqued', '=', $this->aliasC . '.uniqued')
            ->join($this->aliasD, $this->aliasC . '.sid', '=', $this->aliasD . '.pid', 'left')
            ->where($this->getFiled('uid'), '<>', 0)
            ->where($this->getFiled('types', $this->aliasB), 1)
            ->whereDate($this->getFiled('time', $this->aliasB), '<', $remindDay)
            ->where(function ($query) use ($where) {
                $uidField = $this->getFiled('uid');
                if (isset($where['uid']) && $where['uid']) {
                    if (is_array($where['uid'])) {
                        $query->whereIn($uidField, $where['uid']);
                    } else {
                        $query->where($uidField, $where['uid']);
                    }
                }
            })
            ->where(function (Builder $query) {
                $query->where($this->getFiled('status', $this->aliasD), '<>', 3);
                $query->orWhereNull($this->getFiled('id', $this->aliasD));
            });
    }

    /**
     * 急需跟进统计.
     * @throws BindingResolutionException
     */
    public function getUrgentFollowUpCount(array $where): int
    {
        return $this->scheduleSearch($where)->select(['customer.id'])->distinct()->get()->map(function ($item) {
            return $item['id'];
        })->count();
    }

    /**
     * 列表筛选数据.
     * @param mixed $where
     * @param mixed $page
     * @param mixed $limit
     * @param mixed $with
     * @return Builder
     * @throws BindingResolutionException
     */
    public function listSearch($where, $page = 0, $limit = 0, $with = [])
    {
        $dao   = $this->getModel();
        $where = $this->getWhere($where, CustomerEnum::CUSTOMER);
        foreach ($where as $field => $value) {
            if ($value === '') {
                continue;
            }
            if (in_array($field, $this->otherSearch)) {
                switch ($field) {
                    case 'statistics_type':
                        switch ($value) {
                            case 'concern':
                                $ids = app()->get(ClientSubscribeService::class)->column(['uid' => $where['subscribe_uid'], 'subscribe_status' => 1], 'eid');
                                $dao = $dao->whereIn('id', $ids);
                                break;
                            case 'unsettled':
                                $dao = $dao->where('customer_status', 0);
                                break;
                            case 'traded':
                                $dao = $dao->where('customer_status', 1);
                                break;
                            case 'urgent_follow_up':
                                $ids = $this->scheduleSearch(['uid' => $where['uid']])->select(['customer.id'])->distinct()->get();
                                $dao = $dao->whereIn('id', $ids);
                                break;
                        }
                        // no break
                    case 'scope_frame':
                        break;
                    case 'types':
                        if ($value != 3) {
                            $dao = $dao->where('customer_status', '<', 2);
                        }
                        break;
                    case 'before_salesman':
                        $dao = $this->searchBeforeSalesman($dao, $value['value']);
                        break;
                    case 'customer_repeat_check':
                        $dao = $dao->where('customer_name', 'like', "%{$value}%");
                        break;
                }
                unset($where[$field]);
            } elseif (is_array($value)) {
                if (isset($value['input_type'])) {
                    $dao = match ($value['input_type']) {
                        'select' => $this->getMoreSelectSearch($dao, $field, $value['value'], $value['type']),
                        'checked', 'radio' => $this->getSelectSearch($dao, $field, $value['value']),
                        'input'     => $this->getInputSearch($dao, $field, $value['value']),
                        'date'      => $this->getDateSearch($dao, $field, $value['value']),
                        'personnel' => $this->getPersonnelSearch($dao, $field, $value['value']),
                        default     => $dao->where($field, $value['value']),
                    };
                } else {
                    $dao = $dao->whereIn($field, $value);
                }
            } else {
                $dao = $dao->where($field, $value);
            }
        }
        return $dao->when($page && $limit, function ($query) use ($page, $limit) {
            $query->forPage($page, $limit);
        })->when($sort = sort_mode('id'), function ($query) use ($sort) {
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
        })->with($with);
    }

    /**
     * 合同名称查询.
     * @param mixed $dao
     * @param mixed $value
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function searchContractName($dao, $value)
    {
        $ids = app()->get(ContractService::class)->column(['contract_name' => $value], 'eid');
        return $dao->whereIn('id', $ids);
    }

    /**
     * 业务员查询.
     * @param mixed $dao
     * @param mixed $value
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function searchSalesman($dao, $value)
    {
        return $dao->whereIn('uid', $value);
    }

    /**
     * 合同编号查询.
     * @param mixed $dao
     * @param mixed $value
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function searchContractNo($dao, $value)
    {
        $ids = app()->get(ContractService::class)->column(['contract_no' => $value], 'eid');
        return $dao->whereIn('id', $ids);
    }

    /**
     * 联系人电话查询.
     * @param mixed $dao
     * @param mixed $value
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function searchLiaisonTel($dao, $value)
    {
        $ids = app()->get(CustomerLiaisonService::class)->column(['liaison_tel' => $value], 'eid');
        return $dao->whereIn('id', $ids);
    }

    /**
     * 联系人电话查询.
     * @param mixed $dao
     * @param mixed $value
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function searchLiaison($dao, $value)
    {
        $ids = app()->get(CustomerLiaisonService::class)->column(['liaison_name' => $value], 'eid');
        return $dao->whereIn('id', $ids);
    }

    /**
     * 省市区查询.
     * @param mixed $dao
     * @param mixed $value
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function searchAreaCascade($dao, $value)
    {
        $data = [];
        foreach ($value as $item) {
            $item   = is_array($item) ? $item : json_decode($item, true);
            $data[] = (string) end($item);
        }
        return $dao->where(function ($query) use ($data) {
            foreach ($data as $item) {
                $query->orWhere('area_cascade', 'like', "%\"{$item}\"%");
            }
        });
    }

    /**
     * 客户标签查询.
     * @param mixed $dao
     * @param mixed $value
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function searchCustomerLabel($dao, $value)
    {
        return $dao->where(function ($q) use ($value) {
            foreach ($value as $v) {
                $q->orWhere('customer_label', 'like', "%\"{$v}\"%")
                    ->orWhere('customer_label', 'like', "[{$v},%")
                    ->orWhere('customer_label', 'like', "%,{$v},%")
                    ->orWhere('customer_label', 'like', "[{$v}]")
                    ->orWhere('customer_label', 'like', "%,{$v}]");
            }
        });
    }

    /**
     * 客户创建人查询.
     * @param mixed $dao
     * @param mixed $value
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function searchCreator($dao, $value)
    {
        return $dao->whereIn('creator_uid', $value);
    }

    /**
     * 客户标签查询.
     * @param mixed $dao
     * @param mixed $value
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function searchBeforeSalesman($dao, $value)
    {
        return $dao->whereIn('before_uid', $value);
    }

    /**
     * 急需跟进ID.
     * @throws BindingResolutionException
     */
    public function getUrgentFollowUpIds(array $where): array
    {
        return $this->scheduleSearch($where)->select(['customer.id'])->distinct()->get()->map(
            function ($item) {
                return $item['id'];
            }
        )->toArray();
    }

    /**
     * 跟进过期客户.
     * @throws BindingResolutionException
     */
    public function getFollowExpire(array $where): mixed
    {
        return $this->scheduleSearch($where)->select(['customer.id'])->distinct()->get()->map(function ($item) {
            return $item['id'];
        });
    }

    protected function setModel(): string
    {
        return Customer::class;
    }

    protected function setModelB(): string
    {
        return ClientFollowDao::class;
    }

    protected function setModelC(): string
    {
        return ScheduleRemindDao::class;
    }

    protected function setModelD(): string
    {
        return ScheduleTaskDao::class;
    }
}
