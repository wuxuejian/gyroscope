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

class SystemCrudTableRequest extends ApiValidate
{
    /**
     * @var bool
     */
    public $authValidate = true;

    /**
     * @return string[]
     * @email 136327134@qq.com
     * @date 2024/2/27
     */
    public function rules()
    {
        return [
            'view_search'                      => 'array',
            'view_search.rule.*.field_name'    => 'required',
            'view_search.rule.*.field_name_en' => 'required',
            'view_search.rule.*.operator'      => 'required',
        ];
    }

    /**
     * @return string[]
     * @email 136327134@qq.com
     * @date 2024/2/27
     */
    public function message()
    {
        return [
            'view_search.array'                         => '视图数据类型应为数组',
            'view_search.rule.*.field_name.required'    => '缺少字段名',
            'view_search.rule.*.field_name_en.required' => '缺少字段',
            'view_search.rule.*.operator.required'      => '缺少搜索条件',
        ];
    }
}
