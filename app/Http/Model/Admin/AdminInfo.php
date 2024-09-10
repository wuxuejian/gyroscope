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

namespace App\Http\Model\Admin;

use App\Http\Model\BaseModel;
use crmeb\traits\model\TimeDataTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdminInfo extends BaseModel
{
    use SoftDeletes;
    use TimeDataTrait;

    /**
     * 表名.
     *
     * @var string
     */
    protected $table = 'admin_info';

    /**
     * 字段黑名单.
     *
     * @var array
     */
    protected $guarded = [];

    public function scopeType($query, $value)
    {
        if (is_array($value)) {
            $query->whereIn('type', $value);
        } else {
            $query->where('type', $value);
        }
    }

    public function scopeId($query, $value)
    {
        if (is_array($value)) {
            $query->whereIn('id', $value);
        } else {
            $query->where('id', $value);
        }
    }
}
