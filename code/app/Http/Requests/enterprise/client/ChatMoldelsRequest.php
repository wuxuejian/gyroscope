<?php

declare(strict_types=1);
/**
 *  +----------------------------------------------------------------------
 *  | 陀螺匠 [ 赋能开发者，助力企业发展 ]
 *  +----------------------------------------------------------------------
 *  | Copyright (c) 2016~2025 https://www.tuoluojiang.com All rights reserved.
 *  +----------------------------------------------------------------------
 *  | Licensed 陀螺匠并不是自由软件，未经许可不能去掉陀螺匠相关版权
 *  +----------------------------------------------------------------------
 *  | Author: 陀螺匠 Team <admin@tuoluojiang.com>
 *  +----------------------------------------------------------------------
 */

namespace App\Http\Requests\enterprise\client;

use App\Http\Requests\ApiValidate;
use Illuminate\Validation\Rule;

class ChatMoldelsRequest extends ApiValidate
{
    /**
     * 验证场景.
     * @var string[][]
     */
    protected $scene = [
        'store'  => ['name', 'models_type', 'is_model', 'url', 'key'],
        'update' => ['name', 'models_type', 'is_model', 'url', 'key'],
    ];

    /**
     * 错误提醒.
     * @return string[]
     */
    public function message()
    {
        return [
            'name.required'        => '模型名称',
            'name.unique'          => '名称不能重复',
            'models_type.required' => '模型类型',
            'is_model.required'    => '基础模型',
            'url.url'              => 'API URL',
        ];
    }

    /**
     * 规则.
     * @return array|string[]
     */
    protected function rules()
    {
        return [
            'name'        => ['required', Rule::unique('chat_models')->ignore($this->route('id'))->whereNull('deleted_at')],
            'models_type' => 'required',
            'is_model'    => 'required',
            'url'         => 'url',
        ];
    }
}
