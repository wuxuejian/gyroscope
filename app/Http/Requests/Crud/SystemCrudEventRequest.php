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

namespace App\Http\Requests\Crud;

use App\Http\Requests\ApiValidate;

class SystemCrudEventRequest extends ApiValidate
{
    /**
     * @var bool
     */
    public $authValidate = true;

    /**
     * @return string[]
     * @email 136327134@qq.com
     * @date 2024/3/19
     */
    public function rules()
    {
        return [
            'additional_search'                     => 'array',
            'additional_search.*.operator'          => 'required',
            'additional_search.*.form_field_uniqid' => 'required',
            // 'additional_search.*.value'             => '',

            'field_options'                     => 'array',
            'field_options.*.form_field_uniqid' => 'required',
            //            'field_options.*.to_form_field_uniqid' => 'required',
            'field_options.*.operator' => 'required',
            // 'field_options.*.value'                 => 'required',

            // 'options'                               => 'object',

            // 数据校验的验证条件
            // 'options.search_boolean'               => 'required',
            'options.search'                     => 'array',
            'options.search.*.operator'          => 'required',
            'options.search.*.form_field_uniqid' => 'required',

            'aggregate_target_search.*.operator'          => 'required',
            'aggregate_target_search.*.form_field_uniqid' => 'required',

            'aggregate_data_search.*.operator'          => 'required',
            'aggregate_data_search.*.form_field_uniqid' => 'required',

            'aggregate_field_rule.*.form_field_uniqid'    => 'required',
            'aggregate_field_rule.*.to_form_field_uniqid' => 'required', // 聚合字段
            'aggregate_field_rule.*.operator'             => 'required', // 聚合方式
        ];
    }

    /**
     * @return string[]
     * @email 136327134@qq.com
     * @date 2024/3/19
     */
    public function message()
    {
        return [
            'additional_search.array'                        => '附加过滤条件必须为数组对象',
            'additional_search.*.operator.required'          => '附加过滤条件搜索方式不能为空',
            'additional_search.*.form_field_uniqid.required' => '附加过滤条件字段不能为空',

            'field_options.array'                        => '目标字段必须为数组对象',
            'field_options.*.form_field_uniqid.required' => '目标字段不能为空',
            //            'field_options.*.to_form_field_uniqid.required' => '修改字段不能为空',
            'field_options.*.operator.required' => '操作内容的字段方式不能为空',

            // 'options.object'                                => '其他配置项必须为对象',
            // 'options.search_boolean.required'               => '其他配置项筛选条件方式必须选择',
            'options.search.array'                        => '其他配置项筛选条件必须为数组',
            'options.search.*.operator.required'          => '其他配置项筛选条件搜索方式不能为空',
            'options.search.*.form_field_uniqid.required' => '其他配置项筛选条件搜索字段不能为空',

            'aggregate_target_search.array'                        => '聚合搜索必须为数组',
            'aggregate_target_search.*.operator.required'          => '聚合搜索方式必不能为空',
            'aggregate_target_search.*.form_field_uniqid.required' => '聚合搜索字段不能为空',

            'aggregate_data_search.array'                        => '聚合数据搜索必须为数组',
            'aggregate_data_search.*.operator.required'          => '聚合数据搜索方式必不能为空',
            'aggregate_data_search.*.form_field_uniqid.required' => '聚合数据搜索字段不能为空',

            'aggregate_field_rule.array'                           => '聚合规则必须为数组',
            'aggregate_field_rule.*.operator.required'             => '聚合规则方式必不能为空',
            'aggregate_field_rule.*.form_field_uniqid.required'    => '聚合规则字段不能为空',
            'aggregate_field_rule.*.to_form_field_uniqid.required' => '聚合规则回显字段不能为空',
        ];
    }
}
