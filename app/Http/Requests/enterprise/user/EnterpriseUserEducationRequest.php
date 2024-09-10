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

namespace App\Http\Requests\enterprise\user;

use App\Http\Requests\ApiValidate;

/**
 * Class EnterpriseUserEducationRequest.
 */
class EnterpriseUserEducationRequest extends ApiValidate
{
    /**
     * 验证规则.
     * @return array|string[]
     */
    public function rules()
    {
        return [
            'start_time'  => 'required',
            'end_time'    => 'required|time_contrast_api:' . request('start_time'),
            'school_name' => 'required',
            'major'       => 'required',
            'education'   => 'required',
        ];
    }

    /**
     * 错误提醒.
     * @return array
     */
    public function message()
    {
        return [
            'start_time.required'        => '请选择入学开始时间',
            'end_time.required'          => '请输入毕业时间',
            'end_time.time_contrast_api' => '毕业时间不能小于入学时间',
            'school_name.required'       => '请填写学校名称',
            'major.required'             => '请填写所学专业',
            'education.required'         => '请填写学历',
        ];
    }
}
