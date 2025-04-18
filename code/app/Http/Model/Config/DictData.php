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

namespace App\Http\Model\Config;

use App\Http\Model\BaseModel;

/**
 * 字典数据.
 */
class DictData extends BaseModel
{
    protected $table = 'dict_data';

    /**
     * 主键.
     * @var string
     */
    protected $primaryKey = 'id';

    public function type()
    {
        $this->hasOne(DictType::class, 'id', 'type_id');
    }

    /**
     * id 作用域
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeId($query, $value)
    {
        if (is_array($value)) {
            $query->whereIn('id', $value);
        } elseif ($value !== '') {
            $query->where('id', $value);
        }
    }

    public function scopeTypeId($query, $value)
    {
        if (is_array($value)) {
            $query->whereIn('type_id', $value);
        } elseif ($value) {
            $query->where('type_id', $value);
        }
    }

    public function scopeNotId($query, $value)
    {
        if (is_array($value)) {
            $query->whereNotIn('id', $value);
        } elseif ($value !== '') {
            $query->whereNot('id', $value);
        }
    }

    /**
     * pid gt 作用域
     * @param mixed $query
     * @param mixed $value
     */
    public function scopePidGt($query, $value): void
    {
        $query->where('pid', '>', $value);
    }

    /**
     * level_lt Lt 作用域
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeLevelLt($query, $value): void
    {
        $query->where('level', '<=', $value);
    }

    /**
     * level_lt Lt 作用域
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeLevel($query, $value): void
    {
        if ($value !== '') {
            $query->where('level', $value);
        }
    }

    /**
     * value 作用域
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeDictValue($query, $value): void
    {
        if (is_array($value)) {
            $query->whereIn('value', is_nested_array($value) ? array_merge(...$value) : $value);
        } else {
            $query->where('value', $value);
        }
    }

    /**
     * value 作用域
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeNotValue($query, $value): void
    {
        if (is_array($value)) {
            $query->whereNotIn('value', $value);
        } else {
            $query->whereNot('value', $value);
        }
    }

    public function scopeTypeName($query, $value)
    {
        $query->where('type_name', $value);
    }

    public function scopeStatus($query, $value)
    {
        if ($value !== '') {
            $query->where('status', $value);
        }
    }

    public function scopePid($query, $value)
    {
        if (is_array($value)) {
            $query->whereIn('pid', $value);
        } else {
            $query->where('pid', $value);
        }
    }

    public function scopeValues($query, $value)
    {
        $query->whereIn('value', $value);
    }

    public function scopeNameLike($query, $value)
    {
        $query->where(fn ($q) => $q->orWhere('name', 'like', "%{$value}%")->orWhere('value', 'like', "%{$value}%")->orWhere('mark', 'like', "%{$value}%"));
    }

    public function scopeIsCityShow($query, $value)
    {
        if ($value === 'city') {
            $query->where('level', '<=', 2);
        }
    }

    /**
     * name 作用域
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeNameEq($query, $value): void
    {
        if (is_array($value)) {
            $query->whereIn('name', $value);
        } elseif ($value !== '') {
            $query->where('name', $value);
        }
    }
}
