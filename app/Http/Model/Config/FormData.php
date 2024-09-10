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
use Illuminate\Database\Query\Builder;

/**
 * 自定义表单内容.
 */
class FormData extends BaseModel
{
    use SoftDeletes;

    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    protected $table = 'form_data';

    protected $primaryKey = 'id';

    /**
     * id作用域
     * @param Builder $query
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
     * cate_id作用域
     * @param Builder $query
     */
    public function scopeCateId($query, $value): void
    {
        if (is_array($value)) {
            $query->whereIn('cate_id', $value);
        } elseif ($value !== '') {
            $query->where('cate_id', $value);
        }
    }

    /**
     * key 作用域
     * @param Builder $query
     */
    public function scopeKey($query, $value): void
    {
        if (is_array($value)) {
            $query->whereIn('key', $value);
        } elseif ($value !== '') {
            $query->where('key', $value);
        }
    }

    public function dictData()
    {
        return $this->hasMany(DictData::class, 'type_name', 'dict_ident')->select([
            'name as label',
            'value',
            'type_name',
            'pid',
        ]);
    }

    /**
     * value 修改器.
     */
    protected function setValueAttribute($value): void
    {
        $this->attributes['value'] = $value ? json_encode($value) : '';
    }

    /**
     * value 获取器.
     */
    protected function getValueAttribute($value): mixed
    {
        return $value ? json_decode($value, true) : '';
    }
}
