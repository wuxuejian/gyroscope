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
use App\Http\Model\BaseModel;
use App\Http\Model\Client\ClientBill;
use App\Http\Model\Client\Contract;
use App\Http\Model\Client\Customer;
use App\Http\Service\Client\ClientBillService;
use App\Http\Service\Client\ClientContractSubscribeService;
use App\Http\Service\Client\ClientRemindService;
use App\Http\Service\Client\Custom\CommonService;
use App\Http\Service\Client\CustomerService;
use App\Http\Service\Config\DictDataService;
use crmeb\traits\dao\ListSearchTrait;
use crmeb\traits\dao\TogetherSearchTrait;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\HigherOrderWhenProxy;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class ContractDao extends BaseDao
{
    use TogetherSearchTrait;
    use CommonService;
    use ListSearchTrait;

    protected string $joinTable;

    protected $otherSearch = [
        'statistics_type',
        'types',
        'scope_frame',
        'before_salesman',
        'customer_repeat_check',
        'subscribe_uid',
        'payment_time',
        'contract_customer',
    ];

    /**
     * @return BaseModel
     * @throws BindingResolutionException
     */
    public function clientContractJoinModel()
    {
        $model     = $this->getModel();
        $joinModel = app()->get(Customer::class);

        $joinTable = $this->joinTable = $joinModel->getTable();

        $table = $this->table = $model->getTable();

        return $model->join($joinTable, $table . '.eid', '=', $joinTable . '.id');
    }

    /**
     * 获取合同条数.
     * @throws BindingResolutionException
     */
    public function getClientContractCount(array $where = []): int
    {
        return $this->clientContractJoinModel()->where($where)->count();
    }

    /**
     * 获取合同列表.
     * @throws BindingResolutionException
     */
    public function getClientContractList(array $where = [], int $page = 0, int $limit = 0): array
    {
        $model     = $this->clientContractJoinModel();
        $table     = $this->table;
        $joinTable = $this->joinTable;
        return $model->where($where)
            ->whereNotNull($table . '.end_date')
            ->groupBy($table . '.id')
            ->select([
                $table . '.contract_name as title',
                $table . '.contract_price as price',
                $table . '.start_date',
                $table . '.end_date',
                $table . '.uid',
                $table . '.id',
                $joinTable . '.customer_name as name',
            ])
            ->with([
                'card' => fn ($qq) => $qq->where('user_enterprise.entid', $where['entid']),
            ])
            ->when($page && $limit, fn ($q) => $q->forPage($page, $limit))
            ->get()->toArray();
    }

    /**
     * 合同类型分析.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getRankByCategory(string $time, array $userIds, array $categoryId, array $types = [0, 1]): array
    {
        $prefix = Config::get('database.connections.mysql.prefix');

        $model = $this->getModel(false);
        $table = $model->getTable();

        $joinModel = app()->get(ClientBill::class);
        $joinTable = $joinModel->getTable();

        $preTable     = $prefix . $table;
        $preJoinTable = $prefix . $joinTable;
        return $model->join($joinTable, function ($join) use ($joinTable, $table, $time, $types) {
            $join->on($joinTable . '.cid', '=', $table . '.id')->whereIn($joinTable . '.types', $types)
                ->where($joinTable . '.status', 1)->where($joinTable . '.entid', 1)
                ->when($time, function ($query) use ($time, $joinTable) {
                    $query->whereBetween($joinTable . '.date', explode('-', $time));
                });
        })->when($categoryId, function ($query) use ($table, $categoryId) {
            is_array($categoryId) ? $query->whereIn($table . '.contract_category', $categoryId) : $query->where($table . '.contract_category', $categoryId);
        })->whereIn($joinTable . '.uid', $userIds)->whereIn($joinTable . '.uid', $userIds)
            ->selectRaw("`{$preTable}`.`contract_category`, sum(`{$preJoinTable}`.`num`) as `price`")->first()->toArray();
    }

    /**
     * 插入数据.
     * @throws BindingResolutionException
     */
    public function insert(array $data): bool
    {
        return $this->getModel(false)->insert($data);
    }

    /**
     * 搜索.
     * @param mixed $where
     * @return BaseModel|HigherOrderWhenProxy
     * @throws \ReflectionException
     * @throws BindingResolutionException
     */
    public function search($where, ?bool $authWhere = null)
    {
        $title = $where['title'] ?? '';
        $renew = $where['renew'] ?? '';
        $ids   = $where['ids'] ?? [];
        if (isset($where['title'])) {
            unset($where['title']);
        }
        if (isset($where['type'])) {
            unset($where['type']);
        }
        if ($renew == 3) {
            unset($where['renew']);
            if (isset($where['ids'])) {
                unset($where['ids']);
            }
        }
        return parent::search($where, $authWhere)
            ->when($renew == 3, function ($query) use ($ids) {
                $query->where(function ($query) use ($ids) {
                    $query->where('renew', 0)->orWhere(function ($query) use ($ids) {
                        $query->where('renew', 1)->whereIn('id', $ids);
                    });
                });
            })->where(function ($query) use ($title) {
                $query->when($title, function ($query) use ($title) {
                    $query->orWhere('title', 'like', '%' . $title . '%')
                        ->orWhere('contract_no', 'like', '%' . $title . '%')
                        ->orWhere(function ($query) use ($title) {
                            $query->whereIn('eid', function ($query) use ($title) {
                                $query->from('client_list')->select(['id'])->where('name', 'like', '%' . $title . '%');
                            });
                        });
                });
            });
    }

    /**
     * 合同类型数量.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getCountByCategory(string $time, array $userIds, array|int $categoryId = 0): int
    {
        $model = $this->getModel(false);
        $table = $model->getTable();

        $preTable  = Config::get('database.connections.mysql.prefix') . $table;
        $joinTable = app()->get(ClientBill::class)->getTable();
        return $model->join($joinTable, function ($join) use ($joinTable, $table, $time) {
            $join->on($joinTable . '.cid', '=', $table . '.id')
                ->where($joinTable . '.entid', 1)->where($joinTable . '.status', 1)->where($joinTable . '.types', 0)
                ->when($time, function ($query) use ($time, $joinTable) {
                    $query->whereBetween($joinTable . '.date', explode('-', $time));
                });
        })->whereIn($joinTable . '.uid', $userIds)->when($categoryId, function ($query) use ($table, $categoryId) {
            is_array($categoryId) ? $query->whereIn($table . '.contract_category', $categoryId) : $query->where($table . '.contract_category', $categoryId);
        })->selectRaw("count(DISTINCT `{$preTable}`.`id`) as `count`")->first()?->count ?? 0;
    }

    /**
     * 列表筛选数据.
     * @param mixed $where
     * @param mixed $page
     * @param mixed $limit
     * @param mixed $with
     * @throws BindingResolutionException
     */
    public function listSearch($where, $page = 0, $limit = 0, $with = [])
    {
        $dao   = $this->getModel();
        $where = $this->getWhere($where, CustomerEnum::CONTRACT);
        foreach ($where as $field => $value) {
            if ($value === '') {
                continue;
            }
            if (in_array($field, $this->otherSearch)) {
                switch ($field) {
                    case 'statistics_type':
                        switch ($value) {
                            case 'concern':
                                $cids = app()->get(ClientContractSubscribeService::class)->column(['uid' => $where['subscribe_uid'], 'subscribe_status' => 1], 'cid');
                                $dao  = $dao->whereIn('id', $cids);
                                break;
                            case 'not_signed':
                                $dao = $dao->where('signing_status', 0);
                                break;
                            case 'signed':
                                $dao = $dao->where('signing_status', 1);
                                break;
                            case 'void_signed':
                                $dao = $dao->where('signing_status', 2);
                                break;
                            case 'expired':
                                $dao = $dao->where('signing_status', '<', 2)->whereDate('end_date', '<', now()->toDateString())->where('is_abnormal', 0)->whereNotNull('end_date');
                                break;
                            case 'urgent_renewal':
                                $cids = app()->get(ClientRemindService::class)->getUrgentRenewalCid();
                                $dao  = $dao->whereIn('id', $cids)->where('signing_status', '<', 2);
                                break;
                            case 'cost_expired':
                                $cids = app()->get(ClientRemindService::class)->getUrgentRenewalCid(true);
                                $dao  = $dao->whereIn('id', $cids)->where('signing_status', '<', 2);
                                break;
                        }
                        break;
                    case 'payment_time':
                        $cids = app()->get(ClientBillService::class)->column(['status' => 1, 'types' => -1, 'date' => $value['value']], 'cid');
                        $dao  = $dao->whereIn('id', $cids)->where('signing_status', '<', 2);
                        break;
                    case 'scope_frame':
                        break;
                    case 'contract_customer':
                        $ids = app()->get(CustomerService::class)->column(['name_like' => $value], 'id');
                        $dao = $dao->whereIn('eid', $ids);
                        break;
                }
                unset($where[$field]);
            } elseif ($field == 'contract_category') {
                $value['value'] && $dao = $dao->whereIn('contract_category', $value['value']);
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
     * 合同客户查询.
     * @param mixed $dao
     * @param mixed $value
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function searchContractCustomer($dao, $value)
    {
        $ids = app()->get(CustomerService::class)->column(['name_like' => $value], 'id');
        return $dao->whereIn('eid', $ids);
    }

    protected function setModel(): string
    {
        return Contract::class;
    }

    /**
     * 付款单号查询.
     * @param mixed $dao
     * @param mixed $value
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function searchBillNo($dao, $value): mixed
    {
        $ids = app()->get(ClientBillService::class)->column(['bill_no_like' => $value], 'cid');
        return $dao->whereIn('id', $ids);
    }

    /**
     * 合同分类查询.
     * @param mixed $dao
     * @param mixed $value
     */
    private function searchContractCategory($dao, $value): mixed
    {
        $cate = app()->get(DictDataService::class)->getCompleteData('contract_type', $value);
        return $dao->where('contract_category', json_encode($cate));
    }
}
