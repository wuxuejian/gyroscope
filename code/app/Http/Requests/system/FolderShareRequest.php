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
class FolderShareRequest extends ApiValidate
{
    /**
     * 验证规则.
     * @var string[]
     */
    protected $rules = [
        'rule'            => 'required|array|min:1',
        'rule.*.uid'      => 'required',
        'rule.*.download' => 'in:0,1',
        'rule.*.update'   => 'in:0,1',
    ];

    /**
     * 设置错误提示.
     * @var string[]
     */
    protected $message = [
        'rule'             => '请选择用户',
        'rules.*.uid'      => '请选择分享用户',
        'rules.*.download' => '下载权限有误',
        'rules.*.update'   => '更新权限有误',
    ];
}
