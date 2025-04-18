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
 * 考勤排班
 * Class AttendanceArrangeRequest.
 */
class AttendanceArrangeRequest extends ApiValidate
{
    /**
     * 场景.
     * @var \string[][]
     */
    protected $scene = [
        'store' => ['date', 'groups'],
    ];
    /**
     * 自动.
     * @var bool
     */
    // public $authValidate = true;

    /**
     * @return array|string[]
     */
    public function rules(): array
    {
        return [
            'date'   => 'required|date',
            'groups' => 'required|array',
        ];
    }

    /**
     * @return string[]
     */
    public function message(): array
    {
        return [
            'date.required'   => '请选择考勤时间',
            'date.date'       => '请选择正确的考勤时间',
            'groups.required' => '请选择考勤组',
            'groups.array'    => '请选择正确的考勤组',
        ];
    }
}
