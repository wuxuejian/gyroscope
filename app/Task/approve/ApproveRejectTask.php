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

namespace App\Task\approve;

use App\Constants\ApproveEnum;
use App\Constants\Crud\CrudTriggerEnum;
use App\Constants\CustomEnum\InvoiceEnum;
use App\Http\Service\Approve\ApproveApplyService;
use App\Http\Service\Approve\ApproveService;
use App\Http\Service\Client\ClientBillService;
use App\Http\Service\Client\ClientInvoiceService;
use App\Http\Service\Crud\CrudModuleService;
use App\Http\Service\Crud\SystemCrudService;
use App\Task\customer\InvoiceRecordTask;
use crmeb\exceptions\ApiException;
use Hhxsv5\LaravelS\Swoole\Task\Task;
use Illuminate\Support\Facades\Log;

/**
 * 审批驳回后置事件
 * Class ApproveRejectTask.
 */
class ApproveRejectTask extends Task
{
    /**
     * 审批申请id.
     * ApproveRejectJob constructor.
     */
    public function __construct(protected int $applyId) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $approveInfo = toArray(app()->get(ApproveApplyService::class)->get($this->applyId, ['approve_id', 'status', 'user_id', 'entid', 'crud_id', 'link_id']));
            if (! $approveInfo) {
                throw new ApiException('无效的审批信息');
            }
            $types = app()->get(ApproveService::class)->value($approveInfo['approve_id'], 'types');
            switch ($types) {
                case ApproveEnum::CUSTOMER_CONTRACT_RENEWAL:
                case ApproveEnum::CUSTOMER_CONTRACT_EXPENSES:
                case ApproveEnum::CUSTOMER_CONTRACT_PAYMENT:
                    app()->get(ClientBillService::class)->update(['apply_id' => $this->applyId], ['status' => 2]);
                    break;
                case ApproveEnum::CUSTOMER_INVOICE_ISSUANCE:
                    $service         = app()->get(ClientInvoiceService::class);
                    $invoice         = $service->get(['link_id' => $this->applyId]);
                    $invoice->status = InvoiceEnum::STATUS_REJECT;
                    $invoice->save();
                    app()->get(ClientBillService::class)->update(['invoice_id' => $invoice->id], ['invoice_id' => 0]);
                    Task::deliver(new InvoiceRecordTask($approveInfo['entid'], $invoice->id, (int) $invoice->uid, InvoiceEnum::STATUS_REJECT, toArray($invoice)));
                    break;
                case ApproveEnum::CUSTOMER_INVOICE_CANCELLATION:
                    $service         = app()->get(ClientInvoiceService::class);
                    $invoice         = $service->get(['revoke_id' => $this->applyId]);
                    $invoice->status = InvoiceEnum::STATUS_INVOICED;
                    $invoice->save();
                    Task::deliver(new InvoiceRecordTask($approveInfo['entid'], $invoice->id, (int) $invoice->uid, InvoiceEnum::STATUS_INVOICED, toArray($invoice)));
                    break;
            }
            if ($approveInfo['crud_id']) {
                $crudInfo = app()->make(SystemCrudService::class)->get(
                    $approveInfo['crud_id'],
                    with: [
                        'field' => fn ($q) => $q->select(['crud_id', 'field_name_en', 'field_name', 'form_value', 'field_type', 'is_default']),
                        'child' => fn ($q) => $q->select(['crud_id', 'id']),
                    ]
                )?->toArray();
                $service      = app()->get(CrudModuleService::class);
                $masterData   = $service->model(crudId: $approveInfo['crud_id'])->get($approveInfo['link_id'])?->toArray();
                $scheduleData = $crudInfo['child'] ? $service->model(crudId: $crudInfo['child']['id'])->get($approveInfo['link_id'])?->toArray() : [];
                $service->handleEvent(app()->make(SystemCrudService::class)->get($approveInfo['crud_id']), CrudTriggerEnum::TRIGGER_REJECT, $approveInfo['link_id'], $masterData, $scheduleData);
            }
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . ':' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTrace()]);
        }
    }
}
