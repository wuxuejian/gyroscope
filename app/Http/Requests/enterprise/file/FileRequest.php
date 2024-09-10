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

namespace App\Http\Requests\enterprise\file;

use App\Http\Requests\ApiValidate;
use Illuminate\Validation\Rule;

/**
 * 文件
 * Class FileRequest.
 */
class FileRequest extends ApiValidate
{
    /**
     * 自动验证
     * @var bool
     */
    public $authValidate = true;

    /**
     * 规则.
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required',
            'type' => [
                'required', Rule::in(['word', 'ppt', 'excel']),
            ],
        ];
    }

    /**
     * 提醒.
     * @return string[]
     */
    public function message()
    {
        return [
            'name.required' => '请填写文件名称',
            'type.required' => '请选择文档类型',
            'type.in'       => '文档类型不正确',
        ];
    }
}
