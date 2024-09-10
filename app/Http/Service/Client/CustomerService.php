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

namespace App\Http\Service\Client;

use App\Constants\CacheEnum;
use App\Constants\ClientEnum;
use App\Constants\CustomEnum\CustomerEnum;
use App\Constants\ScheduleEnum;
use App\Constants\UserAgentEnum;
use App\Http\Contract\Client\ClientInterface;
use App\Http\Contract\Schedule\ScheduleInterface;
use App\Http\Dao\Client\CustomerDao;
use App\Http\Service\Admin\AdminService;
use App\Http\Service\Assess\UserScopeService;
use App\Http\Service\Attach\AttachService;
use App\Http\Service\BaseService;
use App\Http\Service\Client\Custom\CustomerListService;
use App\Http\Service\Config\DictDataService;
use App\Http\Service\Config\FormService;
use App\Http\Service\Frame\FrameAssistService;
use App\Http\Service\Frame\FrameService;
use App\Http\Service\Message\MessageService;
use App\Http\Service\Notice\NoticeRecordService;
use App\Http\Service\System\RolesService;
use App\Task\message\MessageSendTask;
use App\Task\message\StatusChangeTask;
use crmeb\utils\Date;
use crmeb\utils\Statistics;
use Hhxsv5\LaravelS\Swoole\Task\Task;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * 客户列表.
 * @method getFollowExpire(array $where): mixed
 */
class CustomerService extends BaseService implements ClientInterface
{
    use CustomerListService;

    /**
     * CustomerService constructor.
     */
    public function __construct(CustomerDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 保存客户.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function saveCustomer(array $data, int $customType, int $force): mixed
    {
        if (! in_array($customType, [CustomerEnum::CUSTOMER_HEIGHT_SEAS, CustomerEnum::CUSTOMER_CHARGE])) {
            throw $this->exception('业务类型错误');
        }

        $uid = uuid_to_uid($this->uuId(false));

        // uncompleted customer detection
        $this->uncompletedDetection($uid);

        $label = $data['customer_label'] ?? [];

        $formService = app()->get(FormService::class);

        $attaches = [];

        // TODO:待优化
        $list = $formService->getFormDataList(CustomerEnum::CUSTOMER);
        $formService->fieldValueCheck($data, CustomerEnum::CUSTOMER, 0, $list, $force);
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
        $data['customer_no']     = $this->generateNo();
        $data['creator_uid']     = $uid;
        $data['customer_status'] = 0;
        if ($customType == CustomerEnum::CUSTOMER_CHARGE) {
            $data['uid'] = $data['creator_uid'];
        }
        $attaches = array_filter($attaches);
        return $this->transaction(function () use ($label, $data, $attaches) {
            $res = $this->dao->create($data);
            if (! $res) {
                throw $this->exception(__('common.insert.fail'));
            }

            if ($label) {
                $labelsService = app()->get(ClientLabelsService::class);
                foreach ($label as $v) {
                    $labelsService->create(['eid' => $res->id, 'label_id' => $v]);
                }
            }

            // save relation attach
            if ($attaches) {
                app()->get(AttachService::class)->update(['id' => $attaches], ['relation_id' => $res->id, 'relation_type' => 4]);
            }

            if (isset($data['customer_followed'])) {
                $status = $data['customer_followed'] < 1 ? 0 : 1;
                app()->get(ClientSubscribeService::class)->subscribe($data['creator_uid'], $res->id, $status);
            }

            // save record
            $recordRes = app()->get(CustomerRecordService::class)->create([
                'eid'            => $res->id,
                'type'           => 6,
                'creator_uid'    => $data['creator_uid'],
                'record_version' => 0,
                'reason'         => '新添加客户“' . $data['customer_name'] . '”',
            ]);
            if (! $recordRes) {
                throw $this->exception('操作记录保存失败');
            }

            return $res;
        });
    }

    /**
     * 生成客户编号.
     */
    public function generateNo(int $len = 6): string
    {
        do {
            $no = Str::random($len);
        } while ($this->exists(['customer_no' => $no]));
        return $no;
    }

    public function getSearchField()
    {
        return [
            ['statistics_type', ''],
        ];
    }

    /**
     * 修改客户.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function updateCustomer(array $data, int $id, int $force): mixed
    {
        $info = $this->dao->get($id);
        if (! $info) {
            throw $this->exception('数据获取异常');
        }

        $label = $data['customer_label'] ?? [];

        $formService = app()->get(FormService::class);

        $attaches = [];

        // TODO:待优化
        $list = $formService->getFormDataList(CustomerEnum::CUSTOMER);
        $formService->fieldValueCheck($data, CustomerEnum::CUSTOMER, $id, $list, $force);
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

        if (! $info['customer_no']) {
            $data['customer_no'] = $this->generateNo();
        }

        $uid      = $info->uid;
        $attaches = array_filter($attaches);
        unset($data['customer_status']);
        return $this->transaction(function () use ($id, $uid, $label, $data, $attaches) {
            $res = $this->dao->update($id, $data);
            if (! $res) {
                throw $this->exception(__('common.operation.fail'));
            }

            if ($label) {
                $labelsService = app()->get(ClientLabelsService::class);
                $labelsService->delete(['eid' => $id]);
                foreach ($label as $v) {
                    $labelsService->create(['eid' => $id, 'label_id' => $v]);
                }
            }

            // save relation attach
            if ($attaches) {
                app()->get(AttachService::class)->update(['id' => $attaches], ['relation_id' => $id, 'relation_type' => 4]);
            }

            if (isset($data['customer_followed'])) {
                $status = $data['customer_followed'] < 1 ? 0 : 1;
                app()->get(ClientSubscribeService::class)->subscribe($uid, $id, $status);
            }

            return $res;
        });
    }

    /**
     * 客户详情.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function getInfo(int $id, string $uuid): mixed
    {
        $info = toArray($this->dao->get($id));
        $list = app()->get(FormService::class)->getFormDataWithType(CustomerEnum::CUSTOMER, false, $this->getPlatform());
        if (! $info) {
            throw $this->exception('数据获取异常');
        }
        $attachField               = $this->getAttachField();
        $attachService             = app()->get(AttachService::class);
        $dictDataService           = app()->get(DictDataService::class);
        $info['customer_followed'] = (string) app()->get(ClientSubscribeService::class)->getSubscribeStatus(uuid_to_uid($uuid), $id);

        foreach ($list as &$item) {
            foreach ($item['data'] as &$datum) {
                if (array_key_exists($datum['key'], $info)) {
                    $type           = strtolower($datum['type']);
                    $inputType      = strtolower($datum['input_type']);
                    $datum['value'] = $this->handleFieldValue($inputType, $type, $info[$datum['key']]);
                    if ($datum['dict_ident']) {
                        if (is_dimensional_data($datum['value'])) {
                            $datum['value'] = $this->handleDictValue($datum['dict_ident'], $inputType, $datum['value']);
                        } else {
                            $datum['value'] = $dictDataService->getNamesByValue($datum['dict_ident'], $datum['value']);
                        }
                    }
                    if (in_array($inputType, ['file', 'images'])) {
                        $datum['files'] = empty($datum['value']) ? [] :
                            $attachService->getListByRelationType(AttachService::RELATION_TYPE_CLIENT, $datum['value'], $attachField);
                    }
                }

                if ($datum['key'] == 'customer_label') {
                    $datum['value'] = app()->get(ClientLabelService::class)->column(['id' => $datum['value']], 'name');
                }
            }
        }

        return [
            'customer_no'     => $info['customer_no'],
            'customer_name'   => $info['customer_name'],
            'customer_status' => $info['customer_status'],
            'salesman'        => app()->get(AdminService::class)->get(['id' => $info['uid']], ['id', 'avatar', 'name']),
            'list'            => $list,
        ];
    }

    /**
     * 客户编辑表单数据.
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
        $info['customer_followed'] = (string) app()->get(ClientSubscribeService::class)->getSubscribeStatus(uuid_to_uid($uuid), $id);

        $list = app()->get(FormService::class)->getFormDataWithType(CustomerEnum::CUSTOMER, platform: $this->getPlatform());
        foreach ($list as &$item) {
            foreach ($item['data'] as &$datum) {
                if (array_key_exists($datum['key'], $info)) {
                    $type           = strtolower($datum['type']);
                    $inputType      = strtolower($datum['input_type']);
                    $datum['value'] = $this->handleFieldValue($inputType, $type, $info[$datum['key']]);
                    if (in_array($inputType, ['file', 'images'])) {
                        $datum['files'] = empty($datum['value']) ? [] :
                            $attachService->getListByRelationType(AttachService::RELATION_TYPE_CLIENT, $datum['value'], $attachField);
                    }
                }
            }
        }

        return $list;
    }

    /**
     * 列表统计
     * @param mixed $userIds
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getListStatistics(int $customType, string $uuid, $userIds): array
    {
        $uid          = uuid_to_uid($uuid);
        $concernWhere = match ($customType) {
            CustomerEnum::CUSTOMER_CHARGE => ['eid' => $this->dao->column(['uid' => $uid], 'id')],
            default                       => [],
        };

        return [
            'total'            => $this->dao->count(['uid' => $userIds]),
            'concern'          => app()->get(ClientSubscribeService::class)->count(array_merge(['uid' => $uid, 'subscribe_status' => 1], $concernWhere)),
            'unsettled'        => $this->dao->count(['customer_status' => 0, 'uid' => $userIds]),
            'traded'           => $this->dao->count(['customer_status' => 1, 'uid' => $userIds]),
            'urgent_follow_up' => $this->dao->getUrgentFollowUpCount(['uid' => $userIds]),
            'lost'             => $this->dao->count(['customer_status' => 2, 'uid' => $userIds]),
        ];
    }

    /**
     * 下拉列表.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getSelectList(string $uuid): array
    {
        $uid   = array_merge(app()->get(RolesService::class)->getDataUids(uuid_to_uid($uuid)), [0]);
        $field = [
            'id as value',
            'customer_name as ' . ($this->getPlatform() == UserAgentEnum::ADMIN_AGENT ? ' label' : 'text'),
        ];
        return $this->dao->getList(['uid' => $uid], $field, 0, 0, 'id');
    }

    /**
     * 无需同步字段.
     * @return string[]
     */
    public function getOutOfSyncField(): array
    {
        return [];
    }

    /**
     * 流失.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function lost(array $data): bool
    {
        $list = $this->dao->select(['id' => $data, 'uid' => 0], ['id', 'uid', 'customer_status', 'return_num']);
        if ($list->isEmpty()) {
            return true;
        }

        return $this->transaction(function () use ($list) {
            $records     = $ids = [];
            $time        = now()->toDateTimeString();
            $dictService = app()->get(DictDataService::class);
            $lostName    = $dictService->getNameByValue('customer_status', '2');
            foreach ($list as $customer) {
                $status                    = $customer->customer_status;
                $customer->customer_status = 2;
                if (! $customer->save()) {
                    throw $this->exception(__('common.operation.fail'));
                }
                $ids[]      = $customer->id;
                $statusName = $dictService->getNameByValue('customer_status', $status);
                $records[]  = [
                    'eid'            => $customer->id,
                    'type'           => 3,
                    'creator_uid'    => uuid_to_uid((string) $this->uuId(false)),
                    'record_version' => $customer->return_num,
                    'reason'         => '客户状态由“' . $statusName . '”变为“' . $lostName . '”',
                    'created_at'     => $time,
                ];
            }

            // save lost record
            $recordRes = app()->get(CustomerRecordService::class)->insert($records);
            if (! $recordRes) {
                throw $this->exception('操作记录保存失败');
            }

            // clear customer subscribe status
            $ids && app()->get(ClientSubscribeService::class)->delete(['eid' => $ids]);
            Cache::tags(['client'])->flush();
            return true;
        });
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

        $dictDataService = app()->get(DictDataService::class);
        $customerLabel   = json_decode($info['customer_label'], true);
        $customerLabel   = is_array($customerLabel) ? $this->getCustomerLabelList($info['id'], $customerLabel) : [];

        $data = [
            'id'               => $info['id'],
            'uid'              => $info['uid'],
            'customer_name'    => $info['customer_name'],
            'customer_status'  => $info['customer_status'],
            'salesman'         => app()->get(AdminService::class)->get($info['uid'], ['id', 'avatar', 'name']),
            'last_follow_time' => $this->getLastFollowTime($info['id']),
            'customer_label'   => $customerLabel,
            'created_at'       => $info['created_at'],
        ];

        $list = app()->get(FormService::class)->getFormDataWithType(CustomerEnum::CUSTOMER, false, $this->getPlatform());

        $attachService = app()->get(AttachService::class);
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
                        $datum['files'] = empty($datum['value']) ? [] :
                            $attachService->getListByRelationType(AttachService::RELATION_TYPE_CLIENT, $datum['value']);
                    }
                }

                if ($datum['key'] == 'customer_label') {
                    $datum['value'] = app()->get(ClientLabelService::class)->column(['id' => $datum['value']], 'name');
                }
            }
        }
        $data['list'] = $list;
        return $data;
    }

    /**
     * 退回公海.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function returnHighSeas(array $data, string $reason): bool
    {
        if (! $reason) {
            throw $this->exception('请填写说明原因');
        }

        $list = $this->dao->select(['id' => $data, 'not_uid' => 0], ['id', 'uid', 'customer_status', 'return_num']);
        if ($list->isEmpty()) {
            return true;
        }

        $uid = uuid_to_uid((string) $this->uuId(false));
        return $this->transaction(function () use ($uid, $reason, $list) {
            $ids  = $records = [];
            $time = now()->toDateTimeString();
            foreach ($list as $customer) {
                $customer->before_uid = $customer->uid;
                $customer->uid        = 0;
                ++$customer->return_num;
                if (! $customer->save()) {
                    throw $this->exception(__('common.operation.fail'));
                }

                $ids[]     = $customer->id;
                $records[] = [
                    'eid'            => $customer->id,
                    'type'           => 1,
                    'uid'            => $customer->before_uid,
                    'creator_uid'    => $uid,
                    'record_version' => $customer->return_num,
                    'reason'         => $reason,
                    'created_at'     => $time,
                ];
            }

            // save return record
            $recordRes = app()->get(CustomerRecordService::class)->insert($records);
            if (! $recordRes) {
                throw $this->exception('操作记录保存失败');
            }

            // clear customer subscribe status
            $ids && app()->get(ClientSubscribeService::class)->delete(['eid' => $ids]);
            Cache::tags(['client'])->flush();
            return true;
        });
    }

    /**
     * 取消流失.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws \ReflectionException
     */
    public function cancelLost(int $id): bool
    {
        $info = $this->dao->get($id, ['id', 'uid', 'customer_name', 'customer_status', 'return_num']);
        if (! $info) {
            throw $this->exception('数据获取异常');
        }

        if ($info->uid !== 0) {
            throw $this->exception('客户【' . $info->customer_name . '】存在业务员, 不能进行取消流失操作');
        }
        if ($info->customer_status != 2) {
            throw $this->exception('客户【' . $info->customer_name . '】状态异常, 不能进行取消流失操作');
        }

        return $this->transaction(function () use ($id, $info) {
            $tradedNum = app()->get(ClientBillService::class)->count(['eid' => $id, 'types' => [0, 1], 'status' => 1]);
            $status    = $tradedNum > 0 ? 1 : 0;
            $res       = $this->dao->update($id, ['customer_status' => $status]);
            if (! $res) {
                throw $this->exception('客户状态更新失败');
            }

            $dictService = app()->get(DictDataService::class);
            $lostName    = $dictService->getNameByValue('customer_status', '2');
            $cancelName  = $dictService->getNameByValue('customer_status', (string) $status);

            // save cancel lost record
            $recordRes = app()->get(CustomerRecordService::class)->create([
                'eid'            => $id,
                'type'           => 4,
                'uid'            => $info->uid,
                'creator_uid'    => uuid_to_uid((string) $this->uuId(false)),
                'record_version' => $info->return_num,
                'reason'         => '客户状态由“' . $lostName . '”变为“' . $cancelName . '”',
                'created_at'     => now()->toDateTimeString(),
            ]);
            if (! $recordRes) {
                throw $this->exception('操作记录保存失败');
            }

            // clear customer subscribe status
            app()->get(ClientSubscribeService::class)->delete(['eid' => $id]);
            Cache::tags(['client'])->flush();
            return true;
        });
    }

    /**
     * 领取.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function claim(array $data, string $uuid): bool
    {
        $uid = uuid_to_uid($uuid);

        // claim customer detection
        $this->uncompletedDetection($uid, 'claim');

        $list = $this->dao->select(['id' => $data, 'uid' => 0, 'customer_status_lt' => 2], ['id', 'uid', 'customer_status', 'return_num']);
        if ($list->isEmpty()) {
            return true;
        }

        $salesman = app()->get(AdminService::class)->get($uid, ['id', 'name']);
        if (! $salesman) {
            throw $this->exception(__('common.not.exist', ['attr' => '业务员']));
        }

        $billService = app()->get(ClientBillService::class);
        return $this->transaction(function () use ($uid, $list, $billService, $salesman) {
            $ids  = $records = [];
            $time = now()->toDateTimeString();
            foreach ($list as $customer) {
                $customer->uid             = $uid;
                $customer->customer_status = $billService->count(['eid' => $customer->id, 'types' => [0, 1], 'status' => 1]) > 0 ? 1 : 0;
                if (! $customer->save()) {
                    throw $this->exception(__('common.operation.fail'));
                }

                $ids[]     = $customer->id;
                $records[] = [
                    'eid'            => $customer->id,
                    'type'           => 2,
                    'uid'            => $uid,
                    'creator_uid'    => $uid,
                    'record_version' => $customer->return_num,
                    'reason'         => '“' . $salesman->name . '”从公海领取',
                    'created_at'     => $time,
                ];
            }

            // save claim record
            $recordRes = app()->get(CustomerRecordService::class)->insert($records);
            if (! $recordRes) {
                throw $this->exception('操作记录保存失败');
            }

            // clear customer subscribe status
            $ids && app()->get(ClientSubscribeService::class)->delete(['eid' => $ids]);
            Cache::tags(['client'])->flush();
            return true;
        });
    }

    /**
     * 自动退回定时任务.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function autoReturnTimer(): void
    {
        $returnEvents = ['unsettled_cycle', 'unfollowed_cycle'];
        foreach ($returnEvents as $event) {
            $cycle = (int) sys_config($event, 0);
            if ($cycle < 1) {
                continue;
            }

            match ($event) {
                'unsettled_cycle'  => $this->uncompletedAutoReturn($cycle),
                'unfollowed_cycle' => $this->unfollowedAutoReturn($cycle),
                'default'          => '',
            };
        }
    }

    /**
     * 客户未成交退回提醒定时任务.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function uncompletedReturnRemindTimer(): void
    {
        $reminderCycle = (int) sys_config('advance_cycle');
        if ($reminderCycle < 1) {
            return;
        }

        $unsettedCycle = (int) sys_config('unsettled_cycle');
        if ($reminderCycle >= $unsettedCycle) {
            Cache::tags([CacheEnum::TAG_CONFIG])->remember(md5('unsettled_return_remind'), 864000, function () {
                Log::error('客户未成交提醒异常,请检查提醒/退回周期设置');
            });
            return;
        }

        $list = $this->getReturnCycleList(['not_uid' => 0, 'customer_status' => '0']);
        if ($list->isEmpty()) {
            return;
        }

        $recordService = app()->get(CustomerRecordService::class);
        $noticeService = app()->get(NoticeRecordService::class);

        $now         = now();
        $type        = 'unsettled_return_remind';
        $timeFormat  = 'Y/m/d H:i:s';
        $remindTime  = $now->format('H:i');
        $startTime   = (clone $now)->endOfDay()->subDays($unsettedCycle)->startOfDay();
        $endTime     = (clone $now)->endOfDay()->subDays($unsettedCycle - $reminderCycle);
        $surplus     = $unsettedCycle - $reminderCycle;
        $noticeWhere = [
            'template_type' => $type,
            'time'          => $startTime->format($timeFormat) . '-' . (clone $now)->endOfDay()->format($timeFormat),
        ];

        foreach ($list as $customer) {
            $recordWhere = ['type' => [2, 5], 'eid' => $customer->id, 'record_version' => $customer->return_num];
            $record      = $recordService->get($recordWhere, ['id', 'created_at'], sort: 'created_at');
            if ($endTime->gt($record ? $record->created_at : $customer->created_at)
                && ! $noticeService->count(array_merge(['link_id' => $customer->id], $noticeWhere))) {
                $this->sendRemindMessage($customer, $remindTime, $type, $surplus);
            }
        }
    }

    /**
     * 客户未跟进退回提醒定时任务.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function unfollowedReturnRemindTimer(): void
    {
        $reminderCycle = (int) sys_config('advance_cycle');
        if ($reminderCycle < 1) {
            return;
        }

        $unFollowCycle = (int) sys_config('unfollowed_cycle');
        if ($reminderCycle >= $unFollowCycle) {
            Log::error('客户未跟进提醒异常,请检查提醒/跟进周期设置');
            return;
        }

        $list = $this->getReturnCycleList(['not_uid' => 0]);
        if ($list->isEmpty()) {
            return;
        }

        $noticeService = app()->get(NoticeRecordService::class);
        $followService = app()->get(ClientFollowService::class);
        $recordService = app()->get(CustomerRecordService::class);

        $now         = now();
        $type        = 'unfollowed_return_remind';
        $timeFormat  = 'Y/m/d H:i:s';
        $remindTime  = $now->format('H:i');
        $startTime   = (clone $now)->endOfDay()->subDays($unFollowCycle)->startOfDay();
        $endTime     = (clone $now)->endOfDay()->subDays($unFollowCycle - $reminderCycle);
        $surplus     = $unFollowCycle - $reminderCycle;
        $noticeWhere = [
            'template_type' => $type,
            'time'          => $startTime->format($timeFormat) . '-' . (clone $now)->endOfDay()->format($timeFormat),
        ];

        foreach ($list as $customer) {
            $compTime    = null;
            $followWhere = ['types' => 0, 'eid' => $customer->id, 'follow_version' => $customer->return_num];
            $follow      = $followService->get($followWhere, ['id', 'created_at'], sort: 'created_at');
            if ($follow) {
                $compTime = $follow->created_at;
            } else {
                $recordWhere = ['type' => [2, 5], 'eid' => $customer->id, 'record_version' => $customer->return_num];
                $record      = $recordService->get($recordWhere, ['id', 'created_at'], sort: 'created_at');
                if ($record) {
                    $compTime = $record->created_at;
                }
            }

            if ($endTime->gt($compTime ?: $customer->created_at)
                && ! $noticeService->count(array_merge(['link_id' => $customer->id], $noticeWhere))) {
                $this->sendRemindMessage($customer, $remindTime, $type, $surplus);
            }
        }
    }

    /**
     * 删除.
     * @param mixed $id
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function deleteCustomer($id, string $uuid): int
    {
        if (app()->get(ClientBillService::class)->count(['eid' => $id, 'entid' => 1, 'status' => 1])) {
            throw $this->exception('当前客户存在审核通过的回款、续费数据, 不能删除');
        }

        if (app()->get(ContractService::class)->count(['eid' => $id])) {
            throw $this->exception('当前客户存在合同数据, 不能删除');
        }

        if (app()->get(ClientInvoiceService::class)->count(['eid' => $id])) {
            throw $this->exception('当前客户存在发票数据, 不能删除');
        }

        return $this->transaction(function () use ($id) {
            $res = $this->dao->delete($id);
            if (! $res) {
                throw $this->exception(__('common.delete.fail'));
            }
            Task::deliver(new StatusChangeTask(ClientEnum::CLIENT_DELETE_NOTICE, ClientEnum::CLIENT_DELETE, 1, (int) $id));
            app()->get(ScheduleInterface::class)->delScheduleByLinkId((int) $id, [ScheduleEnum::TYPE_CLIENT_TRACK]);

            // clear customer subscribe status
            app()->get(ClientSubscribeService::class)->delete(['eid' => $id]);
            return $res;
        });
    }

    /**
     * 获取业务员列表.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function getSalesman(string $uuid): array
    {
        $userIds = app()->get(RolesService::class)->getDataUids(uuid_to_uid($uuid));
        if (! $userIds) {
            return [];
        }
        return toArray(app()->get(AdminService::class)->select(['id' => $userIds, 'status' => 1], ['id', 'name']));
    }

    /**
     * 批量修改标签.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function label(array $data, array $label): bool
    {
        if (! $data) {
            return true;
        }
        $labelsService = app()->get(ClientLabelsService::class);
        if ($label) {
            return $this->transaction(function () use ($data, $label, $labelsService) {
                $update = ['customer_label' => json_encode($label)];
                if (count($data) > 1) {
                    foreach ($data as $id) {
                        $this->dao->update($id, $update);
                        foreach ($label as $v) {
                            if (! $labelsService->exists(['eid' => $id, 'label_id' => $v])) {
                                $labelsService->create(['eid' => $id, 'label_id' => $v]);
                            }
                        }
                    }
                } else {
                    $this->dao->update($data[0], $update);
                    $labelsService->delete(['eid' => $data]);
                    foreach ($label as $v) {
                        $labelsService->create(['eid' => $data[0], 'label_id' => $v]);
                    }
                }
                return true;
            });
        }
        return true;
    }

    /**
     *  转移.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function shift(array $data, int $toUid, int $invoice = 0, int $contract = 0): mixed
    {
        if (! $toUid) {
            throw $this->exception(__('common.empty.attr', ['attr' => '转移人ID']));
        }

        $list = $this->dao->select(['id' => $data, 'customer_status_lt' => 2], ['id', 'uid', 'customer_status', 'return_num']);
        if ($list->isEmpty()) {
            return true;
        }

        $adminService = app()->get(AdminService::class);
        $salesman     = $adminService->get($toUid, ['id', 'name']);
        if (! $salesman) {
            throw $this->exception('交接人员不存在');
        }
        $billService = app()->get(ClientBillService::class);
        $uid         = uuid_to_uid($this->uuId(false));
        $res         = $this->transaction(function () use ($data, $toUid, $invoice, $contract, $uid, $salesman, $adminService, $billService, $list) {
            $records = [];
            $nowTime = now()->toDateTimeString();
            foreach ($list as $customer) {
                if ($customer->uid < 1) {
                    $reason = '此客户从公海移交给“' . $salesman->name . '”负责';
                } else {
                    $beforeSalesman = $adminService->get($customer->uid, ['id', 'name']);
                    $reason         = '此客户从“' . $beforeSalesman?->name . '”负责移交给“' . $salesman->name . '”负责';
                }
                $customer->before_uid      = $customer->uid;
                $customer->uid             = $toUid;
                $customer->customer_status = $billService->count(['eid' => $customer->id, 'types' => [0, 1], 'status' => 1]) > 0 ? 1 : 0;
                if (! $customer->save()) {
                    throw $this->exception(__('common.operation.fail'));
                }

                $records[] = [
                    'eid'            => $customer->id,
                    'type'           => 5,
                    'reason'         => $reason,
                    'record_version' => $customer->return_num,
                    'created_at'     => $nowTime,
                    'uid'            => $toUid,
                    'creator_uid'    => $uid,
                ];
            }
            if (! $records) {
                return true;
            }
            // save return record
            if (! app()->get(CustomerRecordService::class)->insert($records)) {
                throw $this->exception('未跟进自动退回异常,操作记录保存失败');
            }
            // clear customer subscribe status
            app()->get(ClientSubscribeService::class)->delete(['eid' => $data]);
            // 转移合同
            if ($contract) {
                app()->get(ContractService::class)->search(['eid' => $data])->update(['uid' => $toUid]);
            }
            // 转移发票
            if ($invoice) {
                app()->get(ClientInvoiceService::class)->search(['eid' => $data])->update(['uid' => $toUid]);
            }
            // 转移联系人
            app()->get(CustomerLiaisonService::class)->search(['eid' => $data])->update(['uid' => $toUid]);

            return true;
        });
        return $res && Cache::tags([CacheEnum::TAG_CUSTOMER])->flush();
    }

    /**
     * 本人所属客户下拉列表.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getCurrentSelect(): array
    {
        $field = [
            'id as value',
            'customer_name as ' . ($this->getPlatform() == UserAgentEnum::ADMIN_AGENT ? ' label' : 'text'),
        ];
        return $this->dao->getList(['uid' => uuid_to_uid($this->uuId(false))], $field, 0, 0, 'id');
    }

    /**
     * 新增客户统计
     */
    public function getRingRatioCount(string $searchTime, array $userIds, string $ratioTime = ''): array
    {
        $ratio = 0;
        $count = $this->count(['time' => $searchTime, 'uid' => $userIds]);
        if (! $ratioTime) {
            return compact('count', 'ratio');
        }
        $ratioCount = $this->count(['time' => $ratioTime, 'uid' => $userIds]);
        $ratio      = Statistics::ringRatio($count, $ratioCount);
        return compact('count', 'ratio');
    }

    /**
     * 业绩统计
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function getStatistics(string $time, array $userIds, array $categoryIds = []): array
    {
        [$searchTime, $ratioTime] = Date::ringRatioTime($time);
        $billService              = app()->get(ClientBillService::class);
        return array_merge(
            ['new_customer' => $this->getRingRatioCount($searchTime, $userIds, $ratioTime)],
            $billService->performanceStatistics($searchTime, $userIds, $ratioTime, $categoryIds),
            app()->get(ContractService::class)->performanceStatistics($searchTime, $userIds, $ratioTime, $categoryIds)
        );
    }

    /**
     * 获取用户客户数据量.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function getUserClientData(string $uuid): array
    {
        return app()->get(ClientBillService::class)->getIncome(['uid' => uuid_to_uid($uuid), 'types' => -1]);
    }

    /**
     * 保单验证
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function uncompletedDetection(int $uid, string $detection = 'insert'): void
    {
        $switch       = (int) sys_config('client_policy_switch');
        $clientNumber = (int) sys_config('unsettled_client_number');
        if ($switch < 1 || $clientNumber < 1) {
            return;
        }

        if ($clientNumber <= $this->dao->count(['uid' => $uid, 'customer_status' => 0])) {
            throw $this->exception(match ($detection) {
                'claim' => '到达保单数量，业务员不能进行公海客户领取',
                default => '您未成交的客户数量已达系统设置的保单数量，请先将其他客户退回公海！'
            });
        }
    }

    /**
     * 跟进提醒定时任务.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function followUpRemindTimer(): void
    {
        $followUpStatus = (array) sys_config('follow_up_status', []);
        if (empty($followUpStatus)) {
            return;
        }

        $now            = now();
        $followUpStatus = array_map('intval', $followUpStatus);
        $followType     = [1 => 'follow_up_traded', 2 => 'follow_up_unsettled'];
        $remindType     = [1 => 'traded_follow_up_remind', 2 => 'unsettled_follow_up_remind'];
        foreach ($followUpStatus as $status) {
            $cycle = (int) sys_config($followType[$status] ?? '', 0);
            if ($cycle < 1) {
                return;
            }

            $this->followUpRemind($cycle, $status, $remindType[$status], $now);
        }
    }

    /**
     * 获取用户部门数据量.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function getUserFrameData(string $uuid, array $userIds): array
    {
        $data    = ['auth' => 1, 'income' => '0', 'today_income' => '0', 'yesterday_income' => '0'];
        $userId  = uuid_to_uid($uuid);
        $isAdmin = app()->get(FrameAssistService::class)->exists(['user_id' => $userId, 'is_admin' => 1]) ?: 0;
        $cardId  = uuid_to_card_id($uuid);
        // 管理权限
        if ($isAdmin < 1 && app()->get(UserScopeService::class)->count(['uid' => $cardId, 'entid' => 1]) < 1) {
            $data['auth'] = 0;
            return $data;
        }

        // 收入
        return app()->get(ClientBillService::class)->getIncome(['uid' => $userIds, 'types' => -1]);
    }

    /**
     * 业绩排行.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function getRanking(string $time, array $userIds, array $categoryIds = []): array
    {
        [$searchTime, $ratioTime] = Date::ringRatioTime($time);
        $categoryIds              = app()->get(ContractService::class)->getStatisticsCategoryIds($categoryIds);
        $where                    = ['date' => $searchTime, 'entid' => 1, 'uid' => $userIds, 'contract_category' => $categoryIds, 'types' => [0, 1]];
        return app()->get(ClientBillService::class)->getRankList($where);
    }

    /**
     * 合同类型分析统计
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function getContractRankWithNotRatio(string $time, array $userIds, int $categoryId): array
    {
        [$searchTime, $ratioTime] = Date::ringRatioTime($time);
        return app()->get(ContractService::class)->getCategoryRank($searchTime, $userIds, [], $categoryId);
    }

    /**
     * 获取绩效考核筛选用户数据.
     * @param array $member
     * @return array|mixed
     * @throws \ReflectionException
     * @throws BindingResolutionException
     */
    public function getIdsByType(int $uid, mixed $member, int $type, int $entId = 1): mixed
    {
        $userIds = [];
        $member  = array_filter(is_string($member) ? explode(',', $member) : $member);
        switch ($type) {
            case 1:
                $userIds[] = $uid;
                break;
            case 2:
                $userIds = app()->get(FrameService::class)->getFrameSubUids($uid);
                break;
            case 3:
                $userIds = app()->get(FrameService::class)->getIdsByFrameIds($uid, $member);
                break;
            case 4:
                $userIds = $member;
                break;
            default:
                $userIds = app()->get(FrameService::class)->subUserInfo($uid, $entId, false, false, withAdmin: true, withSelf: true);
        }
        return $userIds;
    }

    /**
     * 业绩简报统计
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getBriefStatistics(string $time, array $userIds, int $entId = 1): array
    {
        [$searchTime, $ratioTime] = Date::ringRatioTime($time);
        $billService              = app()->get(ClientBillService::class);
        $contractService          = app()->get(ContractService::class);
        return [
            'income'             => sprintf('%.2f', $billService->getSum(['date' => $searchTime, 'entid' => $entId, 'uid' => $userIds, 'types' => -1])),
            'renew'              => sprintf('%.2f', $billService->getSum(['date' => $searchTime, 'entid' => $entId, 'uid' => $userIds, 'types' => 1])),
            'new_customer'       => $this->count(['time' => $searchTime, 'uid' => $userIds]),
            'new_contract'       => $contractService->count(['start_date' => $searchTime, 'uid' => $userIds]),
            'new_contract_price' => sprintf('%.2f', $contractService->sum(['start_date' => $searchTime, 'uid' => $userIds], 'contract_price')),
            'uncollected_price'  => sprintf('%.2f', $contractService->sum(['uid' => $userIds, 'pay_status' => 0, 'signing_status_lt' => 2], 'surplus')),
        ];
    }

    /**
     * 业务员业绩排行.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function getSalesmanRank(string $time, array $userIds, int $entId = 1): array
    {
        [$searchTime, $ratioTime] = Date::ringRatioTime($time);
        return app()->get(ClientBillService::class)->getRankList(['date' => $searchTime, 'entid' => $entId, 'uid' => $userIds, 'types' => [0, 1]]);
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
        $fieldMap = $fieldNameKeyMap = [];
        $fields   = app()->get(FormService::class)->getExportField(CustomerEnum::CUSTOMER);
        foreach ($fields as $field) {
            if ($field['key'] != 'customer_label') {
                $fieldMap[$field['key']] = $field;
            }
            $fieldNameKeyMap[$field['key_name']] = $field['key'];
        }

        $adminService = app()->get(AdminService::class);

        // 业务员
        $salesmanMap = $adminService->column(['id' => $uids, 'name_eq' => array_column($data, '业务员')], 'id', 'name');
        return $this->transaction(function () use ($data, $fieldNameKeyMap, $fieldMap, $salesmanMap, $uid) {
            foreach ($data as $customer) {
                $insert   = [];
                $isCreate = false;
                foreach ($customer as $key => $item) {
                    if (! isset($fieldNameKeyMap[$key])) {
                        if ($key == '业务员') {
                            if ($item == '') {
                                $insert['uid']             = 0;
                                $insert['customer_status'] = 0;
                            } else {
                                $insert['uid'] = $salesmanMap[$item] ?? $uid;
                            }
                        } elseif ($key == '客户编号') {
                            $insert['customer_no'] = $item;
                        }
                        continue;
                    }

                    $value     = $item;
                    $field     = $fieldNameKeyMap[$key];
                    $formField = $fieldMap[$field] ?? [];

                    // 标签
                    if ($field == 'customer_label') {
                        $value = app()->get(ClientLabelService::class)->getIdsByName(array_filter(explode('/', $item)));
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

                    // 状态
                    if ($field == 'customer_status' && is_array($value)) {
                        $value = $value[0] ?? 0;
                    }
                    $insert[$field] = $value;
                }
                // 编号不符合
                if (mb_strlen($insert['customer_no']) > 6) {
                    $insert['customer_no'] = '';
                }

                // 不存在 || 找不到则新增
                if (! isset($insert['customer_no'])
                    || ! $insert['customer_no']
                    || ! $this->dao->exists(['customer_no' => $insert['customer_no']])
                ) {
                    $isCreate = true;
                    if (! $insert['customer_no']) {
                        $insert['customer_no'] = $this->generateNo();
                    }
                    $insert['creator_uid'] = $uid;
                }

                $label    = $insert['customer_label'] ?? [];
                $followed = $insert['customer_followed'] ?? false;
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

                    if ($label) {
                        $labelsService = app()->get(ClientLabelsService::class);
                        foreach ($label as $v) {
                            $labelsService->create(['eid' => $res->id, 'label_id' => $v]);
                        }
                    }

                    if ($followed !== false) {
                        app()->get(ClientSubscribeService::class)->subscribe($uid, $res->id, $followed < 1 ? 0 : 1);
                    }
                } else {
                    if (isset($insert['customer_status'])) {
                        unset($insert['customer_status']);
                    }
                    $info = $this->dao->get(['customer_no' => $insert['customer_no']], ['id']);
                    $res  = $this->dao->update($info->id, $insert);
                    if (! $res) {
                        throw $this->exception(__('common.operation.fail'));
                    }

                    if ($label) {
                        $labelsService = app()->get(ClientLabelsService::class);
                        $labelsService->delete(['eid' => $info->id]);
                        foreach ($label as $v) {
                            $labelsService->create(['eid' => $info->id, 'label_id' => $v]);
                        }
                    }

                    if ($followed !== false) {
                        app()->get(ClientSubscribeService::class)->subscribe($uid, $info->id, $followed < 1 ? 0 : 1);
                    }
                }
            }
            return true;
        });
    }

    /**
     * 获取客户列表.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    private function getReturnCycleList(array $where): mixed
    {
        return Cache::tags(['client'])->remember(md5(json_encode($where)), 3600, function () use ($where) {
            return $this->dao->select($where, ['id', 'uid', 'customer_name', 'customer_status', 'return_num', 'created_at'], ['salesman']);
        });
    }

    /**
     * 未成交自动退回.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    private function uncompletedAutoReturn(int $cycle): void
    {
        $list = $this->getReturnCycleList(['not_uid' => 0, 'customer_status' => '0']);
        if ($list->isEmpty()) {
            return;
        }

        $now     = now();
        $nowTime = $now->toDateTimeString();
        $endTime = (clone $now)->endOfDay()->subDays($cycle);
        $this->transaction(function () use ($list, $endTime, $nowTime) {
            $records       = $ids = [];
            $recordService = app()->get(CustomerRecordService::class);
            foreach ($list as $customer) {
                $recordWhere = ['type' => [2, 5], 'eid' => $customer->id, 'record_version' => $customer->return_num];
                $record      = $recordService->get($recordWhere, ['id', 'created_at'], sort: 'created_at');
                if ($endTime->gt($record ? $record->created_at : $customer->created_at)) {
                    $customer->uid = 0;
                    ++$customer->return_num;
                    if (! $customer->save()) {
                        throw $this->exception('未成交自动退回异常,客户状态修改失败');
                    }
                    $ids[]     = $customer->uid;
                    $records[] = [
                        'eid'            => $customer->id,
                        'type'           => 1,
                        'reason'         => '未及时成交，系统自动退回公海',
                        'record_version' => $customer->return_num,
                        'created_at'     => $nowTime,
                    ];
                }
            }

            if (! $records) {
                return;
            }

            // save return record
            if (! app()->get(CustomerRecordService::class)->insert($records)) {
                throw $this->exception('未成交自动退回异常,操作记录保存失败');
            }

            // clear customer subscribe status
            $ids && app()->get(ClientSubscribeService::class)->delete(['eid' => $ids]);
            Cache::tags(['client'])->flush();
        });
    }

    /**
     * 未跟进自动退回.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    private function unfollowedAutoReturn(int $cycle): void
    {
        $list = $this->getReturnCycleList(['not_uid' => 0]);
        if ($list->isEmpty()) {
            return;
        }

        $now     = now();
        $nowTime = $now->toDateTimeString();
        $endTime = (clone $now)->endOfDay()->subDays($cycle);
        $this->transaction(function () use ($list, $endTime, $nowTime) {
            $records       = $ids = [];
            $recordService = app()->get(CustomerRecordService::class);
            $followService = app()->get(ClientFollowService::class);
            foreach ($list as $customer) {
                $compTime    = null;
                $followWhere = ['types' => 0, 'eid' => $customer->id, 'follow_version' => $customer->return_num];
                $follow      = $followService->get($followWhere, ['id', 'created_at'], sort: 'created_at');
                if ($follow) {
                    $compTime = $follow->created_at;
                } else {
                    $recordWhere = ['type' => [2, 5], 'eid' => $customer->id, 'record_version' => $customer->return_num];
                    $record      = $recordService->get($recordWhere, ['id', 'created_at'], sort: 'created_at');
                    if ($record) {
                        $compTime = $record->created_at;
                    }
                }

                if ($endTime->gt($compTime ?: $customer->created_at)) {
                    $customer->uid = 0;
                    ++$customer->return_num;
                    if (! $customer->save()) {
                        throw $this->exception('未跟进自动退回异常,客户状态修改失败');
                    }
                    $ids[]     = $customer->id;
                    $records[] = [
                        'eid'            => $customer->id,
                        'type'           => 1,
                        'reason'         => '未及时跟进，系统自动退回公海',
                        'record_version' => $customer->return_num,
                        'created_at'     => $nowTime,
                    ];
                }
            }

            if (! $records) {
                return;
            }

            // save return record
            if (! app()->get(CustomerRecordService::class)->insert($records)) {
                throw $this->exception('未跟进自动退回异常,操作记录保存失败');
            }

            // clear customer subscribe status
            $ids && app()->get(ClientSubscribeService::class)->delete(['eid' => $ids]);
            Cache::tags(['client'])->flush();
        });
    }

    /**
     * 退回提醒.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    private function sendRemindMessage(mixed $customer, string $remindTime, string $type, int $surplus = 0): void
    {
        $message = app()->get(MessageService::class)->getMessageContent(1, $type);
        if (! $message['template_time'] || ! $message['remind_time'] || $remindTime != $message['remind_time']) {
            return;
        }

        $task = new MessageSendTask(
            entid: 1,
            i: 1,
            type: $type,
            toUid: ['to_uid' => $customer->uid, 'phone' => $customer->phone ?? ''],
            params: array_merge(['客户名称' => $customer->customer_name], $surplus > 0 ? ['剩余天数' => $surplus] : []),
            other: ['id' => $customer->id, 'phone' => $customer->salesman?->phone ?? ''],
            linkId: $customer->id,
        );
        Task::deliver($task);
    }

    /**
     * 跟进规则提醒.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    private function followUpRemind(int $cycle, int $status, string $type, mixed $nowObj): void
    {
        $list = $this->getReturnCycleList(['not_uid' => 0, 'customer_status' => $status == 1 ? '1' : '0']);
        if ($list->isEmpty()) {
            return;
        }

        $noticeService = app()->get(NoticeRecordService::class);

        $remindTime = $nowObj->format('H:i');
        $nowTime    = (clone $nowObj)->startOfDay();
        $typeWhere  = ['template_type' => $type];
        foreach ($list as $customer) {
            $notice = $noticeService->get(array_merge(['link_id' => $customer->id], $typeWhere), ['created_at'], sort: 'created_at');
            if (($notice ? $notice->created_at : $customer->created_at)->endOfDay()->addDays($cycle)->gt($nowTime)) {
                continue;
            }
            $this->sendRemindMessage($customer, $remindTime, $type);
        }
    }
}
