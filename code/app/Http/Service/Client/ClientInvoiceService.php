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
use App\Http\Contract\Client\ClientInvoiceInterface;
use App\Http\Dao\Client\ClientInvoiceDao;
use App\Http\Model\BaseModel;
use App\Http\Service\Approve\ApproveApplyService;
use App\Http\Service\Approve\ApproveService;
use App\Http\Service\Attach\AttachService;
use App\Http\Service\BaseService;
use App\Http\Service\Frame\FrameService;
use App\Http\Service\Open\OpenapiRuleService;
use App\Http\Service\System\RolesService;
use App\Mail\ClientInvoice;
use App\Task\customer\InvoiceRecordTask;
use App\Task\message\ContractInvoiceRemind;
use App\Task\message\InvoiceCancelJob;
use App\Task\message\StatusChangeTask;
use crmeb\exceptions\ApiRequestException;
use crmeb\services\FormService;
use crmeb\services\SmsService;
use crmeb\traits\service\ResourceServiceTrait;
use Crmeb\Yihaotong\Enum\InvoiceEnum;
use GuzzleHttp\Exception\GuzzleException;
use Hhxsv5\LaravelS\Swoole\Task\Task;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Concerns\BuildsQueries;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * 客户发票
 * Class ClientInvoiceService.
 * @method search($where, ?bool $authWhere = null)
 */
class ClientInvoiceService extends BaseService implements ClientInvoiceInterface
{
    use ResourceServiceTrait;

    public function __construct(ClientInvoiceDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取发票类型名称.
     */
    public function getTypesName(int $types): string
    {
        return match ($types) {
            2       => '企业普通发票',
            3       => '企业专用发票',
            default => '个人普通发票',
        };
    }

    /**
     * 财务查看发票列表.
     * @return array
     */
    public function listForFinance(array $where, array $field = ['*'], string $sort = 'created_at', array $with = ['card', 'treaty', 'client', 'attachs', 'category'])
    {
        if (isset($where['status'])) {
            if (! $where['status']) {
                $where['status'] = [1, -1, 4, 5];
            } else {
                $where['status'] = is_string($where['status']) ? array_intersect([$where['status']], [1, -1, 4, 5]) : array_intersect($where['status'], [1, -1, 4, 5]);
            }
        }
        return parent::getList($where, $field, $sort, $with);
    }

    /**
     * 获取列表.
     * @param array|string[] $field
     * @param null $sort
     */
    public function getList(array $where, array $field = ['*'], $sort = 'created_at', array $with = ['card', 'treaty', 'client', 'attachs', 'category']): array
    {
        if (isset($where['time_data']) && in_array($where['time_field'], ['time', 'real_date'])) {
            $where[$where['time_field']] = $where['time_data'];
        }
        unset($where['time_field'], $where['time_data']);
        return parent::getList($where, $field, $sort, $with);
    }

    /**
     * 获取关联付款单列表.
     * @param mixed $id
     * @return array
     * @throws BindingResolutionException
     * @throws \ReflectionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getBillList($id)
    {
        $linkBill = $this->dao->value($id, 'link_bill');
        if (! $linkBill) {
            return [];
        }
        $linkBill = json_decode($linkBill, true);
        return app()->get(ClientBillService::class)->getList(['id' => $linkBill]);
    }

    /**
     * 保存记录.
     * @return BaseModel|Model
     * @throws BindingResolutionException
     */
    public function resourceSave(array $data)
    {
        $uuid        = (string) $this->uuId(false);
        $data['uid'] = $data['creator'] = app()->get(FrameService::class)->uuidToUid($uuid);

        if ($data['collect_type'] != 'mail' && ! $data['collect_name']) {
            throw $this->exception('请填写邮寄联系人');
        }

        /** @var ClientInvoiceCategoryService $categoryService */
        $categoryService = app()->get(ClientInvoiceCategoryService::class);
        if (! $categoryService->dao->exists(['id' => $data['category_id']])) {
            throw $this->exception('发票类目异常, 请重新选择');
        }

        $entid = 1;

        /** @var ClientBillService $billService */
        $billService   = app()->get(ClientBillService::class);
        $bilId         = $data['bill_id'] ?: [];
        $data['price'] = $this->getPrice($bilId, $billService, $entid, (int) $data['cid']);
        unset($data['bill_id']);

        if (is_array($data['address'])) {
            $data['address'] = implode('', $data['address']);
        }
        // 开具发票申请
        return $this->transaction(function () use ($entid, $data, $bilId, $billService, $uuid) {
            $data['entid']  = $entid;
            $data['unique'] = uniqid();
            $res            = $this->dao->create($data);
            if (! $res) {
                throw $this->exception(__('common.operation.fail'));
            }

            if ($bilId && ! $billService->update(['id' => $bilId, 'entid' => $entid], ['invoice_id' => $res->id])) {
                throw $this->exception(__('common.operation.fail'));
            }
            Task::deliver(new ContractInvoiceRemind($entid, $res->load(['treaty', 'card', 'client'])->toArray()));
            Task::deliver(new InvoiceRecordTask($entid, $res->id, uuid_to_uid($uuid)));
            return $res;
        });
    }

    /**
     * 修改记录.
     * @param mixed $id
     * @return int
     * @throws \ReflectionException
     * @throws BindingResolutionException
     */
    public function resourceUpdate($id, array $data)
    {
        if ($data['collect_type'] != 'mail' && ! $data['collect_name']) {
            throw $this->exception('请填写邮寄联系人');
        }

        if (isset($data['eid'])) {
            unset($data['eid']);
        }

        $info = $this->dao->get($id, ['*'], ['treaty', 'card', 'client', 'clientBill', 'category']);
        if (! $info) {
            throw $this->exception(__('common.operation.noExists'));
        }
        if ($info->status == 1) {
            throw $this->exception(__('common.operation.noPermission'));
        }

        if ($info->category_id != $data['category_id']) {
            /** @var ClientInvoiceCategoryService $categoryService */
            $categoryService = app()->get(ClientInvoiceCategoryService::class);
            if (! $categoryService->dao->exists(['id' => $data['category_id']])) {
                throw $this->exception('发票类目异常, 请重新选择');
            }
        }

        /** @var ClientBillService $billService */
        $billService = app()->get(ClientBillService::class);

        $data['status'] = 0;
        $bilId          = $data['bill_id'] ?: [];
        $entid          = 1;
        if ($bilId || isset($data['cid'])) {
            $data['price'] = $this->getPrice($bilId, $billService, $entid, (int) $data['cid']);
        }
        unset($data['bill_id']);

        return $this->transaction(function () use ($id, $entid, $data, $bilId, $billService, $info) {
            $res = $this->dao->update($id, $data);
            if (! $res) {
                throw $this->exception(__('common.operation.fail'));
            }

            if ($bilId && ! $billService->update(['id' => $bilId, 'entid' => $entid], ['invoice_id' => $id])) {
                throw $this->exception(__('common.operation.fail'));
            }

            Task::deliver(new InvoiceRecordTask($entid, $id, $this->uuId(false), 7, $info->toArray()));
            $info->amount = $data['amount'];
            $info->title  = $data['title'];
            $info->ident  = $data['ident'];
            $info->types  = $data['types'];
            Task::deliver(new ContractInvoiceRemind($entid, $info->toArray()));
            return $res;
        });
    }

    /**
     * 删除记录.
     * @param mixed $id
     * @return int
     * @throws \ReflectionException
     * @throws BindingResolutionException
     */
    public function resourceDelete($id, ?string $key = null)
    {
        if (! $id) {
            throw $this->exception(__('common.empty.attrs'));
        }
        if (strpos(',', $id) !== false) {
            $id = explode(',', $id);
            foreach ($id as $k => $v) {
                $info = $this->checkAuth($id[$k]);
                if ($info->status == 1) {
                    unset($id[$k]);
                }
            }
            if (! count($id)) {
                throw $this->exception(__('common.operation.noExists'));
            }
        } else {
            $info = $this->checkAuth($id);
            if ($info->status == 1) {
                throw $this->exception(__('common.operation.noPermission'));
            }
        }
        return $this->dao->search(['id' => $id])->delete();
    }

    /**
     * 财务审核.(1.4弃用).
     * @return mixed
     * @throws \ReflectionException
     * @throws BindingResolutionException
     */
    public function resourceStatusUpdate(int $id, array $data, int $status)
    {
        if (! in_array($status, [1, 2])) {
            throw $this->exception('参数错误');
        }
        $attachIds = [];
        if ($status == 1) {
            $attachIds         = $data['attach_ids'];
            $data['real_date'] = now()->toDateString();
            $data['status']    = 5;
        } elseif (! $data['remark']) {
            throw $this->exception(__('common.empty.attr', ['attr' => '未通过原因']));
        }

        $info = $this->dao->get($id);
        if (! $info) {
            throw $this->exception(__('common.operation.noExists'));
        }

        $entId  = 1;
        $isSend = $data['is_send'] ?? 0;
        unset($data['attach_ids'], $data['is_send']);
        $res = $this->transaction(function () use ($id, $data, $attachIds) {
            if ($attachIds) {
                app()->get(AttachService::class)->saveRelation($attachIds, $this->uuId(false), (int) $id, AttachService::RELATION_TYPE_INVOICE);
            }
            return $this->dao->update(['id' => $id], $data);
        });
        //        if ($res && $isSend) $this->sendMail($id);
        $info->remark = $data['remark'];
        if ($status == 1) {
            // 发票已开具提醒
            //            event('finance.invoiceOpen.remind', [$entId, $isSend, $info->load(['treaty', 'client', 'card'])->toArray()]);
        }
        // 发票未开具提醒
        //            event('finance.invoiceClose.remind', [$entId, $isSend, $info->load(['treaty', 'client', 'card'])->toArray()]);

        if ($res) {
            Task::deliver(new InvoiceRecordTask($entId, $id, (int) $info->uid, $status, $data));
        }
        return $res;
    }

    /**
     * 修改备注信息.
     * @param mixed $id
     * @param mixed $mark
     * @return bool
     * @throws \ReflectionException
     * @throws BindingResolutionException
     */
    public function setMark($id, $mark)
    {
        $info = $this->dao->get($id);
        if (! $info) {
            throw $this->exception(__('common.operation.noExists'));
        }
        $info->mark = $mark;
        return $info->save();
    }

    /**
     * 获取开票金额.
     * @throws BindingResolutionException
     */
    public function getPrice(array $bilId, ClientBillService $billService, int $entId, int $cid): string
    {
        $price = '0.00';
        if ($bilId) {
            $price = $billService->getInvoicePrice($bilId, $entId);
        } elseif ($cid) {
            $contractService = app()->get(ContractService::class);
            $contract        = $contractService->get($cid, ['id', 'price']);
            if ($contract) {
                $price = $contract->price;
            }
        }
        return $price;
    }

    /**
     * 检查权限.
     * @param mixed $id
     * @return BaseModel|BuildsQueries|mixed|Model|object
     * @throws \ReflectionException
     * @throws BindingResolutionException
     */
    public function checkAuth($id)
    {
        /** @var FrameService $frameService */
        $frameService = app()->get(FrameService::class);
        $uid          = $frameService->uuidToUid((string) $this->uuId(false));
        $uids         = $frameService->getLevelSub($uid);
        $info         = $this->dao->get($id);
        if (! $info) {
            throw $this->exception(__('common.operation.noExists'));
        }
        if ($info->uid != $uid && ! in_array($info->uid, $uids)) {
            throw $this->exception('common.operation.noPermission');
        }
        return $info;
    }

    /**
     * 转移.
     * @param mixed $toUid
     * @return mixed
     * @throws \ReflectionException
     * @throws BindingResolutionException
     */
    public function shift(array $ids, $toUid)
    {
        if (! $toUid) {
            throw $this->exception(__('common.empty.attr', ['attr' => '转移人ID']));
        }

        return $this->transaction(function () use ($ids, $toUid) {
            return $this->dao->search(['id' => $ids])->update(['uid' => $toUid]);
        });
    }

    /**
     * 作废表单.
     * @throws BindingResolutionException
     */
    public function invalidApplyForm(int $id): array
    {
        /** @var FormService $builder */
        $builder = app()->get(FormService::class);
        return [
            $builder->hidden('id', $id),
            $builder->textarea('remark', '作废原因')->required(),
        ];
    }

    /**
     * 作废申请.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function invalidApply(int $id, int $invalid = 1, string $remark = ''): bool
    {
        $info = $this->dao->get($id);
        if (! $info) {
            throw $this->exception(__('common.operation.noExists'));
        }
        if ($invalid == 1) {
            if (! in_array($info->status, [1, 5, 6])) {
                throw $this->exception('当年发票不能申请作废, 请核对发票状态');
            }

            if (! $remark) {
                throw $this->exception('请填写作废原因');
            }

            $info->status      = 3;
            $info->card_remark = $remark;
        } else {
            if ($info->status !== 3) {
                throw $this->exception('发票状态异常, 请稍后再试');
            }
            $info->status = -1;
            InvoiceCancelJob::dispatch($info->num);
        }

        $res = $info->save();
        if ($res) {
            Task::deliver(new InvoiceRecordTask(1, $id, $this->uuId(false), $info->status, ['card_remark' => $remark]));
        }
        return $res;
    }

    /**
     * 作废审核.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function invalidReview(int $id, int $invalid = 2, string $remark = ''): bool
    {
        if ($invalid !== 2 && ! $remark) {
            throw $this->exception('请填写作废原因');
        }

        $info = $this->dao->get($id);
        if (! $info) {
            throw $this->exception(__('common.operation.noExists'));
        }

        $entId = 1;
        $uuId  = $this->uuId(false);
        if (! app()->get(RolesService::class)->checkDefaultRule(uuid_to_uid((string) $uuId, $entId), $entId)) {
            throw $this->exception('您暂无权限查看');
        }

        if ($info->status !== 5) {
            throw $this->exception('当前发票不能操作作废, 请核对发票状态');
        }

        if ($invalid == 2) {
            $info->status         = -1;
            $info->finance_remark = $remark;
        } else {
            $info->status      = 5;
            $info->card_remark = $remark;
        }
        $res = $info->save();
        if ($res) {
            Task::deliver(new InvoiceRecordTask($entId, $id, uuid_to_uid($uuId), $info->status == -1 ? 4 : 1, ['finance_remark' => $remark]));
        }
        return $res;
    }

    /**
     * 累计/审核中/付款金额统计
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function priceStatistics(int $entId, string $eid, string $cid): array
    {
        $where = ['eid' => 0, 'entid' => $entId];
        if ($eid) {
            $where['eid'] = $eid;
        }
        if ($cid) {
            $where['cid'] = $cid;
        }

        return [
            'cumulative_invoiced_price' => $this->dao->sum(array_merge($where, ['status' => 5]), 'amount'),
            'audit_invoiced_price'      => $this->dao->sum(array_merge($where, ['status' => 0]), 'amount'),
            'cumulative_payment_price'  => app()->get(ClientBillService::class)->getSum(array_merge($where, ['types' => [0, 1]])),
        ];
    }

    /**
     * 开票撤回.
     * @param mixed $id
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function withdraw($id, string $remark): int
    {
        $info = $this->checkAuth($id);
        if ($info->status == 1) {
            throw $this->exception(__('common.operation.noPermission'));
        }

        if (! $remark) {
            throw $this->exception(__('common.empty.attr', ['attr' => '未通过原因']));
        }
        if ($info->status !== 0) {
            throw $this->exception('发票撤回失败, 请核对发票状态');
        }
        $res = $this->dao->update(['id' => $id], ['status' => -1, 'remark' => $remark, 'real_date' => null]);
        if ($res) {
            Task::deliver(new InvoiceRecordTask($info->entid, $id, $this->uuId(false), -1, ['remark' => $remark]));
            Task::deliver(new StatusChangeTask(ClientEnum::INVOICE_NOTICE, ClientEnum::INVOICE_DELETE, $info->entid, $id));
        }
        return $res;
    }

    /**
     * 详情.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getInfo(array $where, array $field = ['*'], array $with = []): array
    {
        $info = $this->dao->get($where, $field, $with);
        if (! $info) {
            throw $this->exception('发票不存在');
        }

        $info = $info->toArray();
        if (! $info['attachs'] && $info['num']) {
            $info['attach'] = app()->get(SmsService::class)->invoiceDownload($info['num']);
        }
        $info['apply'] = app()->get(ClientInvoiceLogService::class)->get(['invoice_id' => $where['id'], 'type' => 0], ['id', 'uid', 'type', 'created_at'], with: ['card'], sort: 'id');
        return $info;
    }

    /**
     * 获取在线开票地址
     * @return mixed
     * @throws BindingResolutionException
     * @throws \ReflectionException
     * @throws GuzzleException
     */
    public function getInvoiceUri(int $id)
    {
        $info = $this->dao->get($id, ['*'], [
            'clientBill' => function ($query) {
                $query->with(['treaty']);
            },
        ]);
        if (! $info) {
            throw $this->exception('发票不存在');
        }

        $info->unique = uniqid();
        $info->save();

        if ($info->collect_type === 'mail') {
            $invoiceType = match ((int) $info->types) {
                3       => InvoiceEnum::INVOKE_TYPE_81,
                default => InvoiceEnum::INVOKE_TYPE_82,
            };
        } else {
            $invoiceType = match ((int) $info->types) {
                3       => InvoiceEnum::INVOKE_TYPE_85,
                default => InvoiceEnum::INVOKE_TYPE_86,
            };
        }

        $goodsData = [];
        $info      = $info->toArray();
        foreach ($info['client_bill'] as $item) {
            $goodsData[] = [
                'storeName' => $item['treaty']['title'] ?? '',
                'unitPrice' => $item['num'],
                'num'       => 1,
            ];
        }
        if (! $goodsData) {
            $goodsData[] = [
                'storeName' => $info['name'],
                'unitPrice' => $info['amount'],
                'num'       => 1,
            ];
        }

        $res = app()->get(SmsService::class)->invoiceUrl($info['unique'], [
            'taxId'          => $info['ident'],
            'accountName'    => $info['title'],
            'bankName'       => $info['bank'],
            'bankAccount'    => $info['account'],
            'telephone'      => $info['tel'],
            'companyAddress' => $info['address'],
            'email'          => $info['collect_email'],
            'isEnterprise'   => (int) $info['types'] === 1 ? 0 : 1,
        ], $goodsData, $invoiceType);

        if ($res['status'] !== 200) {
            throw new ApiRequestException($res['msg']);
        }

        return $res['data'];
    }

    /**
     * 发票回调.
     * @return array|bool
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function invoiceCallBack()
    {
        Log::error('invoiceCallBack----' . json_encode(request()->all()));

        return app()->get(SmsService::class)->invoiceCallBack(function ($event, $data) {
            switch ($event) {
                case 'detection':
                    // 回调检测
                    return $data;
                case 'success':
                    // 开票成功
                    if (empty($data['unique'])) {
                        return false;
                    }
                    $invoiceInfo = $this->dao->get(['unique' => $data['unique']]);
                    if (! $invoiceInfo) {
                        return false;
                    }
                    if ($invoiceInfo->status === 5) {
                        return true;
                    }
                    $invoiceInfo->serial_number = $data['invoice_serial_number'];
                    $invoiceInfo->num           = $data['invoice_num'];
                    $invoiceInfo->status        = 5;
                    return $invoiceInfo->save();
            }
        });
    }

    /**
     * 前端发票回调.
     * @param mixed $id
     * @param mixed $num
     * @param mixed $serial_number
     * @return array|bool
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function editStatus($id, $num, $serial_number)
    {
        Log::error('invoiceCallBack--222222--' . json_encode(['id' => $num, 'request' => request()->all()]));
        // 开票成功
        if (! $id) {
            throw $this->exception('缺少发票ID');
        }
        $invoiceInfo = $this->dao->get($id);
        if (! $invoiceInfo) {
            throw $this->exception('缺少发票信息');
        }
        if (! $serial_number) {
            throw $this->exception('缺少发票号码');
        }
        $invoiceInfo->serial_number = $serial_number;
        $invoiceInfo->num           = $num;
        $invoiceInfo->status        = 5;
        return $invoiceInfo->save();
    }

    /**
     * 新增发票.
     * @return string
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function saveInvoice(array $data, int $uid): mixed
    {
        $rule = app()->get(OpenapiRuleService::class)->get(['crud_id' => 0, 'url' => 'open/invoice'], ['id', 'post_prams'])?->toArray();
        if (! $rule) {
            throw $this->exception('无效的接口数据');
        }

        $form                  = [];
        $postParams            = json_decode($rule['post_prams'], true);
        [$approveId, $content] = app()->get(ApproveService::class)->getApproveConfig('invoicing_switch');
        $requestParams         = array_column($postParams, 'name', 'symbol');
        if (isset($content['children'])) {
            foreach ($content['children'] as $value) {
                if (isset($requestParams[$value['symbol']])) {
                    $form[$value['field']] = $data[$requestParams[$value['symbol']]];
                }
            }
        }

        return app()->get(ApproveApplyService::class)->saveForm($form, [], $approveId, 0, $uid);
    }

    /**
     * 作废.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function invalidInvoice(int $id): mixed
    {
        return true;
    }

    /**
     * 发送邮件.
     * @param mixed $id
     * @return bool|mixed
     * @throws \ReflectionException
     * @throws BindingResolutionException
     */
    protected function sendMail($id)
    {
        $info = $this->dao->get($id);
        if (! $info) {
            throw $this->exception('发票不存在');
        }
        $files = app()->get(AttachService::class)->getListByRelation(['relation_type' => AttachService::RELATION_TYPE_INVOICE, 'relation_id' => $id], ['att_dir as path', 'name']);
        return $files ? Mail::to($info['collect_email'])->queue(new ClientInvoice($info, $files)) : true;
    }
}
