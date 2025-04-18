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
 * 工作经历
 * Class EnterpriseUserWorkRequest.
 */
class EnterpriseUserWorkRequest extends ApiValidate
{
    /**
     * 验证规则.
     * @return array|string[]
     */
    public function rules()
    {
        return [
            'start_time' => 'required',
            'end_time'   => 'required|time_contrast_api:' . request('start_time'),
            'company'    => 'required',
            'position'   => 'required',
            'describe'   => 'required',
        ];
    }

    /**
     * 设置错误提示.
     * @return string[]
     */
    public function message()
    {
        return [
            'start_time.required'        => '请选择工作开始时间',
            'end_time.required'          => '请输入工作结束时间',
            'end_time.time_contrast_api' => '工作开始时间不能小于结束时间',
            'company.required'           => '请输入所在公司名称',
            'position.required'          => '请输入所在公司职位',
            'describe.required'          => '请输入所在公司工作描述',
        ];
    }
}
