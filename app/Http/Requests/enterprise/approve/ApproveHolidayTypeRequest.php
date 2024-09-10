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

namespace App\Http\Requests\enterprise\approve;

use App\Http\Requests\ApiValidate;

/**
 * 假期类型验证
 * Class ApproveHolidayTypeRequest.
 */
class ApproveHolidayTypeRequest extends ApiValidate
{
    /**
     * 自动.
     * @var bool
     */
    public $authValidate = true;

    /**
     * 规则.
     * @return array|string[]
     */
    public function rules(): array
    {
        return [
            'name'                     => 'required|max:50',
            'new_employee_limit'       => 'in:0,1',
            'new_employee_limit_month' => 'integer|between:1,12',
            'duration_type'            => 'in:0,1',
            'duration_calc_type'       => 'in:0,1',
        ];
    }

    /**
     * 错误提醒.
     * @return string[]
     */
    public function message(): array
    {
        return [
            'name.required'                    => '缺少类型名称',
            'name.max'                         => '类型名称长度超出限制',
            'new_employee_limit.in'            => '请选择正确的新员工请假限制',
            'new_employee_limit_month.between' => '请选择正确的新员工请假月时限制',
            'duration_type.in'                 => '请选择正确的请假时长类型',
            'duration_calc_type.in'            => '请选择正确的时长计算类型',
        ];
    }
}
