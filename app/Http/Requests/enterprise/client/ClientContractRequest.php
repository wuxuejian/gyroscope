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

namespace App\Http\Requests\enterprise\client;

use App\Http\Requests\ApiValidate;

class ClientContractRequest extends ApiValidate
{
    /**
     * 验证场景.
     * @var \string[][]
     */
    protected $scene = [
        'store'  => ['title', 'eid', 'price', 'start_date', 'end_date', 'category_id', 'sign_status', 'contract_no'],
        'update' => ['title', 'price', 'start_date', 'end_date', 'category_id', 'sign_status', 'contract_no'],
    ];

    /**
     * 错误提醒.
     * @return string[]
     */
    public function message()
    {
        return [
            'title.required'       => '请填写合同名称',
            'title.regex'          => '合同名称不符合规范',
            'title.max'            => '合同名称过长',
            'eid.required'         => '请选择客户',
            'price.required'       => '请填写合同金额',
            'price.numeric'        => '请填写正确的合同金额',
            'start_date.required'  => '请选择合同开始日期',
            'start_date.date'      => '请选择正确的日期',
            'end_date.required'    => '请选择合同结束日期',
            'end_date.date'        => '请选择正确的结束日期',
            'category_id.required' => '请选择合同分类',
            'sign_status.required' => '请选择签约状态',
            'sign_status.integer'  => '签约状态异常',
            'sign_status.in'       => '签约状态异常',
            'contract_no.max'      => '合同编号过长',
        ];
    }

    /**
     * 规则.
     * @return array|string[]
     */
    protected function rules()
    {
        return [
            'title'       => 'required|max:255',
            'eid'         => 'required',
            'price'       => 'required|numeric',
            'start_date'  => 'required|date',
            'category_id' => 'required',
            'sign_status' => 'required|integer|in:0,1,2',
            'end_date'    => 'date',
            'contract_no' => 'max:60',
        ];
    }
}
