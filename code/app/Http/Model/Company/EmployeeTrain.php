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

namespace App\Http\Model\Company;

use App\Http\Model\BaseModel;

/**
 * 员工培训.
 */
class EmployeeTrain extends BaseModel
{
    /**
     * 表名.
     * @var string
     */
    protected $table = 'employee_train';

    /**
     * 主键.
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Content获取器.
     * @param mixed $value
     */
    public function getContentAttribute($value): string
    {
        return $value ? htmlspecialchars_decode($value) : '';
    }
}
