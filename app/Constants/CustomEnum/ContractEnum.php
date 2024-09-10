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
 * 合同业务
 */
final class ContractEnum extends CustomEnum
{
    /**
     * 我查看合同.
     */
    public const CONTRACT_VIEWER = 5;

    /**
     * 我负责合同.
     */
    public const CONTRACT_CHARGE = 6;

    /**
     * 查看客户合同.
     */
    public const CUSTOMER_VIEWER_CONTRACT = 115;

    /**
     * 负责客户合同.
     */
    public const CUSTOMER_CHARGE_CONTRACT = 125;

    /**
     * 公海客户合同.
     */
    public const CUSTOMER_HEIGHT_SEAS_CONTRACT = 135;

    public const CONTRACT_LIST_FIELD = [
        [
            'field' => 'bill_no',
            'name'  => '付款单号',
        ],
        [
            'field' => 'salesman',
            'name'  => '业务员',
            'type'  => 'salesman',
        ],
        [
            'field' => 'creator',
            'name'  => '创建人',
        ],
        [
            'field' => 'payment_status',
            'name'  => '付款状态',
        ],
        [
            'field' => 'created_at',
            'name'  => '创建时间',
        ],
        [
            'field' => 'payment_time',
            'name'  => '付款时间',
        ],
        [
            'field' => 'start_date',
            'name'  => '开始日期',
        ],
        [
            'field' => 'end_date',
            'name'  => '结束日期',
        ],
        // [
        //     'field' => 'contract_customer',
        //     'name'  => '客户名称',
        //     'type'  => 'customer',
        // ],
    ];

    public const CONTRACT_SEARCH_FIELD = [
        [
            'field'      => 'creator',
            'name'       => '创建人',
            'input_type' => 'personnel',
        ],
        [
            'field'      => 'salesman',
            'name'       => '业务员',
            'input_type' => 'personnel',
        ],
        [
            'field'      => 'bill_no',
            'name'       => '付款单号',
            'input_type' => 'input',
        ],
        [
            'field'      => 'created_at',
            'name'       => '创建日期',
            'input_type' => 'date',
        ],
        [
            'field'      => 'payment_time',
            'name'       => '付款日期',
            'input_type' => 'date',
        ],
        //        [
        //            'field' => 'start_date',
        //            'name'  => '开始日期',
        //            'input_type'  => 'date',
        //        ],
        //        [
        //            'field' => 'end_date',
        //            'name'  => '结束日期',
        //            'input_type'  => 'date',
        //        ],
    ];

    public const CONTRACT_VIEWER_LIST_FIELD = [];

    public const CONTRACT_VIEWER_LIST_DEFAULT_FIELD = [
        'contract_name', 'contract_price', 'contract_customer', 'start_date', 'end_date', 'payment_status',
        'contract_status', 'contract_followed', 'salesman', 'created_at',
    ];

    public const CONTRACT_CHARGE_LIST_DEFAULT_FIELD = [
        'contract_name', 'contract_price', 'contract_customer', 'start_date', 'end_date', 'payment_status',
        'contract_status', 'contract_followed', 'created_at',
    ];

    public const CONTRACT_VIEWER_SEARCH_FIELD = [
        [
            'field'      => 'scope_frame',
            'name'       => '管理范围',
            'input_type' => 'scope_frame',
        ],
    ];

    public const CONTRACT_VIEWER_SEARCH_DEFAULT_FIELD = [
        'contract_name', 'contract_category', 'start_date',
    ];

    public const CONTRACT_CHARGE_SEARCH_DEFAULT_FIELD = [
        'contract_name', 'contract_no', 'contract_category', 'contract_customer', 'bill_no', 'created_at',
        'payment_time', 'start_date', 'end_date',
    ];

    public const CONTRACT_CHARGE_LIST_FIELD = [];

    public const CONTRACT_CHARGE_SEARCH_FIELD = [];

    public const CONTRACT_NOT_ALLOW_DELETE_FIELD = [
        'contract_status', 'contract_category', 'contract_price', 'start_date', 'end_date', 'contract_followed',
        'contract_customer', 'signing_status',
    ];

    public const CUSTOMER_CONTRACT_VIEWER_LIST_DEFAULT_FIELD = [
        'contract_name', 'contract_price', 'payment_status', 'contract_status', 'salesman',
    ];

    public const CONTRACT_TYPE = [
        self::CONTRACT_VIEWER,
        self::CONTRACT_CHARGE,
        self::CUSTOMER_VIEWER_CONTRACT,
        self::CUSTOMER_CHARGE_CONTRACT,
        self::CUSTOMER_HEIGHT_SEAS_CONTRACT,
        self::CONTRACT_VIEWER,
    ];
}
