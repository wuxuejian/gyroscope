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
 * Class SystemRoleRequest.
 */
class EnterpriseFolderShareRequest extends ApiValidate
{
    /**
     * 验证规则.
     * @var string[]
     */
    protected $rules = [
        'name' => 'required|max:32',
    ];

    /**
     * 设置错误提示.
     * @var string[]
     */
    protected $message = [
        'name.required' => '请填写空间名称',
        'name.max'      => '空间名称长度不能大于32位',
    ];

    protected $scene = [
        'add'    => ['name'],
        'update' => [],
    ];
}
