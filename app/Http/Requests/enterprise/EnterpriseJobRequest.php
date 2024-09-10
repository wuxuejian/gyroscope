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

namespace App\Http\Requests\enterprise;

use App\Http\Requests\ApiValidate;

/**
 * 企业岗位
 * Class EnterpriseJobRequest.
 */
class EnterpriseJobRequest extends ApiValidate
{
    /**
     * 自动验证
     * @var bool
     */
    public $authValidate = true;

    /**
     * 提示.
     * @return string[]
     */
    public function message()
    {
        return [
            'name.required'    => '请填写岗位名称',
            'cate_id.required' => '请选择职级类别',
            'rank_id.required' => '请选择职级',
        ];
    }

    /**
     * 规则.
     * @return array|string[]
     */
    protected function rules()
    {
        return [
            'name'    => 'required',
            'cate_id' => 'required',
            'rank_id' => 'required',
        ];
    }
}
