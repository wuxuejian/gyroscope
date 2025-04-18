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

namespace App\Http\Requests\user;

use App\Http\Requests\ApiValidate;

/**
 * 用户备忘录分类
 * Class UserMemorialCategoryRequest.
 */
class UserMemorialCategoryRequest extends ApiValidate
{
    /**
     * 自动.
     * @var bool
     */
    public $authValidate = true;

    /**
     * @return array|string[]
     */
    public function rules()
    {
        return [
            'name' => 'required',
        ];
    }

    /**
     * @return string[]
     */
    public function message()
    {
        return [
            'name.required' => '请填写分类名称',
        ];
    }
}
