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
 * 协议
 * Class SystemAgreementRequest.
 */
class SystemAgreementRequest extends ApiValidate
{
    /**
     * 设置规则.
     * @var array
     */
    protected $rules = [
        'content' => 'required',
    ];

    /**
     * 错误提示.
     * @var string[]
     */
    protected $message = [
        'content.required' => '请填写协议内容',
    ];
}
