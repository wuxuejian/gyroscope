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

/**
 * 客户联系人
 * Class ClientRequest.
 */
class ClientLiaisonRequest extends ApiValidate
{
    /**
     * 验证场景.
     * @var \string[][]
     */
    protected $scene = [
        'store'  => ['eid', 'name', 'tel', 'wechat'],
        'update' => ['name', 'tel', 'wechat'],
    ];

    /**
     * 错误提醒.
     * @return string[]
     */
    public function message()
    {
        return [
            'eid.required'  => '请填写客户编号',
            'name.required' => '请填写联系人姓名',
            'name.max'      => '联系人姓名长度超出限制',
            'tel.required'  => '请填写联系人电话',
            'wechat.max'    => '联系人微信长度超出限制',
        ];
    }

    /**
     * 规则.
     * @return array|string[]
     */
    protected function rules()
    {
        return [
            'eid'    => 'required',
            'name'   => ['required', 'max:30'],
            'wechat' => 'max:50',
            // 'tel'  => ['required'],
        ];
    }
}
