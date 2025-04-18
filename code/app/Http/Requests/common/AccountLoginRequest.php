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

namespace App\Http\Requests\common;

use App\Http\Requests\ApiValidate;

/**
 * 账号登录验证
 * Class AccountLoginRequest.
 */
class AccountLoginRequest extends ApiValidate
{
    /**
     * 场景.
     * @var \string[][]
     */
    protected $scene = [
        'login' => [
            'account', 'password', 'captcha',
        ],
        'login_uni' => [
            'account', 'password',
        ],
    ];

    /**
     * 验证码类型.
     * @var string
     */
    protected $captcha = 'user';

    /**
     * 设置验证码类型.
     * @return $this
     */
    public function setCaptcha(string $type)
    {
        $this->captcha = $type;
        return $this;
    }

    /**
     * 设置规则.
     * @return array|string[]
     */
    public function rules()
    {
        return [
            'account'  => 'required|min:5|max:50|alpha_num',
            'password' => 'required',
            'captcha'  => 'required|captcha_api:' . request('key') . ',' . $this->captcha,
        ];
    }

    /**
     * 设置提示语.
     * @return string[]
     */
    public function message()
    {
        return [
            'account.required'    => '账号必须填写',
            'account.min'         => '账号长度不正确',
            'account.max'         => '账号长度超出限制',
            'account.alpha_num'   => '账号不正确',
            'password.required'   => '密码必须填写',
            'captcha.required'    => '验证码必须填写',
            'captcha.captcha_api' => '验证码不正确',
        ];
    }
}
