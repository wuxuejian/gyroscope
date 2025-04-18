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
use crmeb\utils\Regex;

/**
 * 客户
 * Class ClientRequest.
 */
class ClientRequest extends ApiValidate
{
    /**
     * 错误提醒.
     * @return string[]
     */
    public function message()
    {
        return [
            'name.required' => '请填写客户名称',
            'phone.max'     => '请填写正确的联系电话',
            // 'phone.regex'      => '请填写正确的联系电话',
            'label.required'  => '请选择客户标签',
            'cid.required'    => '请选择客户分类',
            'cid.integer'     => '请选择正确的客户分类',
            'source.required' => '请选择客户来源',
            'source.integer'  => '请选择正确的客户来源',
        ];
    }

    /**
     * 规则.
     * @return array|string[]
     */
    protected function rules()
    {
        return [
            'name' => 'required',
            // 'phone'   => ['size:11', 'regex:' . Regex::PHONE_NUMBER],
            'phone' => 'max:12',
            'label' => 'required',
            // 'cid'     => 'required|integer',
            'source' => 'required|integer',
        ];
    }
}
