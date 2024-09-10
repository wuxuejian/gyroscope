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
 * 字典数据验证器.
 */
class DIctDataRequest extends ApiValidate
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
        'name'    => 'required|max:64',
        'type_id' => 'required|numeric',
    ];

    /**
     * 提示.
     * @var string[]
     */
    protected $message = [
        'name.required'    => '请填写数据值',
        'name.max'         => '数据值长度不能大于64个字符',
        'type_id.required' => '缺少关联字典',
        'type_id.numeric'  => '无效的字典关联',
    ];
}
