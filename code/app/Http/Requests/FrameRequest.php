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

namespace App\Http\Requests;

/**
 * 组织架构数据验证
 */
class FrameRequest extends ApiValidate
{
    protected $scene = [
        'create' => ['path', 'name', 'role_id'],
        'update' => ['name', 'role_id'],
    ];

    /**
     * 提醒.
     * @var string[]
     */
    protected $message = [
        'path.required'    => '请选择父级部门',
        'name.required'    => '请填写部门名称',
        'role_id.required' => '请选择角色',
        'role_id.integer'  => '请选择正确的角色',
        'role_id.gt'       => '请选择角色',
    ];

    /**
     * 验证规则.
     * @return array|string[]
     */
    protected function rules()
    {
        return [
            'path'    => 'required',
            'name'    => 'required',
            'role_id' => 'required|integer|gt:0',
        ];
    }
}
