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

namespace App\Http\Requests\enterprise\attendance;

use App\Http\Requests\ApiValidate;

/**
 * 考勤班次
 * Class AttendanceShiftRequest.
 */
class AttendanceShiftRequest extends ApiValidate
{
    /**
     * 自动.
     * @var bool
     */
    public $authValidate = true;

    /**
     * @return array|string[]
     */
    public function rules(): array
    {
        return [
            'name'             => 'required|max:50',
            'number'           => 'required|in:1,2',
            'rest_time'        => 'required|in:0,1',
            'overtime'         => 'integer',
            'color'            => 'required',
            'number1'          => 'required|array',
            'number2'          => 'array',
            'rest_start'       => 'date_format:H:i',
            'rest_end'         => 'date_format:H:i',
            'rest_start_after' => 'required|in:0,1',
            'rest_end_after'   => 'required|in:0,1|gte:rest_start_after',
        ];
    }

    /**
     * @return string[]
     */
    public function message(): array
    {
        return [
            'name.required'             => '请填写班次名称',
            'name.max'                  => '班次名称长度超出限制',
            'number.required'           => '请选择上下班次数',
            'number.in'                 => '请选择正确的上下班次数',
            'rest_time.in'              => '请选择正确的中途休息时间',
            'rest_time.required'        => '请选择正确的中途休息时间',
            'overtime.integer'          => '请选择正确的加班起算时间',
            'color.required'            => '请选择颜色标识',
            'number1.required'          => '请填写班次规则',
            'number1.array'             => '班次规则参数类型错误',
            'rest_start.date_format'    => '请选择正确的中途休息时间格式',
            'rest_end.date_format'      => '请选择正确的中途休息时间格式',
            'rest_start_after.required' => '请选择中途休息时间规则',
            'rest_end_after.required'   => '请选择中途休息时间规则',
            'rest_start_after.in'       => '请选择正确的中途休息时间规则',
            'rest_end_after.in'         => '请选择正确的中途休息时间规则',
            'rest_end_after.gte'        => '请选择正确的中途休息时间规则',
        ];
    }
}
