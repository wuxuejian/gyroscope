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

namespace App\Http\Model\Config;

use App\Http\Model\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 自定义表单分组.
 */
class FormCate extends BaseModel
{
    use SoftDeletes;

    protected $table = 'form_cate';

    protected $primaryKey = 'id';

    public function data()
    {
        return $this->hasMany(FormData::class, 'cate_id', 'id')->orderByDesc('sort');
    }
}
