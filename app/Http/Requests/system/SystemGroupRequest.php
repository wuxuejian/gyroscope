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

namespace App\Http\Requests\system;

use App\Http\Requests\ApiRequest;

/**
 * 组合数据组验证器
 * Class SystemGroupRequest.
 */
class SystemGroupRequest extends ApiRequest
{
    /**
     * 验证规则.
     * @return array
     */
    public function rules()
    {
        return [
            'group_name' => 'required|max:64',
            'group_key'  => 'required|alpha_dash|max:64',
            'fields'     => 'required|max:5000',
            'group_info' => 'max:255',
            'sort'       => 'integer',
        ];
    }

    /**
     * 错误信息.
     * @return string[]
     */
    public function messages()
    {
        return [
            'group_name.required'  => '请填写数据组名称',
            'group_name.max'       => '数据组名称过长',
            'group_key.required'   => '请填写数据组字段名称',
            'group_key.alpha_dash' => '数据组字段名称仅可包含字母、数字、下划线',
            'group_key.max'        => '数据组字段名称过长',
            'fields.required'      => '请填写数据组内容',
            'fields.max'           => '数据组内容过长',
            'group_info.max'       => '数据组简介过长',
            'sort.integer'         => '排序只能为整数',
        ];
    }
}
