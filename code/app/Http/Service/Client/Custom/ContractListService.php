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

namespace App\Http\Service\Client\Custom;

use App\Constants\CustomEnum\ContractEnum;
use App\Constants\CustomEnum\CustomerEnum;
use App\Http\Service\Attach\AttachService;
use App\Http\Service\Client\ClientBillService;
use App\Http\Service\Client\ClientContractSubscribeService;
use App\Http\Service\Config\FormService;
use App\Http\Service\Config\SalesmanCustomService;
use App\Http\Service\System\RolesService;
use crmeb\traits\service\ResourceServiceTrait;
use Illuminate\Contracts\Container\BindingResolutionException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * 合同列表.
 */
trait ContractListService
{
    use ResourceServiceTrait;
    use CommonService;

    /**
     * 字段过滤.
     * @return string[]
     */
    public function dictFilterField(): array
    {
        return [];
    }

    public function followUpField(): string
    {
        return 'contract_followed';
    }

    /**
     * 关注状态
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function getSubscribeStatus(int $uid, array $ids): array
    {
        return app()->get(ClientContractSubscribeService::class)->getSubscribeStatusWithCid($uid, $ids);
    }

    /**
     * 获取列表.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function getListByType(array $where, int $uid, bool $isExport = false): array
    {
        $customType             = (int) $where['types'];
        $where['subscribe_uid'] = $uid;
        if ($isExport) {
            $localField   = ['id', 'eid', 'uid', 'contract_no'];
            $fields       = app()->get(FormService::class)->getExportField(CustomerEnum::CONTRACT);
            $intersects   = ['contract_customer', 'salesman', 'contract_followed'];
            $customFields = $field = array_diff(array_merge($localField, array_column($fields, 'key')), $intersects);
        } else {
            $dataField    = ['key', 'key_name', 'input_type', 'type', 'dict_ident'];
            $fields       = app()->get(FormService::class)->getCustomDataByTypes(ContractEnum::CONTRACT, $dataField);
            $customFields = app()->get(SalesmanCustomService::class)->getCustomField($uid, $customType, ContractEnum::LIST_SELECT);
            $tmpFields    = ['payment_status', 'salesman', 'creator', 'payment_time', 'bill_no', 'contract_customer'];
            $localField   = ['id', 'eid', 'uid', 'creator_uid', 'contract_name', 'surplus', 'contract_no', 'created_at', 'updated_at'];
            $field        = array_merge($localField, array_diff(array_intersect(array_column($fields, 'key'), $customFields), $tmpFields));
            $intersects   = array_diff(array_merge($field, array_intersect($customFields, $tmpFields)), $localField);
            if ($index = array_search('contract_followed', $field)) {
                unset($field[$index]);
            }

            // 客户详情列表
            if ($customType > ContractEnum::CONTRACT_CHARGE) {
                if (! in_array('contract_status', $field)) {
                    $field[] = 'contract_status';
                }
                if (! in_array('contract_price', $field)) {
                    $field[] = 'contract_price';
                }
                if (! in_array('creator', $intersects)) {
                    $intersects[] = 'creator';
                }
            }
        }

        $dictField  = $this->getDictField($fields);
        $types      = array_column($fields, 'type', 'key');
        $inputTypes = array_column($fields, 'input_type', 'key');

        $attachService  = app()->get(AttachService::class);
        $billService    = app()->get(ClientBillService::class);
        [$page, $limit] = $this->getPageValue();

        if (isset($where['contract_category']) && $where['contract_category']) {
            $where['contract_category'] = $this->getStatisticsCategoryIds($where['contract_category']);
        }
        $list        = $this->dao->listSearch($where, $page, $limit)->select($field)->get()?->toArray();
        $followMap   = $this->getFollowUpData($intersects, $list, $uid);
        $userMap     = $this->getCreatorAndSalesman($intersects, $list);
        $customerMap = $this->getCustomerData($list);
        foreach ($list as &$item) {
            foreach ($item as $key => $customer) {
                if (! in_array($key, $localField)) {
                    $inputType  = strtolower($inputTypes[$key]);
                    $type       = strtolower($types[$key]);
                    $item[$key] = $this->handleFieldValue($inputType, $type, $customer);
                    if (in_array($key, $customFields) && array_key_exists($key, $dictField)) {
                        $item[$key] = $this->handleDictValue($dictField[$key], $item[$key], $type, $inputType);
                    }
                }

                if (isset($inputTypes[$key]) && in_array($inputTypes[$key], ['file', 'images'])) {
                    $item[$key] = empty($item[$key]) ? []
                        : $attachService->getListByRelationType(AttachService::RELATION_TYPE_CONTRACT, $item[$key]);
                }

                foreach ($intersects as $intersect) {
                    if (! isset($item[$intersect])) {
                        $item[$intersect] = match ($intersect) {
                            'bill_no'           => $billService->select(['cid' => $item['id']], ['id', 'bill_no'])->toArray(),
                            'salesman'          => $userMap[$item['uid']] ?? [],
                            'contract_followed' => $followMap[$item['id']] ?? 0,
                            'creator'           => isset($item['creator_uid']) ? ($userMap[$item['creator_uid']] ?? null) : null,
                            'contract_customer' => $customerMap[$item['eid']] ?? null,
                            'payment_status'    => bccomp($item['surplus'], '0', 2) === 0 ? 1 : 0,
                            default             => ''
                        };
                    }
                }
            }
        }
        return $this->listData(
            $list,
            $this->dao->listSearch($where)->count(),
            ['total_price' => sprintf('%.2f', $this->dao->listSearch($where)->sum('contract_price'))]
        );
    }

    /**
     * 获取移动端列表.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function getUniListByType(int $customType, array $where): array
    {
        $uid        = uuid_to_uid($this->uuId(false));
        $fields     = app()->get(FormService::class)->getCustomDataByTypes(ContractEnum::CONTRACT, ['key', 'key_name', 'input_type', 'type', 'dict_ident']);
        $dictField  = $this->getDictField($fields);
        $types      = array_column($fields, 'type', 'key');
        $inputTypes = array_column($fields, 'input_type', 'key');
        $fieldKeys  = array_column($fields, 'key');

        $field = [
            'id', 'uid', 'contract_no', 'contract_name', 'contract_followed', 'contract_price', 'contract_status',
            'created_at', 'start_date', 'end_date', 'surplus',
        ];
        $intersects = ['contract_followed'];

        $whereUid = match ($customType) {
            ContractEnum::CONTRACT_VIEWER => ['uid' => $where['uid'] ?: app()->get(RolesService::class)->getDataUids($uid)],
            default                       => ['uid' => $uid]
        };

        $where          = array_merge($where, $whereUid);
        [$page, $limit] = $this->getPageValue();
        $list           = $this->dao->getList($where, $field, $page, $limit, 'id');
        $followMap      = $this->getFollowUpData($intersects, $list, $uid);
        $userMap        = $this->getCreatorAndSalesman(['salesman'], $list);

        $data = [];
        foreach ($list as $item) {
            $tmp = [];
            foreach ($item as $key => $customer) {
                if (array_key_exists($key, $inputTypes)) {
                    $type      = strtolower($types[$key]);
                    $inputType = strtolower($inputTypes[$key]);

                    $item[$key] = $this->handleFieldValue($inputType, strtolower($types[$key]), $customer);
                    if (array_key_exists($key, $dictField)) {
                        $name = $this->getNameListByIdent($dictField[$key], $item[$key]);
                        if ($inputType == 'radio') {
                            $name = $name[0] ?? '';
                        }
                        $item[$key] = $name;
                    }
                }

                if ($key == 'contract_followed') {
                    $item[$key] = (string) ($followMap[$item['id']] ?? 0);
                }

                if ($key == 'uid') {
                    $tmp['salesman'] = $userMap[$item[$key]] ?? [];
                }

                if (in_array($key, $fieldKeys)) {
                    $tmp[$key] = $item[$key];
                }

                if (in_array($key, ['id', 'contract_no', 'created_at', 'surplus']) && ! isset($tmp[$key])) {
                    $tmp[$key] = $item[$key];
                }
            }

            $data[] = $tmp;
        }

        $count = $this->dao->count($where);
        return $this->listData($data, $count);
    }

    /**
     * 获取用户设置的搜索列表.
     * @param mixed $customType
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws BindingResolutionException
     */
    public function searchField($customType)
    {
        $field[] = ['statistics_type', ''];
        $field[] = ['types', ''];
        $field[] = ['uid', ''];
        $field[] = ['eid', ''];

        $fieldSet = app()->get(FormService::class)->getCustomDataByTypes(CustomerEnum::CONTRACT, ['key as field', 'input_type']);
        $fieldSet = match ((int) $customType) {
            1       => array_merge($fieldSet, CustomerEnum::CUSTOMER_SEARCH_FIELD, CustomerEnum::CUSTOMER_VIEWER_SEARCH_FIELD),
            2       => array_merge($fieldSet, CustomerEnum::CUSTOMER_SEARCH_FIELD, CustomerEnum::CUSTOMER_CHARGE_SEARCH_FIELD),
            3       => array_merge($fieldSet, CustomerEnum::CUSTOMER_SEARCH_FIELD, CustomerEnum::CUSTOMER_HEIGHT_SEAS_SEARCH_FIELD),
            5       => array_merge($fieldSet, ContractEnum::CONTRACT_SEARCH_FIELD, ContractEnum::CONTRACT_VIEWER_SEARCH_FIELD),
            default => array_merge($fieldSet, ContractEnum::CONTRACT_SEARCH_FIELD, ContractEnum::CONTRACT_CHARGE_SEARCH_FIELD),
        };
        foreach ($fieldSet as $value) {
            $field[] = [$value['field'], ''];
        }
        return $field;
    }
}
