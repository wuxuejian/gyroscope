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

namespace App\Http\Model\Crud;

use App\Http\Model\Admin\Admin;
use App\Http\Model\BaseModel;

/**
 * 操作日志.
 */
class SystemCrudLog extends BaseModel
{
    /**
     * 表明.
     * @var string
     */
    protected $table = 'system_crud_log';

    /**
     * 主键.
     * @var string
     */
    protected $primaryKey = 'id';

    public function user()
    {
        return $this->hasOne(Admin::class, 'id', 'uid');
    }
}
