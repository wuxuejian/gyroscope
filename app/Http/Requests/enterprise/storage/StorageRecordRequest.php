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

namespace App\Http\Requests\enterprise\storage;

use App\Http\Requests\ApiValidate;
use Illuminate\Validation\Rule;

class StorageRecordRequest extends ApiValidate
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
            'types' => [
                'required', Rule::in([0, 1, 2, 3, 4, 5]),
            ],
            'storage' => 'required',
        ];
    }

    /**
     * 提示.
     * @return string[]
     */
    public function message()
    {
        return [
            'types.required'   => '请填写操作类型',
            'types.in'         => '操作类型不正确',
            'storage.required' => '缺少物资参数',
        ];
    }
}
