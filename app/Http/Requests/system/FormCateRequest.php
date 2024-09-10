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

/**
 * 表单分组验证器.
 */
class FormCateRequest extends ApiValidate
{
    /**
     * 开启自动验证
     * @var bool
     */
    public $authValidate = true;

    /**
     * 规则.
     * @var string[]
     */
    protected $rules = [
        'title' => 'required|max:64',
        'sort'  => 'integer|max:99999',
    ];

    /**
     * 提示.
     * @var string[]
     */
    protected $message = [
        'title.required' => '请填写分组名称',
        'title.max'      => '空间名称长度不能大于64个字符',
        'sort.integer'   => '排序只能为整数',
        'sort.max'       => '排序最大不能超过99999',
    ];
}
