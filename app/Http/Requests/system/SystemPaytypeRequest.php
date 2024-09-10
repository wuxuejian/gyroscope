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

use App\Http\Requests\ApiValidate;

class SystemPaytypeRequest extends ApiValidate
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name'  => 'required',
            'ident' => 'required',
        ];
    }

    /**
     * 设置错误提醒.
     * @return string[]
     */
    public function message()
    {
        return [
            'name.required'  => '请填写支付方式名称',
            'ident.required' => '请填写支付方式标识',
        ];
    }
}
