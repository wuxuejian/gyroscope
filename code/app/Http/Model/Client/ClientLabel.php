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

namespace App\Http\Model\Client;

use App\Http\Model\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder;

/**
 * 客户标签
 * Class ClientLabel.
 */
class ClientLabel extends BaseModel
{
    /**
     * @var string
     */
    protected $id = 'id';

    /**
     * @var string
     */
    protected $table = 'client_label';

    /**
     * @return HasMany
     */
    public function children()
    {
        return $this->hasMany(self::class, 'pid', 'id');
    }

    /**
     * name作用域
     * @param Builder $query
     * @param mixed $value
     * @return mixed
     */
    public function scopeNameLike($query, $value)
    {
        if ($value) {
            return $query->where('name', 'like', '%' . $value . '%');
        }
    }

    /**
     * id作用域
     * @param Builder $query
     * @param mixed $value
     * @return mixed
     */
    public function scopeId($query, $value)
    {
        if (is_array($value)) {
            $query->whereIn('id', $value);
        } elseif ($value !== '') {
            $query->where('id', $value);
        }
    }

    /**
     * pid作用域
     * @param Builder $query
     * @param mixed $value
     * @return mixed
     */
    public function scopePid($query, $value)
    {
        if ($value !== '') {
            $query->where('pid', $value);
        }
    }

    /**
     * pid作用域
     * @param Builder $query
     * @param mixed $value
     * @return mixed
     */
    public function scopeNotPid($query, $value)
    {
        if ($value !== '') {
            $query->whereNotIn('pid', [$value]);
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
