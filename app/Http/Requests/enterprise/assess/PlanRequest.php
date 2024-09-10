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

namespace App\Http\Requests\enterprise\assess;

use App\Http\Requests\ApiValidate;

/**
 * 企业资金流水
 * Class RankRequest.
 */
class PlanRequest extends ApiValidate
{
    /**
     * 自动验证
     * @var bool
     */
    public $authValidate = true;

    protected $types = ['before', 'after', 'start'];

    /**
     * 错误提醒.
     * @return string[]
     */
    public function message()
    {
        return [
            'make_type.required'  => '请选择目标制定时间类型',
            'make_type.in'        => '请选择正确的目标制定时间类型',
            'make_day.required'   => '请填写目标制定时间',
            'make_day.integer'    => '请填写正确的目标制定时间',
            'eval_type.required'  => '请选择上级评价时间类型',
            'eval_type.in'        => '请选择正确的上级评价时间类型',
            'eval_day.required'   => '请填写上级评价时间',
            'eval_day.integer'    => '请填写正确的上级评价时间',
            'verify_day.required' => '请填写绩效审核时间',
            'verify_day.integer'  => '请填写正确的绩效审核时间',
        ];
    }

    /**
     * 规则.
     * @return array|string[]
     */
    protected function rules()
    {
        return [
            'make_type'  => 'required|in:' . implode(',', $this->types),
            'make_day'   => 'required|integer',
            'eval_type'  => 'required|in:' . implode(',', $this->types),
            'eval_day'   => 'required|integer',
            'verify_day' => 'required|integer',
        ];
    }
}
