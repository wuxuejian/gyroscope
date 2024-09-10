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

namespace App\Constants\CustomEnum;

/**
 * 客户业务
 */
final class CustomerEnum extends CustomEnum
{
    /**
     * 我查看客户.
     */
    public const CUSTOMER_VIEWER = 1;

    /**
     * 我负责客户.
     */
    public const CUSTOMER_CHARGE = 2;

    /**
     * 公海池客户.
     */
    public const CUSTOMER_HEIGHT_SEAS = 3;

    /**
     * 公司客户.
     */
    public const CUSTOMER_COMPANY = 4;

    public const CUSTOMER_LIST_FIELD = [
        [
            'field' => 'customer_no',
            'name'  => '客户编号',
            'type'  => 'text',
        ],
        [
            'field' => 'un_followed_days',
            'name'  => '未跟进天数',
        ],
        [
            'field' => 'amount_recorded',
            'name'  => '已入账金额',
        ],
        [
            'field' => 'amount_expend',
            'name'  => '已支出金额',
        ],
        [
            'field' => 'invoiced_amount',
            'name'  => '已开票金额',
        ],
        [
            'field' => 'contract_num',
            'name'  => '合同数量',
        ],
        [
            'field' => 'invoice_num',
            'name'  => '发票数量',
        ],
        [
            'field' => 'attachment_num',
            'name'  => '附件数量',
        ],
        // [
        //     'field' => 'customer_status',
        //     'name'  => '客户状态',
        // ],
        [
            'field' => 'liaison_tel',
            'name'  => '联系人：联系电话',
        ],
        [
            'field' => 'last_follow_time',
            'name'  => '最后跟进时间',
        ],
        [
            'field' => 'creator',
            'name'  => '创建人',
        ],
        [
            'field' => 'created_at',
            'name'  => '创建时间',
        ],
    ];

    public const CUSTOMER_SEARCH_FIELD = [
        [
            'field'      => 'customer_no',
            'name'       => '客户编号',
            'input_type' => 'input',
        ],
        //        [
        //            'field' => 'un_followed_days',
        //            'name'  => '未跟进天数',
        //        ],
        //        [
        //            'field' => 'amount_recorded',
        //            'name'  => '已入账金额',
        //        ],
        //        [
        //            'field' => 'amount_expend',
        //            'name'  => '已支出金额',
        //        ],
        //        [
        //            'field' => 'invoiced_amount',
        //            'name'  => '已开票金额',
        //        ],
        //        [
        //            'field' => 'contract_num',
        //            'name'  => '合同数量',
        //        ],
        //        [
        //            'field' => 'invoice_num',
        //            'name'  => '发票数量',
        //        ],
        //        [
        //            'field' => 'attachment_num',
        //            'name'  => '附件数量',
        //        ],
        [
            'field'      => 'created_at',
            'name'       => '创建时间',
            'input_type' => 'date',
        ],
        [
            'field'      => 'creator',
            'name'       => '创建人',
            'input_type' => 'personnel',
        ],
        //        [
        //            'field' => 'customer_repeat_check',
        //            'name'  => '客户查重',
        //            'input_type'  => 'input',
        //        ],
        [
            'field'      => 'contract_name',
            'name'       => '合同名称',
            'input_type' => 'input',
        ],
        [
            'field'      => 'contract_no',
            'name'       => '合同编号',
            'input_type' => 'input',
        ],
        [
            'field'      => 'liaison',
            'name'       => '联系人',
            'input_type' => 'input',
        ],
        [
            'field'      => 'liaison_tel',
            'name'       => '联系电话',
            'input_type' => 'input',
        ],
    ];

    public const CUSTOMER_HEIGHT_SEAS_LIST_FIELD = [
        [
            'field' => 'before_salesman',
            'name'  => '前业务员',
        ],
        [
            'field' => 'return_num',
            'name'  => '退回次数',
        ],
        [
            'field' => 'return_reason',
            'name'  => '退回原因',
        ],
    ];

    public const CUSTOMER_HEIGHT_SEAS_LIST_DEFAULT_FIELD = [
        'customer_name', 'customer_way', 'liaison_tel', 'customer_label', 'salesman', 'last_follow_time',
        'customer_status', 'return_num', 'return_reason', 'created_at',
    ];

    public const CUSTOMER_HEIGHT_SEAS_SEARCH_FIELD = [
        [
            'field'      => 'before_salesman',
            'name'       => '前业务员',
            'input_type' => 'personnel',
        ],
    ];

    public const CUSTOMER_HEIGHT_SEAS_SEARCH_DEFAULT_FIELD = [
        'customer_name', 'customer_repeat_check', 'customer_no', 'customer_label', 'before_salesman', 'liaison', 'liaison_tel',
        'customer_way', 'created_at', 'contract_name', 'contract_no',
    ];

    public const CUSTOMER_CHARGE_LIST_FIELD = [
        [
            'field' => 'salesman',
            'name'  => '业务员',
            'type'  => 'salesman',
        ],
    ];

    public const CUSTOMER_CHARGE_LIST_DEFAULT_FIELD = [
        'customer_name', 'customer_way', 'liaison_tel', 'customer_label', 'last_follow_time', 'customer_status',
        'customer_followed', 'created_at',
    ];

    public const CUSTOMER_VIEWER_SEARCH_FIELD = [
        [
            'field'      => 'salesman',
            'name'       => '业务员',
            'input_type' => 'personnel',
        ],
        [
            'field'      => 'scope_frame',
            'name'       => '管理范围',
            'input_type' => 'scope_frame',
        ],
    ];

    public const CUSTOMER_CHARGE_SEARCH_FIELD = [
        [
            'field'      => 'salesman',
            'name'       => '业务员',
            'input_type' => 'personnel',
        ],
    ];

    public const CUSTOMER_CHARGE_SEARCH_DEFAULT_FIELD = [
        'customer_name', 'customer_repeat_check', 'customer_no', 'customer_label', 'liaison', 'liaison_tel',
        'customer_way', 'created_at', 'contract_name', 'contract_no',
    ];

    public const CUSTOMER_VIEWER_LIST_FIELD = self::CUSTOMER_CHARGE_LIST_FIELD;

    public const CUSTOMER_VIEWER_LIST_DEFAULT_FIELD = [
        'customer_name', 'customer_way', 'liaison_tel', 'customer_label', 'last_follow_time', 'customer_status',
        'customer_followed', 'salesman', 'created_at',
    ];

    public const CUSTOMER_VIEWER_SEARCH_DEFAULT_FIELD = [
        'customer_name', 'customer_label', 'created_at',
    ];

    public const CUSTOMER_COMPANY_LIST_FIELD = self::CUSTOMER_CHARGE_LIST_FIELD;

    public const CUSTOMER_COMPANY_LIST_DEFAULT_FIELD = [
        'customer_name', 'customer_way', 'liaison_tel', 'customer_label', 'last_follow_time', 'customer_status',
        'customer_followed', 'salesman', 'created_at',
    ];

    public const CUSTOMER_COMPANY_SEARCH_FIELD = self::CUSTOMER_VIEWER_SEARCH_FIELD;

    public const CUSTOMER_COMPANY_SEARCH_DEFAULT_FIELD = [
        'customer_name', 'customer_repeat_check', 'salesman', 'customer_no', 'customer_label', 'liaison', 'liaison_tel',
        'customer_way', 'created_at', 'contract_name', 'contract_no',
    ];

    public const CUSTOMER_NOT_ALLOW_DELETE_FIELD = [
        'customer_way', 'customer_name', 'customer_label', 'customer_followed', 'area_cascade', 'customer_status', 'b37a3f16',
    ];

    public const CUSTOMER_TYPE = [
        self::CUSTOMER_VIEWER,
        self::CUSTOMER_CHARGE,
        self::CUSTOMER_HEIGHT_SEAS,
        self::CUSTOMER_COMPANY,
    ];
}
