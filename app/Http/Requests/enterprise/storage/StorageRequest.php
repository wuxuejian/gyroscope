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

namespace App\Http\Requests\enterprise\storage;

use App\Http\Requests\ApiValidate;

class StorageRequest extends ApiValidate
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
            'cid'  => 'required',
            'name' => 'required',
        ];
    }

    /**
     * 提示.
     * @return string[]
     */
    public function message()
    {
        return [
            'cid.required'  => '请选择物资分类',
            'name.required' => '请填写物资名称',
        ];
    }
}
