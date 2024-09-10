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

namespace App\Http\Requests\enterprise\program;

use App\Http\Requests\ApiValidate;

/**
 * 项目
 * Class ProgramRequest.
 */
class ProgramRequest extends ApiValidate
{
    /**
     * 自动验证
     * @var bool
     */
    public $authValidate = true;

    /**
     * 错误提醒.
     * @return string[]
     */
    public function message(): array
    {
        return [
            'name.required'          => '请填写项目名称',
            'name.max'               => '项目名称长度超出限制',
            'uid.required'           => '请选择负责人',
            'uid.integer'            => '请选择负责人',
            'status.required'        => '请选择正确的状态',
            'status.integer'         => '请选择正确的状态',
            'status.in'              => '请选择正确的状态',
            'start_date.date_format' => '计划开始时间格式异常',
            'end_date.date_format'   => '计划结束时间格式异常',
            'describe.max'           => '项目描述长度超出限制',
            'members.array'          => '项目成员格式异常',
        ];
    }

    /**
     * 规则.
     * @return array|string[]
     */
    protected function rules(): array
    {
        return [
            'name'       => 'required|max:100',
            'uid'        => 'required|integer',
            'start_date' => 'date_format:Y-m-d',
            'end_date'   => 'date_format:Y-m-d',
            'status'     => 'required|integer|in:0,1,2',
            'describe'   => 'max:1000',
            'members'    => 'array',
        ];
    }
}
