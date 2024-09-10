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
 * Class FastEntryRequest.
 */
class QuickRequest extends ApiValidate
{
    /**
     * 验证规则.
     * @var string[]
     */
    protected $rules = [
        'name'     => 'required|max:50',
        'cid'      => 'required',
        'pc_url'   => 'required|max:120',
        'uni_url'  => 'max:120',
        'status'   => 'integer',
        'sort'     => 'integer',
        'pc_show'  => 'integer',
        'uni_show' => 'integer',
    ];

    /**
     * 设置错误提示.
     * @var string[]
     */
    protected $message = [
        'name.required'    => '角色名称必须填写',
        'name.max'         => '数据组名称过长',
        'cid.required'     => '请选择分类',
        'pc_url.required'  => '请输入PC端地址',
        'pc_url.max'       => 'PC端地址过长',
        'uni_url.max'      => '移动端地址过长',
        'status.integer'   => '状态值必须为数字',
        'sort.integer'     => '配置排序只能为数字',
        'pc_show.integer'  => 'PC端显示字段只能为数字',
        'uni_show.integer' => '移动端显示字段只能为数字',
    ];
}
