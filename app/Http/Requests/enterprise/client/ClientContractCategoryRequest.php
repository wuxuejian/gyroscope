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

namespace App\Http\Requests\enterprise\client;

use App\Http\Requests\ApiValidate;

class ClientContractCategoryRequest extends ApiValidate
{
    /**
     * 自动验证
     * @var bool
     */
    public $authValidate = true;

    /**
     * 规则.
     * @return string[]
     */
    protected function rules()
    {
        return [
            'name'           => 'required|max:50',
            'bill_cate_path' => 'array',
        ];
    }

    /**
     * 错误提醒.
     * @return string[]
     */
    protected function message()
    {
        return [
            'name.required'           => '请填写分类名称',
            'name.max'                => '分类名称长度超出限制',
            'bill_cate_path.required' => '请选择账目分类',
            'bill_cate_path.array'    => '请选择正确的账目分类',
        ];
    }
}
