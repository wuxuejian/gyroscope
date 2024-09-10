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

namespace App\Http\Requests\enterprise\approve;

use App\Http\Requests\ApiValidate;

/**
 * 审批配置验证
 * Class ApproveRequest.
 */
class ApproveRequest extends ApiValidate
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
            'baseConfig.required'    => '缺少基础配置信息',
            'formConfig.required'    => '缺少表单配置信息',
            'processConfig.required' => '缺少流程配置信息',
            'ruleConfig.required'    => '缺少规则配置信息',
        ];
    }

    /**
     * 规则.
     * @return array|string[]
     */
    protected function rules()
    {
        return [
            'baseConfig'    => 'required',
            'formConfig'    => 'required',
            'processConfig' => 'required',
            'ruleConfig'    => 'required',
        ];
    }
}
