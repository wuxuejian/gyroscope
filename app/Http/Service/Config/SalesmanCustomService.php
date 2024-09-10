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

namespace App\Http\Service\Config;

use App\Constants\CacheEnum;
use App\Constants\CustomEnum\ContractEnum;
use App\Constants\CustomEnum\CustomEnum;
use App\Constants\CustomEnum\CustomerEnum;
use App\Constants\CustomEnum\LiaisonEnum;
use App\Http\Dao\Config\SalesmanCustomDao;
use App\Http\Service\BaseService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Cache;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * 业务自定义数据
 * Class SalesmanCustomService.
 */
class SalesmanCustomService extends BaseService
{
    public function __construct(SalesmanCustomDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取用户自定义表单.
     */
    public function salesmanCustomFullField(int $customType): array
    {
        return match ($customType) {
            CustomerEnum::CUSTOMER_VIEWER => [
                array_merge(CustomerEnum::CUSTOMER_LIST_FIELD, CustomerEnum::CUSTOMER_VIEWER_LIST_FIELD),
                array_merge(CustomerEnum::CUSTOMER_SEARCH_FIELD, CustomerEnum::CUSTOMER_VIEWER_SEARCH_FIELD),
            ],
            CustomerEnum::CUSTOMER_CHARGE => [
                array_merge(CustomerEnum::CUSTOMER_LIST_FIELD, CustomerEnum::CUSTOMER_CHARGE_LIST_FIELD),
                array_merge(CustomerEnum::CUSTOMER_SEARCH_FIELD, CustomerEnum::CUSTOMER_CHARGE_SEARCH_FIELD),
            ],
            CustomerEnum::CUSTOMER_HEIGHT_SEAS => [
                array_merge(CustomerEnum::CUSTOMER_LIST_FIELD, CustomerEnum::CUSTOMER_HEIGHT_SEAS_LIST_FIELD),
                array_merge(CustomerEnum::CUSTOMER_SEARCH_FIELD, CustomerEnum::CUSTOMER_HEIGHT_SEAS_SEARCH_FIELD),
            ],
            CustomerEnum::CUSTOMER_COMPANY => [
                array_merge(CustomerEnum::CUSTOMER_LIST_FIELD, CustomerEnum::CUSTOMER_COMPANY_LIST_FIELD),
                array_merge(CustomerEnum::CUSTOMER_SEARCH_FIELD, CustomerEnum::CUSTOMER_COMPANY_SEARCH_FIELD),
            ],
            ContractEnum::CUSTOMER_VIEWER_CONTRACT,
            ContractEnum::CUSTOMER_CHARGE_CONTRACT,
            ContractEnum::CUSTOMER_HEIGHT_SEAS_CONTRACT,
            ContractEnum::CONTRACT_VIEWER => [
                array_merge(ContractEnum::CONTRACT_LIST_FIELD, ContractEnum::CONTRACT_VIEWER_LIST_FIELD),
                array_merge(ContractEnum::CONTRACT_SEARCH_FIELD, ContractEnum::CONTRACT_VIEWER_SEARCH_FIELD),
            ],
            ContractEnum::CONTRACT_CHARGE => [
                array_merge(ContractEnum::CONTRACT_LIST_FIELD, ContractEnum::CONTRACT_CHARGE_LIST_FIELD),
                array_merge(ContractEnum::CONTRACT_SEARCH_FIELD, ContractEnum::CONTRACT_CHARGE_SEARCH_FIELD),
            ],
            default => [[], []]
        };
    }

    /**
     * 获取业务列表默认数据.
     */
    public function getListDefaultFieldByCustomType(int $customType): array
    {
        return match ($customType) {
            CustomerEnum::CUSTOMER_CHARGE      => CustomerEnum::CUSTOMER_CHARGE_LIST_DEFAULT_FIELD,
            CustomerEnum::CUSTOMER_COMPANY     => CustomerEnum::CUSTOMER_COMPANY_LIST_DEFAULT_FIELD,
            CustomerEnum::CUSTOMER_VIEWER      => CustomerEnum::CUSTOMER_VIEWER_LIST_DEFAULT_FIELD,
            CustomerEnum::CUSTOMER_HEIGHT_SEAS => CustomerEnum::CUSTOMER_HEIGHT_SEAS_LIST_DEFAULT_FIELD,
            ContractEnum::CONTRACT_VIEWER      => ContractEnum::CONTRACT_VIEWER_LIST_DEFAULT_FIELD,
            ContractEnum::CONTRACT_CHARGE      => ContractEnum::CONTRACT_CHARGE_LIST_DEFAULT_FIELD,
            ContractEnum::CUSTOMER_VIEWER_CONTRACT,
            ContractEnum::CUSTOMER_CHARGE_CONTRACT,
            ContractEnum::CUSTOMER_HEIGHT_SEAS_CONTRACT,
            LiaisonEnum::CUSTOMER_VIEWER_LIAISON,
            LiaisonEnum::CUSTOMER_CHARGE_LIAISON,
            LiaisonEnum::CUSTOMER_HEIGHT_SEAS_LIAISON,
            LiaisonEnum::CUSTOMER_COMPANY_LIAISON,
            LiaisonEnum::LIAISON_VIEWER => LiaisonEnum::LIAISON_VIEWER_LIST_DEFAULT_FIELD,
            default                     => []
        };
    }

    /**
     * 获取业务搜索默认数据.
     */
    public function getSearchDefaultFieldByCustomType(int $customType): array
    {
        return match ($customType) {
            CustomerEnum::CUSTOMER_CHARGE,
            CustomerEnum::CUSTOMER_COMPANY,
            CustomerEnum::CUSTOMER_HEIGHT_SEAS,
            CustomerEnum::CUSTOMER_VIEWER => CustomerEnum::CUSTOMER_VIEWER_SEARCH_DEFAULT_FIELD,
            ContractEnum::CONTRACT_CHARGE,
            ContractEnum::CONTRACT_VIEWER => ContractEnum::CONTRACT_VIEWER_SEARCH_DEFAULT_FIELD,
            LiaisonEnum::LIAISON_VIEWER   => LiaisonEnum::LIAISON_VIEWER_LIST_DEFAULT_FIELD,
            default                       => []
        };
    }

    /**
     * 默认数据.
     */
    public function getDefaultField(int $customType, string $selectType): array
    {
        return match ($selectType) {
            CustomEnum::LIST_SELECT   => $this->getListDefaultFieldByCustomType($customType),
            CustomEnum::SEARCH_SELECT => $this->getSearchDefaultFieldByCustomType($customType),
            default                   => []
        };
    }

    /**
     * 业务字段.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function salesmanCustomField(int $uid, int $customType): array
    {
        return Cache::tags([CacheEnum::TAG_CUSTOMER])->remember($uid . '_' . $customType, (int) sys_config('system_cache_ttl', 3600), function () use ($uid, $customType) {
            // get custom form field by types
            $fields = app()->get(FormService::class)->getCustomDataByTypes(
                $this->getTypesByCustomerType($customType),
                ['key as field', 'key_name as name', 'type', 'input_type', 'dict_ident'],
                ['dictData' => fn ($q) => $q->whereNot('type_name', 'area_cascade')],
            );
            [$list, $search] = $this->salesmanCustomFullField($customType);
            $list            = array_merge($list, $this->filterFields($fields, ['file', 'oawangeditor']));
            $search          = array_merge($search, $this->filterFields($fields, ['images', 'file', 'oawangeditor'], ['contract_followed', 'customer_followed']));
            $listFieldKeys   = array_column($list, 'field');
            $searchFieldKeys = array_column($search, 'field');

            if (in_array($customType, [ContractEnum::CONTRACT_VIEWER, ContractEnum::CONTRACT_CHARGE])) {
                foreach ($search as $key => $item) {
                    if ($item['field'] == 'contract_customer') {
                        $search[$key] = ['field' => $item['field'], 'name' => $item['name'], 'input_type' => 'input'];
                    }
                }
            }
            $listSelect = $this->dao->value(['uid' => $uid, 'custom_type' => $customType . '_' . CustomEnum::LIST_SELECT], 'field_list');
            if (! $listSelect) {
                // get list select custom form field
                $listSelect = $this->getListDefaultFieldByCustomType($customType);
            }

            $listSelect   = $this->filterSelectData($listSelect, $listFieldKeys);
            $searchSelect = $this->filterSelectData($this->getSearchDefaultFieldByCustomType($customType), $searchFieldKeys);
            return ['list' => $list, 'search' => $search, 'list_select' => $listSelect, 'search_select' => $searchSelect];
        });
    }

    /**
     * 过滤选择数据.
     */
    public function filterSelectData(array $data, array $listFields): array
    {
        return array_values(array_filter($data, fn ($item) => in_array($item, $listFields)));
    }

    /**
     * 保存业务自定义字段.
     * @throws BindingResolutionException
     */
    public function saveSalesmanCustomField(int $uid, int $customType, string $selectType, array $data): mixed
    {
        if (! in_array($selectType, [CustomEnum::LIST_SELECT, CustomEnum::SEARCH_SELECT])) {
            throw $this->exception('业务类型错误');
        }
        $data = array_unique($data);
        if (count($data) < 3) {
            throw $this->exception('至少选中3个字段');
        }
        $res = $this->transaction(function () use ($uid, $customType, $selectType, $data) {
            $where = ['uid' => $uid, 'custom_type' => $customType . '_' . $selectType];
            if ($this->dao->exists(['uid' => $uid, 'custom_type' => $customType . '_' . $selectType])) {
                return $this->dao->update($where, ['field_list' => $data]);
            }
            return $this->dao->create(array_merge($where, ['field_list' => $data]));
        });
        return $res && Cache::tags([CacheEnum::TAG_CUSTOMER])->flush();
    }

    /**
     * 获取业务类型获取表单类型.
     */
    public function getTypesByCustomerType(int $customType): int
    {
        return match (true) {
            in_array($customType, CustomerEnum::CUSTOMER_TYPE) => CustomEnum::CUSTOMER,
            in_array($customType, ContractEnum::CONTRACT_TYPE) => CustomEnum::CONTRACT,
            in_array($customType, LiaisonEnum::LIAISON_TYPE)   => CustomEnum::LIAISON,
            default                                            => 0
        };
    }

    /**
     * 获取表单类型获取业务类型.
     */
    public function getCustomTypesByTypes(int $types): array
    {
        return match ($types) {
            CustomEnum::CUSTOMER => CustomerEnum::CUSTOMER_TYPE,
            CustomEnum::CONTRACT => ContractEnum::CONTRACT_TYPE,
            CustomEnum::LIAISON  => LiaisonEnum::LIAISON_TYPE,
            default              => []
        };
    }

    /**
     * 移除业务自定义数据.
     * @throws BindingResolutionException
     */
    public function forgetCustomTableField(int $types): void
    {
        $customTypes = [];
        foreach ($this->getCustomTypesByTypes($types) as $item) {
            $customTypes[] = $item . CustomEnum::LIST_SELECT;
            $customTypes[] = $item . CustomEnum::SEARCH_SELECT;
        }

        $customTypes && $this->dao->update(['custom_type' => $customTypes], ['field_list' => []]);
    }

    /**
     * 获取业务自定义数据.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getCustomField(int $uid, int $customType, string $selectType): array
    {
        if (! in_array($selectType, [CustomEnum::LIST_SELECT, CustomEnum::SEARCH_SELECT])) {
            throw $this->exception('业务类型错误');
        }

        $field = $this->dao->get(['uid' => $uid, 'custom_type' => $customType . '_' . CustomEnum::LIST_SELECT], ['field_list']);
        if ($field) {
            return $field->field_list;
        }

        return $this->getDefaultField($customType, $selectType);
    }

    /**
     * 过滤指定字段.
     */
    public function filterFields(array $fields, array $filterTypes = [], array $filterFields = []): array
    {
        return array_values(array_filter($fields, function ($value) use ($filterTypes, $filterFields) {
            $res1 = $res2 = true;
            if ($filterTypes) {
                $res1 = ! in_array(strtolower($value['input_type']), $filterTypes);
            }
            if ($filterFields) {
                $res2 = ! in_array(strtolower($value['field']), $filterFields);
            }
            return $res1 && $res2;
        }));
    }
}
