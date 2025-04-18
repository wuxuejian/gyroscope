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
use Illuminate\Database\Query\Builder;

/**
 * 业务自定义数据.
 */
class SalesmanCustom extends BaseModel
{
    protected $hidden = ['created_at', 'updated_at'];

    protected $table = 'salesman_custom_field';

    protected $primaryKey = 'id';

    /**
     * id作用域
     * @param Builder $query
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
     * custom_type 作用域
     * @param Builder $query
     * @param mixed $value
     */
    public function scopeCustomType($query, $value): void
    {
        if (is_array($value)) {
            $query->whereIn('custom_type', $value);
        } elseif ($value !== '') {
            $query->where('custom_type', $value);
        }
    }

    /**
     * field_list 修改器.
     * @param mixed $value
     */
    protected function setFieldListAttribute($value): void
    {
        $this->attributes['field_list'] = $value ? json_encode($value) : '';
    }

    /**
     * field_list 获取器.
     * @param mixed $value
     */
    protected function getFieldListAttribute($value): mixed
    {
        return $value ? json_decode($value, true) : [];
    }
}
