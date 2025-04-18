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
 * 人事考勤
 * Class AttendancePersonRequest.
 */
class AttendancePersonRequest extends ApiValidate
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
            'number'          => 'required|integer|in:0,1,2,3',
            'remark'          => 'max:255',
            'status'          => 'required|integer|in:1,2,3,4,5',
            'location_status' => 'required|integer|in:0,1,2',
        ];
    }

    /**
     * @return string[]
     */
    public function message(): array
    {
        return [
            'number.required'          => '请选择正确的上下班次',
            'name.integer'             => '请选择正确的上下班次',
            'number.in'                => '请选择正确的上下班次',
            'remark.max'               => '备注长度超出255限制',
            'status.required'          => '请选择正确的打卡状态',
            'status.integer'           => '请选择正确的打卡状态',
            'status.in'                => '请选择正确的打卡状态',
            'location_status.required' => '请选择正确的地点状态',
            'location_status.integer'  => '请选择正确的地点状态',
            'location_status.in'       => '请选择正确的地点状态',
        ];
    }
}
