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

namespace App\Http\Requests\enterprise\assess;

use App\Http\Requests\ApiValidate;

/**
 * 企业资金流水
 * Class RankRequest.
 */
class TargetRequest extends ApiValidate
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
    public function message()
    {
        return [
            'cate_id.required' => '请选择模板分类',
            'cate_id.integer'  => '请选择正确的模板分类',
            'name.required'    => '请填写模板名称',
            'content.required' => '请填写模板内容',
        ];
    }

    /**
     * 规则.
     * @return array|string[]
     */
    protected function rules()
    {
        return [
            'cate_id' => 'required|integer',
            'name'    => 'required',
            'content' => 'required',
        ];
    }
}
