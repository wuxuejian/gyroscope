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

namespace App\Http\Model\Position;

use App\Http\Model\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Query\Builder;

/**
 * 职级类别
 * Class Category.
 */
class Category extends BaseModel
{
    /**
     * 表名.
     * @var string
     */
    protected $table = 'rank_category';

    /**
     * 主键.
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * @return HasOne
     */
    public function cate()
    {
        return $this->hasOne(self::class, 'id', 'pid');
    }

    /**
     * path修改器.
     * @param mixed $value
     */
    public function setPathAttribute($value)
    {
        $this->attributes['path'] = $value ? implode('/', $value) : '';
    }

    /**
     * path获取器.
     * @param mixed $value
     * @return false|string[]
     */
    public function getPathAttribute($value)
    {
        return $value ? array_map('intval', explode('/', $value)) : [];
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
     */
    public function scopeId($query, $value)
    {
        if ($value !== '') {
            $query->where('id', $value);
        }
    }

    /**
     * entid作用域
     * @param Builder $query
     * @param mixed $value
     */
    public function scopeEntid($query, $value)
    {
        if ($value !== '') {
            $query->where('entid', $value);
        }
    }

    /**
     * ID 作用域
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeNotId($query, $value): void
    {
        $query->where('id', '<>', $value);
    }
}
