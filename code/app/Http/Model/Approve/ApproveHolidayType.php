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

namespace App\Http\Model\Approve;

use App\Http\Model\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 假期类型表
 * Class ApproveHolidayType.
 */
class ApproveHolidayType extends BaseModel
{
    use SoftDeletes;

    /**
     * 表名.
     * @var string
     */
    protected $table = 'approve_holiday_type';

    /**
     * 主键.
     * @var string
     */
    protected $primaryKey = 'id';

    protected $hidden = ['deleted_at'];

    /**
     * id作用域
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeId($query, $value): void
    {
        if (is_array($value)) {
            $query->whereIn('id', $value);
        } elseif ($value !== '') {
            $query->where('id', $value);
        }
    }

    /**
     * name作用域.
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeNameLike($query, $value): void
    {
        if ($value !== '') {
            $query->where('name', 'like', '%' . $value . '%');
        }
    }

    /**
     * id作用域
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeNotId($query, $value): void
    {
        if ($value !== '') {
            $query->where('id', '<>', $value);
        }
    }

    /**
     * deleted_at作用域
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeFilterNormal($query, $value): void
    {
        $query->whereNotNull('deleted_at');
    }
}
