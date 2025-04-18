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

namespace App\Constants;

use crmeb\utils\MessageType;

/**
 * 客户相关.
 */
final class ClientEnum
{
    /**
     * 资金状态：删除.
     */
    public const BILL_DELETE = -1;

    /**
     * 资金状态：待审核.
     */
    public const BILL_PENDING = 0;

    /**
     * 资金状态：通过.
     */
    public const BILL_PASSED = 1;

    /**
     * 资金状态：未通过.
     */
    public const BILL_REJECTED = 2;

    /**
     * 合同状态：删除.
     */
    public const CONTRACT_DELETE = -1;

    /**
     * 发票状态：删除.
     */
    public const INVOICE_DELETE = -1;

    // 发票状态 -1：开票撤回；0：待开票；1：已开票；2:已拒绝；3：申请作废；4:同意作废；5：拒绝作废；6：作废撤回；

    /**
     * 发票状态：待开票.
     */
    public const INVOICE_PENDING = 0;

    /**
     * 发票状态：已开票.
     */
    public const INVOICE_PASSED = 1;

    /**
     * 发票状态：已拒绝.
     */
    public const INVOICE_REJECTED = 2;

    /**
     * 发票状态：申请作废.
     */
    public const INVOICE_CANCELING = 3;

    /**
     * 发票状态：同意作废.
     */
    public const INVOICE_CANCEL = 4;

    /**
     * 发票状态：拒绝作废.
     */
    public const INVOICE_REFUSE_CANCEL = 5;

    /**
     * 发票状态：作废撤回.
     */
    public const INVOICE_RECALL_CANCEL = 6;

    /**
     * 客户状态：删除.
     */
    public const CLIENT_DELETE = -1;

    /**
     * 发票关联通知.
     */
    public const INVOICE_NOTICE = [
        MessageType::CONTRACT_INVOICE_TYPE,
        MessageType::FINANCE_INVOICE_VERIFY_SUCCESS_TYPE,
        MessageType::FINANCE_INVOICE_VERIFY_FAIL_TYPE,
        MessageType::FINANCE_INVOICE_OPEN_TYPE,
        MessageType::FINANCE_INVOICE_CLOSE_TYPE,
    ];

    /**
     * 资金关联通知.
     */
    public const BILL_NOTICE = [
        MessageType::FINANCE_VERIFY_FAIL_TYPE,
        MessageType::FINANCE_VERIFY_SUCCESS_TYPE,
    ];

    /**
     * 合同关联通知.
     */
    public const CONTRACT_NOTICE = [
        MessageType::CONTRACT_RETURN_MONEY_TYPE,
        MessageType::CONTRACT_RENEW_TYPE,
        MessageType::CONTRACT_EXPEND_TYPE,
        MessageType::FINANCE_VERIFY_FAIL_TYPE,
    ];

    /**
     * 合同删除关联通知.
     */
    public const CONTRACT_DELETE_NOTICE = [
        MessageType::CONTRACT_RENEW_TYPE,
        MessageType::CONTRACT_ABNORMAL_TYPE,
        MessageType::CONTRACT_RETURN_MONEY_TYPE,
        MessageType::CONTRACT_OVERDUE_DAY_REMIND,
        MessageType::CONTRACT_SOON_OVERDUE_REMIND,
        MessageType::CONTRACT_OVERDUE_REMIND_TYPE,
        MessageType::CONTRACT_URGENT_RENEW_TYPE,
        MessageType::CONTRACT_DAY_REMIND_TYPE,
        MessageType::CONTRACT_EXPEND_TYPE,
        MessageType::FINANCE_VERIFY_FAIL_TYPE,
        MessageType::DEALT_MONEY_WORK_TYPE,
    ];

    /**
     * 客户跟进关联通知.
     */
    public const CLIENT_FOLLOW_NOTICE = [
        MessageType::DEALT_CLIENT_WORK_TYPE,
    ];

    /**
     * 客户回款关联通知.
     */
    public const CLIENT_RETURN_MONEY_NOTICE = [
        MessageType::CONTRACT_RETURN_MONEY_TYPE,
    ];

    /**
     * 客户回款提醒关联通知.
     */
    public const CLIENT_REMIND_NOTICE = [
        MessageType::DEALT_MONEY_WORK_TYPE,
    ];

    /**
     * 待办提醒关联通知.
     */
    public const CLIENT_SCHEDULE_NOTICE = [
        MessageType::DEALT_PRESON_WORK_TYPE,
    ];

    /**
     * 客户删除关联通知.
     */
    public const CLIENT_DELETE_NOTICE = [
        MessageType::DEALT_CLIENT_WORK_TYPE,
    ];
}
