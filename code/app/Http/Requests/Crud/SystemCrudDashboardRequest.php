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

namespace App\Http\Requests\Crud;

use App\Http\Requests\ApiValidate;

/**
 * 统计看板验证
 * Class SystemCrudDashboardRequest.
 */
class SystemCrudDashboardRequest extends ApiValidate
{
    /**
     * 自动验证
     * @var bool
     */
    public $authValidate = true;

    /**
     * 错误提醒.
     * @return string[]
     */
    public function message(): array
    {
        return [
            'name.required' => '请填写名称',
            'name.max'      => '名称长度超出限制',
        ];
    }

    /**
     * 规则.
     * @return array|string[]
     */
    protected function rules(): array
    {
        return [
            'name' => 'required|max:100',
        ];
    }
}
