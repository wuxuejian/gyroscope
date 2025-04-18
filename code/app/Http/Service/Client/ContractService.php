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

namespace App\Http\Service\Client;

use App\Constants\ClientEnum;
use App\Constants\CustomEnum\ContractEnum;
use App\Constants\CustomEnum\CustomEnum;
use App\Constants\ScheduleEnum;
use App\Http\Contract\Client\ClientContractInterface;
use App\Http\Contract\Schedule\ScheduleInterface;
use App\Http\Dao\Client\ContractDao;
use App\Http\Model\BaseModel;
use App\Http\Service\Admin\AdminService;
use App\Http\Service\Attach\AttachService;
use App\Http\Service\BaseService;
use App\Http\Service\Client\Custom\ContractListService;
use App\Http\Service\Config\DictDataService;
use App\Http\Service\Config\FormService;
use App\Http\Service\Message\MessageService;
use App\Http\Service\System\RolesService;
use App\Http\Service\User\UserRemindLogService;
use App\Task\message\MessageSendTask;
use App\Task\message\StatusChangeTask;
use crmeb\utils\MessageType;
use crmeb\utils\Statistics;
use Hhxsv5\LaravelS\Swoole\Task\Task;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * 合同
 * Class ContractService.
 */
class ContractService extends BaseService implements ClientContractInterface
{
    use ContractListService;

    public $dao;

    public function __construct(ContractDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 保存合同.
     * @return BaseModel|Model
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function saveContract(array $data): mixed
    {
        $formService = app()->get(FormService::class);
        $list        = $formService->getFormDataList(CustomEnum::CONTRACT);
        $formService->fieldValueCheck($data, CustomEnum::CONTRACT, 0, $list);

        $attaches = [];

        foreach ($list as $item) {
            $type      = strtolower($item['type']);
            $inputType = strtolower($item['input_type']);
            foreach ($data as $key => &$datum) {
                if ($item['key'] == $key) {
                    if (in_array($inputType, ['file', 'images'])) {
                        $attaches = array_merge($attaches, (array) $datum);
                    }
                    $datum = $formService->getFormValue($type, $inputType, $datum);
                }
            }
        }

        if (isset($data['contract_customer'])) {
            $data['eid'] = $data['contract_customer'];
            unset($data['contract_customer']);
        }
        if (isset($data['contract_category'])) {
            $data['contract_cate'] = '/' . implode('/', json_decode($data['contract_category'], true)) . '/';
        }

        $attaches                                                 = array_filter($attaches);
        $data['surplus']                                          = $data['contract_price'];
        isset($data['signing_status']) && $data['signing_status'] = (int) $data['signing_status'];
        $data['creator_uid']                                      = $data['uid'] = uuid_to_uid($this->uuId(false));
        $data['contract_status']                                  = $this->getStatus($data);
        return $this->transaction(function () use ($data, $attaches) {
            $res = $this->dao->create($data);
            if (! $res) {
                throw $this->exception('保存失败');
            }

            // save relation attach
            if ($attaches) {
                app()->get(AttachService::class)->update(['id' => $attaches], ['relation_id' => $res->id, 'relation_type' => 3]);
            }

            if (isset($data['contract_followed'])) {
                $status = $data['contract_followed'] < 1 ? 0 : 1;
                app()->get(ClientContractSubscribeService::class)->subscribe($data['uid'], $res->id, $status);
            }
            return $res;
        });
    }

    public function getSearchField()
    {
        return [
            ['eid', ''],
            ['name', '', 'name_like'],
            ['salesman_id', '', 'uid'],
            ['time', ''],
            ['signing_status', ''],
            ['abnormal', '', 'contract_status'],
        ];
    }

    /**
     * 修改合同.
     * @return BaseModel|Model
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function updateContract(array $data, int $id): mixed
    {
        $info = $this->dao->get($id);
        if (! $info) {
            throw $this->exception('数据获取异常');
        }

        $formService = app()->get(FormService::class);
        $list        = $formService->getFormDataList(CustomEnum::CONTRACT);
        $formService->fieldValueCheck($data, CustomEnum::CONTRACT, $id, $list);

        $attaches = [];

        foreach ($list as $item) {
            $type      = strtolower($item['type']);
            $inputType = strtolower($item['input_type']);
            foreach ($data as $key => &$datum) {
                if ($item['key'] == $key) {
                    if (in_array($inputType, ['file', 'images'])) {
                        $attaches = array_merge($attaches, (array) $datum);
                    }
                    $datum = $formService->getFormValue($type, $inputType, $datum);
                }
            }
        }

        if (isset($data['contract_customer'])) {
            $data['eid'] = $data['contract_customer'];
            unset($data['contract_customer']);
        }
        if (isset($data['contract_category'])) {
            $data['contract_cate'] = '/' . implode('/', json_decode($data['contract_category'], true)) . '/';
        }

        if ($info->received >= $data['contract_price']) {
            $data['surplus'] = 0;
        } else {
            $data['surplus'] = bcsub((string) $data['contract_price'], $info->received, 2);
        }
        $uid                                                      = $info->uid;
        $attaches                                                 = array_filter($attaches);
        $data['contract_status']                                  = $info->is_abnormal ? '3' : $this->getStatus($data);
        isset($data['signing_status']) && $data['signing_status'] = (int) $data['signing_status'];
        return $this->transaction(function () use ($id, $uid, $data, $attaches) {
            $res = $this->dao->update($id, $data);
            if (! $res) {
                throw $this->exception('更新失败');
            }

            // save relation attach
            if ($attaches) {
                app()->get(AttachService::class)->update(['id' => $attaches], ['relation_id' => $id, 'relation_type' => 3]);
            }

            if (isset($data['contract_followed'])) {
                $status = $data['contract_followed'] < 1 ? 0 : 1;
                app()->get(ClientContractSubscribeService::class)->subscribe($uid, $id, $status);
            }
            return $res;
        });
    }

    /**
     * 合同详情.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function getInfo(int $id, string $uuid): mixed
    {
        $info = toArray($this->dao->get($id));
        if (! $info) {
            throw $this->exception('数据获取异常');
        }

        $attachField               = $this->getAttachField();
        $customerService           = app()->get(CustomerService::class);
        $attachService             = app()->get(AttachService::class);
        $dictDataService           = app()->get(DictDataService::class);
        $adminService              = app()->get(AdminService::class);
        $info['contract_followed'] = (string) app()->get(ClientContractSubscribeService::class)->getSubscribeStatus(uuid_to_uid($uuid), $id);

        $list = app()->get(FormService::class)->getFormDataWithType(CustomEnum::CONTRACT, false);
        foreach ($list as &$item) {
            foreach ($item['data'] as &$datum) {
                if (array_key_exists($datum['key'], $info)) {
                    $type           = strtolower($datum['type']);
                    $inputType      = strtolower($datum['input_type']);
                    $datum['value'] = $this->handleFieldValue($inputType, $type, $info[$datum['key']]);
                    if ($datum['dict_ident']) {
                        if (is_dimensional_data($datum['value'])) {
                            $datum['value'] = $this->handleDictValue($datum['dict_ident'], $datum['value'], $type);
                        } else {
                            $datum['value'] = $dictDataService->getNamesByValue($datum['dict_ident'], $datum['value']);
                        }
                    }
                    if (in_array($inputType, ['file', 'images'])) {
                        $datum['files'] = empty($datum['value']) ? [] : $attachService->select(['id' => $datum['value']], $attachField)?->toArray();
                    }
                }

                if ($datum['key'] == 'contract_customer') {
                    $datum['value'] = $customerService->column(['id' => $info['eid']], 'customer_name');
                }
            }
        }

        return [
            'contract_price'    => $info['contract_price'],
            'contract_status'   => $info['contract_status'],
            'contract_customer' => $customerService->get($info['eid'], ['id', 'customer_name']),
            'salesman'          => $adminService->get(['id' => $info['uid']], ['id', 'avatar', 'name']),
            'list'              => $list,
        ];
    }

    /**
     * 合同编辑表单数据.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function getEditInfo(int $id, string $uuid): mixed
    {
        $info = toArray($this->dao->get($id));
        if (! $info) {
            throw $this->exception('数据获取异常');
        }

        $attachField               = $this->getAttachField();
        $attachService             = app()->get(AttachService::class);
        $info['contract_followed'] = (string) app()->get(ClientContractSubscribeService::class)->getSubscribeStatus(uuid_to_uid($uuid), $id);

        $list = app()->get(FormService::class)->getFormDataWithType(ContractEnum::CONTRACT, platform: $this->getPlatform(), associationId: (int) $info['eid']);
        foreach ($list as &$item) {
            foreach ($item['data'] as &$datum) {
                if (array_key_exists($datum['key'], $info)) {
                    $type           = strtolower($datum['type']);
                    $inputType      = strtolower($datum['input_type']);
                    $datum['value'] = $this->handleFieldValue($inputType, $type, $info[$datum['key']]);
                    if (in_array($inputType, ['file', 'images'])) {
                        $datum['files'] = empty($datum['value']) ? [] : $attachService->select(['id' => $datum['value']], $attachField)?->toArray();
                    }
                }

                if ($datum['key'] == 'contract_customer') {
                    $datum['value'] = $info['eid'];
                }
            }
        }

        return $list;
    }

    /**
     * 列表统计
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function getListStatistics(int $customType, string $uuid): array
    {
        $subscribeUid = $uid = uuid_to_uid($uuid);

        $uid = match ($customType) {
            ContractEnum::CONTRACT_VIEWER => app()->get(RolesService::class)->getDataUids($uid),
            default                       => $uid,
        };

        // 全部
        $total = $this->dao->count(['uid' => $uid]);
        // 未签约
        $notSigned = $this->dao->count(['uid' => $uid, 'signing_status' => 0]);
        // 已签约
        $signed = $this->dao->count(['uid' => $uid, 'signing_status' => 1]);
        // 签约作废
        $voidSigned = $this->dao->count(['uid' => $uid, 'signing_status' => 2]);
        // 我关注的
        $concern = app()->get(ClientContractSubscribeService::class)->contractCount($uid, $subscribeUid);
        // 过期合同
        $expired = $this->dao->count(['uid' => $uid, 'abnormal' => 2, 'signing_status_lt' => 2]);

        $remindService = app()->get(ClientRemindService::class);
        // 急需续费
        $urgentRenewal = $this->dao->count(['uid' => $uid, 'id' => $remindService->getUrgentRenewalCid(), 'signing_status_lt' => 2]);
        // 费用过期
        $costExpired = $this->dao->count(['uid' => $uid, 'id' => $remindService->getUrgentRenewalCid(true), 'signing_status_lt' => 2]);
        return [
            'total'          => $total,
            'concern'        => $concern,
            'not_signed'     => $notSigned,
            'signed'         => $signed,
            'void_signed'    => $voidSigned,
            'expired'        => $expired,
            'urgent_renewal' => $urgentRenewal,
            'cost_expired'   => $costExpired,
        ];
    }

    /**
     * 无需同步字段.
     * @return string[]
     */
    public function getOutOfSyncField(): array
    {
        return ['contract_customer'];
    }

    /**
     * 下拉列表.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function getSelectList(array|int $eid, string $uuid): array
    {
        $userIds = app()->get(RolesService::class)->getDataUids(uuid_to_uid($uuid));
        return $this->dao->getList(['eid' => $eid, 'uid' => $userIds], ['id', 'eid', 'contract_name as title', 'contract_no'], 0, 0, 'id');
    }

    /**
     * 移动端详情.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function getUniInfo(int $id, string $uuid): array
    {
        $info = toArray($this->dao->get($id));
        if (! $info) {
            throw $this->exception('数据获取异常');
        }

        $data = [
            'id'                => $info['id'],
            'uid'               => $info['uid'],
            'contract_name'     => $info['contract_name'],
            'contract_status'   => $info['contract_status'],
            'contract_customer' => app()->get(CustomerService::class)->get(['id' => $info['eid']], ['id', 'customer_name']),
            'start_date'        => $info['start_date'],
            'end_date'          => $info['end_date'],
            'created_at'        => $info['created_at'],
        ];

        $attachField               = $this->getAttachField();
        $attachService             = app()->get(AttachService::class);
        $dictDataService           = app()->get(DictDataService::class);
        $subscribeService          = app()->get(ClientContractSubscribeService::class);
        $info['contract_followed'] = (string) $subscribeService->getSubscribeStatus(uuid_to_uid($uuid), $id);

        $list = app()->get(FormService::class)->getFormDataWithType(ContractEnum::CONTRACT, false);
        foreach ($list as &$item) {
            foreach ($item['data'] as &$datum) {
                if (array_key_exists($datum['key'], $info)) {
                    $type           = strtolower($datum['type']);
                    $inputType      = strtolower($datum['input_type']);
                    $datum['value'] = $this->handleFieldValue($inputType, $type, $info[$datum['key']]);
                    if ($datum['dict_ident']) {
                        $datum['value'] = $dictDataService->getNamesByValue($datum['dict_ident'], $datum['value']);
                    }
                    if (in_array($inputType, ['file', 'images'])) {
                        $datum['files'] = empty($datum['value']) ? [] : $attachService->select(['id' => $datum['value']], $attachField)?->toArray();
                    }
                }

                if ($datum['key'] == 'contract_customer') {
                    $datum['value'] = app()->get(CustomerService::class)->column(['id' => $info['eid']], 'customer_name');
                }
            }
        }
        $data['list'] = $list;
        return $data;
    }

    /**
     * 获取合同缓存个数.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getClientContractCountCache(): int
    {
        $ttl = (int) sys_config('system_cache_ttl', 3600);
        return (int) Cache::tags(['client'])->remember('client_contract_count', $ttl, function () {
            $this->dao->getClientContractCount();
        });
    }

    /**
     * 执行定时器任务
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function timer(int $page, int $limit): void
    {
        $key          = 'client_contract_list_' . $page . '_' . $limit;
        $ttl          = (int) sys_config('system_cache_ttl', 3600);
        $contractList = Cache::tags(['client'])->remember($key, $ttl, function () use ($page, $limit) {
            $this->dao->getClientContractList([], $page, $limit);
        });

        if (! $contractList) {
            return;
        }

        $nowTimer = time();
        foreach ($contractList as $item) {
            $dateTimer = strtotime($item['end_date']);
            $dateNow   = now()->setTimeFromTimeString(date('Y-m-d H:i:s', $dateTimer));
            if ($dateTimer > $nowTimer) {
                // 前30天提醒
                // 转换成天
                $subTime = (int) (($dateTimer - $nowTimer) / 60 / 60 / 24);
                // 30天前
                if ($subTime == 30) {
                    $this->sendMessage($item, MessageType::CONTRACT_SOON_OVERDUE_REMIND);
                }
            } elseif ($dateNow->year == now()->year && $dateNow->day == now()->day && $dateNow->month == now()->month) {
                // 结束当天
                $this->sendMessage($item, MessageType::CONTRACT_OVERDUE_DAY_REMIND);
            }
        }
    }

    /**
     * 发送消息.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function sendMessage(array $item, string $type): void
    {
        $userRemindLogService = app()->get(UserRemindLogService::class);
        // 发送过不再发送
        $exists = $userRemindLogService->exists([
            'entid'       => 1,
            'user_id'     => $item['card']['user_id'],
            'year'        => now()->year,
            'month'       => now()->month,
            'day'         => now()->day,
            'remind_type' => $type,
            'relation_id' => $item['id'],
        ]);

        if ($exists) {
            return;
        }

        $message = app()->get(MessageService::class)->getMessageContent(1, $type);
        if ($message['template_time']) {
            if ($message['remind_time'] && date('H:i') == $message['remind_time']) {
                $res = true;
            } else {
                $res = false;
            }
        } else {
            $res = true;
        }

        if (! $res) {
            return;
        }

        $task = new MessageSendTask(
            entid: 1,
            i: 1,
            type: $type,
            toUid: ['to_uid' => $item['card']['user_id'], 'phone' => $item['card']['phone'] ?? ''],
            params: [
                '合同名称'     => $item['title'] ?? '',
                '合同金额'     => $item['price'] ?? '',
                '合同开始时间' => $item['start_date'] ?? '',
                '合同结束时间' => $item['end_date'] ?? '',
                '客户名称'     => $item['name'] ?? '',
                '业务员'       => $item['card']['name'] ?? '',
            ],
            other: ['id' => $item['id']],
            linkId: $item['id'],
        );

        Task::deliver($task);
        // 写入消息提醒记录数据
        $userRemindLogService->create([
            'remind_type' => $type,
            'user_id'     => $item['uid'],
            'relation_id' => $item['id'],
            'entid'       => 1,
            'year'        => now()->year,
            'day'         => now()->day,
            'month'       => now()->month,
            'week'        => now()->week,
        ]);
    }

    /**
     * 异常状态.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function abnormal(int $id, int $status, string $uuid): bool
    {
        $info = $this->dao->get($id);
        if (! $info) {
            throw $this->exception('数据获取异常');
        }

        if ($status !== 0) {
            $info->is_abnormal     = 1;
            $info->contract_status = 3;
            $info->save();
        } else {
            $info->is_abnormal = 0;
            // reload contract status
            $this->reloadStatus($id, $info);
        }
        return true;
    }

    /**
     * 未回款金额.
     * @throws BindingResolutionException
     */
    public function getRingRatioUncollected(array $userIds, array $ids = []): array
    {
        $ratio = 0;
        $where = array_merge(['uid' => $userIds, 'pay_status' => 0, 'signing_status_lt' => 2], $ids ? ['id' => $ids] : []);
        $price = sprintf('%.2f', $this->dao->sum($where, 'surplus'));
        return compact('price', 'ratio');
    }

    /**
     * 删除合同.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function deleteContract(int $id, string $uuid): int
    {
        if (app()->get(ClientBillService::class)->count(['cid' => $id, 'entid' => 1, 'status' => 1])) {
            throw $this->exception('当前合同存在审核通过的付款/支出数据, 不能删除');
        }

        if (app()->get(ClientInvoiceService::class)->count(['cid' => $id, 'entid' => 1])) {
            throw $this->exception('当前合同存在发票, 不能删除');
        }

        return $this->transaction(function () use ($id) {
            $res = $this->dao->delete($id);
            if (! $res) {
                throw $this->exception(__('common.delete.fail'));
            }

            $billService = app()->get(ClientBillService::class);
            $linkIds     = array_merge([$id], $billService->column(['cid' => $id, 'entid' => 1], 'id'));
            $billService->delete($id, 'cid');
            Task::deliver(new StatusChangeTask(ClientEnum::CONTRACT_DELETE_NOTICE, ClientEnum::CONTRACT_DELETE, 1, $linkIds));
            app()->get(ScheduleInterface::class)->delScheduleByLinkId($id, [ScheduleEnum::TYPE_CLIENT_RENEW, ScheduleEnum::TYPE_CLIENT_RETURN]);
            app()->get(ClientContractSubscribeService::class)->delete(['cid' => $id]);
            Cache::tags(['client'])->flush();
            return $res;
        });
    }

    /**
     * 新增合同统计
     * @throws BindingResolutionException
     */
    public function getRingRatioCount(string $searchTime, array $userIds, string $ratioTime = '', array $ids = []): array
    {
        $ratio   = 0;
        $idWhere = $ids ? ['id' => $ids] : [];
        $count   = $this->dao->count(array_merge(['uid' => $userIds, 'start_date' => $searchTime], $idWhere));
        if (! $ratioTime) {
            return compact('count', 'ratio');
        }
        $ratioCount = $this->dao->count(array_merge(['uid' => $userIds, 'start_date' => $ratioTime], $idWhere));
        $ratio      = Statistics::ringRatio($count, $ratioCount);
        return compact('count', 'ratio');
    }

    /**
     * 新增合同金额统计
     * @throws BindingResolutionException
     */
    public function getNewAddRingRatio(string $searchTime, array $userIds, string $ratioTime, array $ids = []): array
    {
        $field   = 'contract_price';
        $idWhere = $ids ? ['id' => $ids] : [];
        $price   = sprintf('%.2f', $this->dao->sum(array_merge($idWhere, ['uid' => $userIds, 'start_date' => $searchTime]), $field));
        $ratio   = Statistics::ringRatio($price, $this->dao->sum(array_merge($idWhere, ['uid' => $userIds, 'start_date' => $ratioTime]), $field));
        return compact('price', 'ratio');
    }

    /**
     * 合同类型分析统计
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function getCategoryRank(string $time, array $userIds, array $categoryIds = [], int $categoryId = 0): array
    {
        $categoryIds = $this->getStatisticsCategoryIds($categoryIds);
        if (! $categoryIds) {
            $categoryIds = app()->get(DictDataService::class)->getDefaultContractCategory();
        }
        $topIds = $this->getLevelIdByCategoryIds($categoryIds, $categoryId);
        $field  = ['name as category_name', 'value as category_id'];
        $where  = ['dict_value' => array_keys($topIds), 'type_name' => 'contract_type'];
        $list   = app()->get(DictDataService::class)->select($where, $field)->toArray();
        if (! $list) {
            return [];
        }

        foreach ($list as $key => &$item) {
            $ids           = $topIds[$item['category_id']];
            $rank          = $this->dao->getRankByCategory($time, $userIds, $ids);
            $item['price'] = $rank['price'] ?? '0.00';
            if ($item['price'] < 0.01) {
                unset($list[$key]);
                continue;
            }
            $item['count']  = $this->dao->getCountByCategory($time, $userIds, $ids);
            $expend         = $this->dao->getRankByCategory($time, $userIds, $ids, [2]);
            $item['expend'] = $expend['price'] ?? '0.00';
        }
        $price = array_column($list, 'price');
        array_multisort($price, SORT_DESC, $list);

        return $list;
    }

    /**
     * 获取分类等级.
     */
    public function getLevelIdByCategoryIds(array $categoryIds, int $categoryId = 0): array
    {
        $ids = [];
        foreach ($categoryIds as $item) {
            $tmp = json_decode($item, true);
            if ($categoryId == 0) {
                if (! isset($ids[$tmp[0]])) {
                    $ids[$tmp[0]] = [];
                }
                $ids[$tmp[0]][] = $item;
            } else {
                $index = array_search($categoryId, $tmp);
                if ($index !== false) {
                    if (isset($tmp[$index + 1])) {
                        if (! isset($ids[$tmp[$index + 1]])) {
                            $ids[$tmp[$index + 1]] = [];
                        }
                        if (isset($ids[$tmp[$index + 1]])) {
                            $ids[$tmp[$index + 1]][] = $item;
                        }
                    }
                }
            }
        }
        return $ids;
    }

    /**
     * 业绩统计
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function performanceStatistics(string $searchTime, array $userIds, string $ratioTime = '', array $categoryIds = []): array
    {
        $ids         = [];
        $categoryIds = $this->getStatisticsCategoryIds($categoryIds);
        if ($categoryIds) {
            $ids = $this->dao->column(['contract_category' => $categoryIds], 'id');
        }

        return [
            'new_contract'       => $this->getRingRatioCount($searchTime, $userIds, $ratioTime, $ids),
            'new_contract_price' => $this->getNewAddRingRatio($searchTime, $userIds, $ratioTime, $ids),
            'uncollected_price'  => $this->getRingRatioUncollected($userIds, $ids),
        ];
    }

    /**
     * 处理分类ID.
     */
    public function handleCategoryIds(array $categoryIds): array
    {
        return array_map(function ($item) {
            return json_encode(array_map('strval', $item), JSON_UNESCAPED_UNICODE);
        }, $categoryIds);
    }

    /**
     * 合同状态定时任务.
     * @throws BindingResolutionException
     */
    public function statusTimer(): void
    {
        $now = Carbon::now(config('app.timezone'))->toDateString();
        $this->dao->update(['start_date_gt' => $now, 'contract_status_lt' => '2'], ['contract_status' => '1']);
        $this->dao->update(['end_date_lt' => $now, 'contract_status_lt' => '3'], ['contract_status' => '2']);
    }

    /**
     * 重载合同状态
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function reloadStatus(int $id, mixed $contract = null): void
    {
        $contract = $contract ?: $this->dao->get($id);
        if ($contract->is_abnormal) {
            $contract->contract_status = '3';
        } else {
            $status = '1';
            $tz     = config('app.timezone');
            $now    = Carbon::now($tz)->toDateString();
            if ($contract->start_date && Carbon::parse($contract->start_date, $tz)->gt($now)) {
                $status = '0';
            }

            if ($contract->end_date && Carbon::parse($contract->end_date, $tz)->lt($now)) {
                $status = '2';
            }
            $contract->contract_status = $status;
        }

        $contract->save();
    }

    /**
     * 获取分类筛选数据.
     */
    public function getStatisticsCategoryIds(array $categoryIds = []): array
    {
        $categoryIds = array_filter($categoryIds);
        return $categoryIds ? $this->handleCategoryIds($categoryIds) : [];
    }

    /**
     * 转移.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function shift(array $ids, int $toUid, int $invoice = 0): mixed
    {
        if ($toUid < 1) {
            throw $this->exception(__('common.empty.attr', ['attr' => '转移人ID']));
        }
        return $this->transaction(function () use ($ids, $toUid, $invoice) {
            if ($invoice) {// 转移发票
                app()->get(ClientInvoiceService::class)->search(['cid' => $ids])->update(['uid' => $toUid]);
            }

            app()->get(ClientBillService::class)->search(['cid' => $ids])->update(['uid' => $toUid]);
            return $this->dao->search(['id' => $ids])->update(['uid' => $toUid]);
        });
    }

    /**
     * 导入
     * TODO:待优化.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function batchImport(array $data, array $uids): mixed
    {
        $uid      = auth('admin')->id();
        $required = $fieldMap = $fieldNameKeyMap = [];
        $fields   = app()->get(FormService::class)->getExportField(ContractEnum::CONTRACT);
        foreach ($fields as $field) {
            $fieldMap[$field['key']]             = $field;
            $fieldNameKeyMap[$field['key_name']] = $field['key'];

            if ($field['required']) {
                $required[] = $field['key_name'];
            }
        }

        $adminService    = app()->get(AdminService::class);
        $customerService = app()->get(CustomerService::class);

        // 业务员
        $salesmanMap = $adminService->column(['id' => $uids, 'name_eq' => array_column($data, '业务员')], 'id', 'name');
        // 客户
        $customerMap = $customerService->column(['uid' => $uids, 'name_eq' => array_column($data, '客户名称')], 'id', 'customer_name');
        return $this->transaction(function () use ($data, $fieldNameKeyMap, $fieldMap, $customerMap, $salesmanMap, $uid, $uids) {
            foreach ($data as $index => $customer) {
                $insert   = [];
                $isCreate = false;
                foreach ($customer as $key => $item) {
                    $key = trim($key, '"');
                    if (! isset($fieldNameKeyMap[$key])) {
                        if ($key == '业务员') {
                            $insert['uid'] = $salesmanMap[$item] ?? $uid;
                        } elseif ($key == 'ID') {
                            $insert['id'] = max((int) $item, 0);
                            if ($insert['id'] > 0 && ! $this->dao->exists(['id' => $insert['id'], 'uid' => $uids])) {
                                $insert['id'] = 0;
                            }
                        }

                        continue;
                    }

                    $value     = $item;
                    $field     = $fieldNameKeyMap[$key];
                    $formField = $fieldMap[$field] ?? [];

                    if ($key == '客户名称') {
                        if (! isset($customerMap[$value])) {
                            break;
                        }
                        $insert['eid'] = $customerMap[$value];
                        continue;
                    }

                    if ($field == 'start_date' || $field == 'end_date') {
                        try {
                            Carbon::parse($value);
                        } catch (\Throwable $e) {
                            throw $this->exception('第' . ($index + 1) . '行数据' . ['start_date' => '开始', 'end_date' => '结束'][$field] . '时间格式无法解析');
                        }
                    }

                    // 字典
                    if ($formField && $formField['dict_ident']) {
                        $value = app()->get(DictDataService::class)->getValuesByType($item, $formField['dict_ident'], $formField['input_type'], $formField['type']);
                        if ($formField['input_type'] == 'select' && $formField['type'] == 'single') {
                            if ($formField['dict_ident'] !== 'area_cascade') {
                                $value = $value[0] ?? '';
                            }
                        }
                    }

                    if ($field == 'contract_status' && is_array($value)) {
                        $value                                = intval($value[0] ?? 0);
                        $value == 3 && $insert['is_abnormal'] = 1;
                    }
                    $insert[$field] = $value;
                }

                if (! isset($insert['eid']) || $insert['eid'] < 1) {
                    continue;
                }

                if (isset($insert['contract_status'])
                    && $insert['contract_status'] < 3
                    && isset($insert['start_date'], $insert['end_date'])
                ) {
                    $insert['contract_status'] = $this->getStatus([
                        'contract_status' => $insert['contract_status'],
                        'start_date'      => $insert['start_date'],
                        'end_date'        => $insert['end_date'],
                    ]);
                }

                // 找不到则新增
                if (! isset($insert['id']) || $insert['id'] < 1) {
                    $isCreate              = true;
                    $insert['creator_uid'] = $uid;
                }

                if (! isset($insert['uid'])) {
                    $insert['uid'] = $uid;
                }

                $followed = $insert['contract_followed'] ?? false;
                if ($isCreate) {
                    foreach ($insert as $field => $value) {
                        if (is_array($value)) {
                            $insert[$field] = json_encode($value);
                        }
                    }

                    $res = $this->dao->create($insert);
                    if (! $res) {
                        throw $this->exception(__('common.insert.fail'));
                    }

                    if ($followed !== false) {
                        app()->get(ClientContractSubscribeService::class)->subscribe($uid, $res->id, $followed < 1 ? 0 : 1);
                    }
                } else {
                    if (isset($insert['contract_status'])) {
                        unset($insert['contract_status']);
                    }

                    $id = $insert['id'];
                    unset($insert['id']);
                    $res = $this->dao->update($id, $insert);
                    if (! $res) {
                        throw $this->exception(__('common.operation.fail'));
                    }

                    if ($followed !== false) {
                        app()->get(ClientContractSubscribeService::class)->subscribe($uid, $id, $followed < 1 ? 0 : 1);
                    }
                }
            }
            return true;
        });
    }

    /**
     * 获取表单合同状态
     */
    private function getStatus(array $data): string
    {
        $now       = now();
        $startTime = $endTime = null;
        $status    = $data['contract_status'] ?: '1';
        if (isset($data['start_date']) && $data['start_date']) {
            $startTime = Carbon::parse($data['start_date']);
            if ($startTime->gt($now)) {
                $status = '0';
            }

            if ($startTime->lt($now)) {
                $status = '1';
            }
        }

        if (isset($data['end_date']) && $data['end_date']) {
            $endTime = Carbon::parse($data['end_date']);
            if ($endTime->lt($now)) {
                $status = '2';
            }
        }

        if ($startTime && $endTime && $startTime->gt($endTime)) {
            throw $this->exception('结束时间不能早于开始时间');
        }
        return $status;
    }
}
