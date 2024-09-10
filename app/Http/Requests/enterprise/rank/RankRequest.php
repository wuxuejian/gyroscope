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

namespace App\Http\Requests\enterprise\rank;

use App\Http\Requests\ApiValidate;

/**
 * 企业职级
 * Class RankRequest.
 */
class RankRequest extends ApiValidate
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
            'name.required'    => '请填写职级名称',
            'cate_id.required' => '请选择职级类别',
            'cate_id.integer'  => '职级类别只能为数字',
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
            'cate_id' => 'required|integer',
        ];
    }
}
