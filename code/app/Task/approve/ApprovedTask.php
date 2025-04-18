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

namespace App\Task\approve;

use App\Constants\ApproveEnum;
use App\Constants\Crud\CrudTriggerEnum;
use App\Constants\CustomEnum\InvoiceEnum;
use App\Http\Service\Approve\ApproveApplyService;
use App\Http\Service\Approve\ApproveContentService;
use App\Http\Service\Approve\ApproveService;
use App\Http\Service\Attendance\AttendanceApplyRecordService;
use App\Http\Service\Client\ClientBillService;
use App\Http\Service\Client\ClientInvoiceService;
use App\Http\Service\Crud\CrudModuleService;
use App\Http\Service\Crud\SystemCrudService;
use App\Task\customer\InvoiceRecordTask;
use App\Task\financial\FinancialRecordTask;
use crmeb\exceptions\ApiException;
use Hhxsv5\LaravelS\Swoole\Task\Task;
use Illuminate\Support\Facades\Log;

/**
 * 审批通过后置事件
 * Class ApprovedTask.
 */
class ApprovedTask extends Task
{
    /**
     * 审批申请id.
     * ApprovedJob constructor.
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
                $service->handleEvent(app()->make(SystemCrudService::class)->get($approveInfo['crud_id']), CrudTriggerEnum::TRIGGER_APPROVED, $approveInfo['link_id'], $masterData, $scheduleData);
            } else {
                $types = app()->get(ApproveService::class)->value($approveInfo['approve_id'], 'types');
                switch ($types) {
                    case ApproveEnum::CUSTOMER_CONTRACT_RENEWAL:
                    case ApproveEnum::CUSTOMER_CONTRACT_EXPENSES:
                    case ApproveEnum::CUSTOMER_CONTRACT_PAYMENT:
                        $service = app()->get(ClientBillService::class);
                        $service->approveSuccess($this->applyId);
                        Task::deliver(new FinancialRecordTask($service->value(['apply_id' => $this->applyId], 'id')));
                        break;
                    case ApproveEnum::CUSTOMER_INVOICE_ISSUANCE:
                        $service         = app()->get(ClientInvoiceService::class);
                        $invoice         = $service->get(['link_id' => $this->applyId]);
                        $invoice->status = InvoiceEnum::STATUS_APPROVED;
                        $invoice->save();

                        $content = app()->get(ApproveContentService::class)->get(['apply_id' => $this->applyId, 'symbol' => 'billId'], ['id', 'value']);
                        if ($content && is_array($content?->value)) {
                            app()->get(ClientBillService::class)->update(['id' => $content->value], ['invoice_id' => $invoice->id]);
                        }

                        Task::deliver(new InvoiceRecordTask($approveInfo['entid'], $invoice->id, (int) $invoice->uid, InvoiceEnum::STATUS_APPROVED, toArray($invoice)));
                        break;
                    case ApproveEnum::CUSTOMER_INVOICE_CANCELLATION:
                        $service         = app()->get(ClientInvoiceService::class);
                        $invoice         = $service->get(['revoke_id' => $this->applyId]);
                        $invoice->status = InvoiceEnum::STATUS_CANCEL;
                        $invoice->save();
                        app()->get(ClientBillService::class)->update(['invoice_id' => $invoice->id], ['invoice_id' => 0]);
                        Task::deliver(new InvoiceRecordTask($approveInfo['entid'], $invoice->id, (int) $invoice->uid, InvoiceEnum::STATUS_CANCEL, toArray($invoice)));
                        break;
                    default:
                        app()->get(AttendanceApplyRecordService::class)->createApplyRecord($this->applyId);
                }
            }
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . ':' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTrace()]);
        }
    }
}
