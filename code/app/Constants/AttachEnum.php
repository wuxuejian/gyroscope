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

/**
 * 附件关联类型.
 */
final class AttachEnum
{
    /**
     * 客户合同附件.
     */
    private const Company_Client_Contract = 'client_contract';

    /**
     * 客户跟踪附件.
     */
    private const Company_Client_Follow = 'client_follow';

    /**
     * 客户发票附件.
     */
    private const Company_Client_Invoice = 'client_invoice';

    /**
     * 汇报附件.
     */
    private const Company_Report_File = 'report';

    /**
     * 记事本附件.
     */
    private const User_NotePad_File = 'notepad';

    /**
     * 审批附件.
     */
    private const Company_Approve_File = 'approve';

    /**
     * 企业素材.
     */
    private const Company_Attach = 'company_attach';

    /**
     * 用户素材.
     */
    private const User_Attach = 'user_attach';
}
