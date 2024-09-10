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
 * 调薪记录
 * Class UserSalary.
 */
class UserSalary extends BaseModel
{
    /**
     * 表名.
     * @var string
     */
    protected $table = 'enterprise_user_salary';

    /**
     * 主键.
     * @var string
     */
    protected $primaryKey = 'id';

    public function setContentAttribute($value)
    {
        $this->attributes['content'] = $value ? json_encode($value) : '';
    }

    public function getContentAttribute($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    public function scopeEntid($query, $value)
    {
        if ($value !== '') {
            $query->where('entid', $value);
        }
    }

    public function scopeCardId($query, $value)
    {
        if ($value !== '') {
            $query->where('card_id', $value);
        }
    }
}
