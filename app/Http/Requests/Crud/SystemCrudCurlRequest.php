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
use Illuminate\Validation\Rule;

class SystemCrudCurlRequest extends ApiValidate
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
    public function message()
    {
        return [
            'title.required'           => '请填写接口标题',
            'url.required'             => '请填写接口请求地址',
            'url.url'                  => '接口请求地址格式不正确',
            'is_pre.required'          => '前置请求类型必须存在',
            'is_pre.in'                => '前置请求类型错误',
            'method.required'          => '请求方式必须存在',
            'headers.array'            => '请求headers头必须为数组对象',
//            'headers.*.name.required'  => '请求headers必须包含name字段',
//            'headers.*.type.required'  => '请求headers必须包含type字段',
//            'headers.*.value.required' => '请求headers必须包含value字段',
            'data.array'               => '请求data必须为数组对象',
//            'data.*.name.required'     => '请求data必须包含name字段',
//            'data.*.value.required'    => '请求data必须包含value字段',
//            'data.*.type.required'     => '请求data必须包含type字段',
        ];
    }

    /**
     * 规则.
     * @return array|string[]
     */
    protected function rules()
    {
        return [
            'title'           => 'required',
            'url'             => 'required|url',
            'is_pre'          => ['required', Rule::in([0, 1])],
            'method'          => 'required',
            'headers'         => 'array',
//            'headers.*.name'  => 'required',
//            'headers.*.type'  => 'required',
//            'headers.*.value' => 'required',
            'data'            => 'array',
//            'data.*.name'     => 'required',
//            'data.*.value'    => 'required',
//            'data.*.type'     => 'required',
        ];
    }
}
