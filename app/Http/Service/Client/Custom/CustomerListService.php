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

namespace App\Http\Service\Client\Custom;

use App\Constants\CustomEnum\ContractEnum;
use App\Constants\CustomEnum\CustomerEnum;
use App\Http\Service\Attach\AttachService;
use App\Http\Service\Client\ClientBillService;
use App\Http\Service\Client\ClientFollowService;
use App\Http\Service\Client\ClientInvoiceService;
use App\Http\Service\Client\ClientLabelService;
use App\Http\Service\Client\ClientSubscribeService;
use App\Http\Service\Client\ContractService;
use App\Http\Service\Client\CustomerLiaisonService;
use App\Http\Service\Client\CustomerRecordService;
use App\Http\Service\Config\FormService;
use App\Http\Service\Config\SalesmanCustomService;
use App\Http\Service\System\RolesService;
use crmeb\traits\service\ResourceServiceTrait;
use crmeb\traits\service\ServicesTrait;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Carbon;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * 客户列表.
 */
trait CustomerListService
{
    use CommonService;
    use ResourceServiceTrait;
    use ServicesTrait;

    /**
     * 获取列表.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function getListByType(array $where, bool $isExport = false): array
    {
        $tz             = '';
        $nowObj         = null;
        $lastFollowTime = '';
        $customType     = (int) $where['types'];
        $uid            = $where['subscribe_uid'] = uuid_to_uid($this->uuId(false));

        if ($isExport) {
            $localField = ['id', 'customer_no', 'uid', 'customer_status'];
            $fields     = app()->get(FormService::class)->getExportField(CustomerEnum::CUSTOMER);
            $types      = array_column($fields, 'type', 'key');
            $dictField  = $this->getDictField($fields);
            $intersects = ['salesman', 'customer_followed'];
            $field      = array_diff(array_merge($localField, array_column($fields, 'key')), $intersects);
        } else {
            $fields       = app()->get(FormService::class)->getCustomDataByTypes(CustomerEnum::CUSTOMER, ['key', 'key_name', 'input_type', 'type', 'dict_ident']);
            $dictField    = $this->getDictField($fields);
            $customFields = app()->get(SalesmanCustomService::class)->getCustomField($uid, $customType, CustomerEnum::LIST_SELECT);
            $tmpFields    = [
                'liaison_tel', 'last_follow_time', 'un_followed_days', 'amount_recorded', 'amount_expend', 'invoiced_amount',
                'contract_num', 'invoice_num', 'attachment_num', 'salesman', 'customer_followed', 'creator', 'return_reason',
            ];
            $types      = array_column($fields, 'type', 'key');
            $localField = ['id', 'uid', 'before_uid', 'creator_uid', 'customer_no', 'customer_status', 'return_num', 'created_at', 'updated_at'];
            $field      = array_merge($localField, array_diff(array_intersect(array_keys($types), $customFields), $tmpFields));
        }

        $inputTypes     = array_column($fields, 'input_type', 'key');
        [$page, $limit] = $this->getPageValue();

        // TODO 自定义搜索
        $list = toArray($this->dao->listSearch($where, $page, $limit)->select($field)->get());

        if ($isExport) {
            $liaisonMap = [];
            $intersects = ['salesman', 'customer_followed'];
        } else {
            $intersects = array_diff(array_merge($field, array_intersect($customFields, $tmpFields)), $localField);
            $intersects = match ($customType) {
                CustomerEnum::CUSTOMER_HEIGHT_SEAS => array_merge($intersects, ['before_salesman']),
                default                            => $intersects
            };

            $liaisonMap = $this->getLiaisonTel(array_column($list, 'id'));
            if (in_array('un_followed_days', $intersects)) {
                $tz     = config('app.timezone');
                $nowObj = Carbon::today($tz);
            }
        }
        $userMap       = $this->getCreatorAndSalesman($intersects, $list);
        $followMap     = $this->getFollowUpData($intersects, $list, $uid);
        $attachService = app()->get(AttachService::class);
        foreach ($list as &$item) {
            if (in_array('last_follow_time', $intersects)) {
                $lastFollowTime = $this->getLastFollowTime($item['id']);
            }
            foreach ($item as $key => $customer) {
                if (! in_array($key, $localField)) {
                    $inputType  = strtolower($inputTypes[$key]);
                    $item[$key] = $this->handleFieldValue($inputType, strtolower($types[$key]), $customer);
                    if (array_key_exists($key, $dictField)) {
                        $item[$key] = $this->handleDictValue($dictField[$key], $inputType, $item[$key]);
                    }
                }

                if ($key == 'customer_label') {
                    $item[$key] = is_array($item[$key]) ? $this->getCustomerLabelList($item['id'], $item[$key]) : [];
                }

                if (isset($inputTypes[$key]) && in_array($inputTypes[$key], ['file', 'images'])) {
                    $item[$key] = empty($item[$key]) ? [] :
                        $attachService->getListByRelationType(AttachService::RELATION_TYPE_CLIENT, $item[$key]);
                }

                foreach ($intersects as $intersect) {
                    if (! isset($item[$intersect])) {
                        $item[$intersect] = match ($intersect) {
                            'liaison_tel'       => $liaisonMap[$item['id']] ?? [],
                            'un_followed_days'  => $this->getUnFollowedDays($item['id'], $tz, $nowObj, $lastFollowTime),
                            'amount_recorded'   => $this->getAmountRecorded($item['id']),
                            'amount_expend'     => $this->getAmountExpend($item['id']),
                            'invoiced_amount'   => $this->getInvoicedAmount($item['id']),
                            'contract_num'      => $this->getContractNum($item['id']),
                            'invoice_num'       => $this->getInvoiceNum($item['id']),
                            'attachment_num'    => $this->getAttachmentNum($item['id']),
                            'salesman'          => $userMap[$item['uid']] ?? [],
                            'customer_followed' => $followMap[$item['id']] ?? 0,
                            'last_follow_time'  => $lastFollowTime,
                            'creator'           => isset($item['creator_uid']) ? ($userMap[$item['creator_uid']] ?? null) : null,
                            'return_reason'     => $this->getReturnReason($item['id']),
                            'before_salesman'   => $userMap[$item['before_uid']] ?? [],
                            default             => ''
                        };
                    }
                }
            }
        }
        $count = $this->dao->listSearch($where)->count();
        return $this->listData($list, $count);
    }

    /**
     * 标签列表.
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getCustomerLabelList(int $id, array $labelIds = []): array
    {
        // TODO 加载优化
        return app()->get(ClientLabelService::class)->select(['id' => $labelIds], ['id', 'name'])->toArray();
    }

    /**
     * 获取业务员数据.
     */
    public function getLiaisonTel(array|int $id): array
    {
        $liaisonMap = [];
        if (empty($id)) {
            return $liaisonMap;
        }
        // TODO 加载优化
        $list = app()->get(CustomerLiaisonService::class)->select(['eid' => $id], ['eid', 'liaison_name', 'liaison_tel'])->toArray();
        foreach ($list as $item) {
            $liaisonMap[$item['eid']] = $item;
        }

        return $liaisonMap;
    }

    /**
     * 已入账金额.
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getAmountRecorded(int $id): string
    {
        $billService = app()->get(ClientBillService::class);
        return sprintf('%.2f', $billService->getSum(['eid' => $id, 'types' => [0, 1]]));
    }

    /**
     * 已支出金额.
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getAmountExpend(int $id): string
    {
        $billService = app()->get(ClientBillService::class);
        return sprintf('%.2f', $billService->getSum(['eid' => $id, 'types' => 2]));
    }

    /**
     * 已开票金额.
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getInvoicedAmount(int $id): string
    {
        $billService = app()->get(ClientBillService::class);
        return sprintf('%.2f', $billService->sum(['eid' => $id, 'status' => [1, 3, 5, 6]], 'num'));
    }

    /**
     * 合同数量.
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getContractNum(int $id): int
    {
        return app()->get(ContractService::class)->count(['eid' => $id]);
    }

    /**
     * 发票数量.
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getInvoiceNum(int $id): int
    {
        return app()->get(ClientInvoiceService::class)->count(['eid' => $id]);
    }

    /**
     * 附件数量.
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getAttachmentNum(int $id): int
    {
        return app()->get(AttachService::class)->getCountByRelationType(AttachService::RELATION_TYPE_CLIENT, $id);
    }

    /**
     * 最后跟进时间.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getLastFollowTime(int $id): string
    {
        return app()->get(ClientFollowService::class)->getLastFollowTime($id);
    }

    /**
     * 获取退回原因.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getReturnReason(int $id): string
    {
        return app()->get(CustomerRecordService::class)->getLastReasonByEid($id, 1);
    }

    /**
     * 字段过滤.
     * @return string[]
     */
    public function dictFilterField(): array
    {
        return ['customer_followed'];
    }

    public function followUpField(): string
    {
        return 'customer_followed';
    }

    public function followUpService(): string
    {
        return ClientSubscribeService::class;
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
        return app()->get(ClientSubscribeService::class)->getSubscribeStatusWithEid($uid, $ids);
    }

    /**
     * 获取未跟进天数.
     * @throws BindingResolutionException
     */
    public function getUnFollowedDays(int $id, string $tz, $nowObj, string $followTime = ''): int
    {
        if (! $followTime) {
            $followTime = $this->getLastFollowTime($id);
        }
        return Carbon::parse($followTime, $tz)->startOfDay()->diffInDays($nowObj, false);
    }

    /**
     * 获取移动端列表.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function getUniListByType(int $customType, array $where, int $uid): array
    {
        $fields     = app()->get(FormService::class)->getCustomDataByTypes(CustomerEnum::CUSTOMER, ['key', 'key_name', 'input_type', 'type', 'dict_ident']);
        $dictField  = $this->getDictField($fields);
        $types      = array_column($fields, 'type', 'key');
        $inputTypes = array_column($fields, 'input_type', 'key');
        $fieldKeys  = array_column($fields, 'key');

        $field = [
            'id', 'uid', 'customer_no', 'customer_name', 'customer_followed', 'customer_label', 'b37a3f16',
            'customer_status', 'created_at',
        ];
        $intersects = ['last_follow_time', 'customer_label', 'customer_followed'];

        $whereUid = match ($customType) {
            CustomerEnum::CUSTOMER_VIEWER      => ['uid' => app()->get(RolesService::class)->getDataUids($uid)],
            CustomerEnum::CUSTOMER_HEIGHT_SEAS => ['uid' => 0],
            default                            => ['uid' => $uid]
        };

        [$page, $limit] = $this->getPageValue();
        $where          = array_merge($where, $whereUid);
        if (isset($where['salesman']) && $where['salesman']) {
            $where['salesman'] = is_string($where['salesman']) ? explode(',', $where['salesman']) : $where['salesman'];
        }
        $list      = $this->dao->listSearch($where, $page, $limit)->select($field)->get()?->toArray();
        $followMap = $this->getFollowUpData($intersects, $list, $uid);
        $userMap   = $this->getCreatorAndSalesman(['salesman'], $list);

        $data = [];
        foreach ($list as $item) {
            $tmp = [
                'last_follow_time' => $this->getLastFollowTime($item['id']),
            ];
            foreach ($item as $key => $customer) {
                if (array_key_exists($key, $inputTypes)) {
                    $type      = strtolower($types[$key]);
                    $inputType = strtolower($inputTypes[$key]);

                    $item[$key] = $this->handleFieldValue($inputType, strtolower($types[$key]), $customer);
                    if (array_key_exists($key, $dictField)) {
                        $name = $this->getNameListByIdent($dictField[$key], $item[$key]);
                        if ($inputType == 'radio' || ($inputType == 'select' && $type == 'single')) {
                            $name = $name[0] ?? '';
                        }
                        $item[$key] = $name;
                    }

                    if ($key == 'customer_label') {
                        $item[$key] = is_array($item[$key]) ? $this->getCustomerLabelList($item['id'], $item[$key]) : [];
                    }
                }

                if ($key == 'customer_followed') {
                    $item[$key] = (string) ($followMap[$item['id']] ?? 0);
                }

                if ($key == 'uid') {
                    $tmp['salesman'] = $userMap[$item[$key]] ?? [];
                }

                if (in_array($key, $fieldKeys)) {
                    $tmp[$key] = $item[$key];
                }

                if (in_array($key, ['created_at', 'id', 'customer_no']) && ! isset($tmp[$key])) {
                    $tmp[$key] = $item[$key];
                }
            }
            $data[] = $tmp;
        }

        $count = $this->dao->listSearch($where)->count();
        return $this->listData($data, $count);
    }

    /**
     * 获取用户设置的搜索列表.
     * @return array
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function searchField($customType)
    {
        $field[] = ['statistics_type', ''];
        $field[] = ['types', ''];
        $field[] = ['uid', ''];

        $fieldSet = app()->get(FormService::class)->getCustomDataByTypes(CustomerEnum::CUSTOMER, ['key as field', 'input_type']);
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
