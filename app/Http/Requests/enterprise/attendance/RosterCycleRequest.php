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
 * 考勤周期
 * Class RosterCycleRequest.
 */
class RosterCycleRequest extends ApiValidate
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
            'group_id' => 'required|integer|gt:0',
            'name'     => 'required|max:50',
            'cycle'    => 'required|integer',
            'shifts'   => 'required|array',
        ];
    }

    /**
     * @return string[]
     */
    public function message(): array
    {
        return [
            'group_id.required' => '请选择考勤组',
            'group_id.integer'  => '请选择正确的考勤组',
            'group_id.gt'       => '请选择正确的考勤组',
            'name.required'     => '请填写班次名称',
            'name.max'          => '班次名称长度超出限制',
            'cycle.required'    => '请选择周期天数',
            'cycle.integer'     => '请选择正确的周期天数',
            'shifts.required'   => '请设置周期班次',
            'shifts.array'      => '请设置正确的周期班次',
        ];
    }
}
