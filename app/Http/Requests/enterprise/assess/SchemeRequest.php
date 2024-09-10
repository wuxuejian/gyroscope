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
use Illuminate\Validation\Rule;

/**
 * 绩效考核方案
 * Class SchemeRequest.
 */
class SchemeRequest extends ApiValidate
{
    /**
     * 自动验证
     * @var bool
     */
    public $authValidate = true;

    /**
     * 规则.
     * @return array
     */
    public function rules()
    {
        return [
            'name'     => 'required',
            'user_id'  => 'required|integer',
            'user_ids' => 'required|array',
            //            'create_type'            => [
            //                'required',
            //                Rule::in(['time', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'])
            //            ],
            'create_date'         => 'required',
            'file_id'             => 'required',
            'own_appraise_period' => [
                'required',
                Rule::in(['year', 'nextyear', 'month', 'nextmonth', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']),
            ],
            'own_appraise_date'      => 'required',
            'leader_appraise_period' => [
                'required',
                Rule::in(['year', 'nextyear', 'month', 'nextmonth', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']),
            ],
            'leader_appraise_date' => 'required',
            'compute_rule'         => 'required|array',
        ];
    }

    /**
     * 错误提醒.
     * @return string[]
     */
    public function message()
    {
        return [
            'name.required'                   => '请填写考核名称',
            'user_id.required'                => '请选择考核人名称',
            'user_id.integer'                 => '考核人参数类型错误',
            'user_ids.required'               => '请选择被考核人',
            'user_ids.array'                  => '被考核人参数类型错误',
            'file_id.required'                => '请选择考核文档',
            'compute_rule.required'           => '请填写考核文档计算坐标',
            'compute_rule.array'              => '考核文档计算规则类型错误',
            'create_date.required'            => '请填写考核生成时间',
            'own_appraise_period.required'    => '请选择自评结束时间类型',
            'own_appraise_period.in'          => '自评结束时间类型错误',
            'own_appraise_date.required'      => '请选择自评结束时间',
            'leader_appraise_period.required' => '请选择上级评价结束时间类型',
            'leader_appraise_period.in'       => '上级评价结束时间类型错误',
            'leader_appraise_date.required'   => '请选择上级评价结束时间',
        ];
    }
}
