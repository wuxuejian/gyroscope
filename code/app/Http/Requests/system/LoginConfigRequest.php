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
 * Class LoginConfigRequest.
 */
class LoginConfigRequest extends ApiValidate
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
        'login_password_length' => 'required|integer|max:50',
        'login_password_type'   => 'required',
        'logint_time_out'       => 'required|integer|max:24',
        'logint_error_count'    => 'required|integer|min:5|max:50',
        'logint_lock'           => 'required|integer|max:5',
    ];

    /**
     * 提示.
     * @var string[]
     */
    protected $message = [
        'login_password_length.required' => '密码长度必须填写',
        'login_password_length.integer'  => '密码长度必须为数字',
        'login_password_length.max'      => '密码长度超出对最大限制50位',
        'login_password_type.required'   => '密码规则必须选择一项',
        'logint_time_out.required'       => '登录退出时间必须填写',
        'logint_time_out.integer'        => '登录退出时间必须为数字',
        'logint_time_out.max'            => '登录退出时间不能大于24小时',
        'logint_error_count.required'    => '密码错误次数必须填写',
        'logint_error_count.integer'     => '密码错误次数必须为数字',
        'logint_error_count.min'         => '设置密码错误次数不能小于5次',
        'logint_error_count.max'         => '设置密码错误次数不能超过5次',
        'logint_lock.required'           => '登录错误锁定时间必须填写',
        'logint_lock.integer'            => '登录错误锁定时间必须为数字',
        'logint_lock.max'                => '登录错误锁定时间不能大于5个小时',
    ];
}
