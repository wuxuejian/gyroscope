<?php

declare(strict_types=1);
/**
 *  +----------------------------------------------------------------------
 *  | 陀螺匠 [ 赋能开发者，助力企业发展 ]
 *  +----------------------------------------------------------------------
 *  | Copyright (c) 2016~2025 https://www.tuoluojiang.com All rights reserved.
 *  +----------------------------------------------------------------------
 *  | Licensed 陀螺匠并不是自由软件，未经许可不能去掉陀螺匠相关版权
 *  +----------------------------------------------------------------------
 *  | Author: 陀螺匠 Team <admin@tuoluojiang.com>
 *  +----------------------------------------------------------------------
 */

namespace App\Http\Model\Open;

use App\Http\Model\BaseModel;

class OpenapiRule extends BaseModel
{
    /**
     * 表名.
     * @var string
     */
    protected $table = 'openapi_rule';

    /**
     * 主键.
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * 请求地址提取.
     * @param mixed $value
     * @return mixed
     */
    public function getUrlAttribute($value): string
    {
        return 'api/' . $value;
    }
}
