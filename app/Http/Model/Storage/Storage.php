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

namespace App\Http\Model\Storage;

use App\Http\Model\BaseModel;
use crmeb\traits\model\TimeDataTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 物资管理.
 */
class Storage extends BaseModel
{
    use SoftDeletes;
    use TimeDataTrait;

    protected $primaryKey = 'id';

    protected $table = 'storage';

    public function scopeCid($query, $value)
    {
        if (is_array($value)) {
            $query->whereIn('cid', $value);
        } elseif ($value !== '') {
            $query->where('cid', $value);
        }
    }

    public function scopeId($query, $value)
    {
        if ($value !== '') {
            $query->where('id', $value);
        }
    }

    public function scopeIds($query, $value)
    {
        if (is_array($value)) {
            $query->whereIn('id', $value);
        } elseif ($value !== '') {
            $query->where('id', $value);
        }
    }

    public function scopeEntid($query, $value)
    {
        if ($value !== '') {
            $query->where('entid', $value);
        }
    }

    /**
     * @param Builder $query
     */
    public function scopeTypes($query, $value)
    {
        if ($value !== '') {
            $query->where('types', $value);
        }
    }

    /**
     * @param Builder $query
     */
    public function scopeStatus($query, $value)
    {
        if ($value !== '') {
            $query->where('status', $value);
        }
    }

    /**
     * @param Builder $query
     */
    public function scopeStock($query, $value)
    {
        if ($value !== '') {
            if ($value > 0) {
                $query->where('stock', '>', 0);
            } else {
                $query->where('stock', 0);
            }
        }
    }

    /**
     * @param Builder $query
     */
    public function scopeNameLike($query, $value)
    {
        if ($value !== '') {
            $query->where(function ($query) use ($value) {
                $query->orWhere('name', 'like', '%' . $value . '%')->orWhere('number', 'like', '%' . $value . '%');
            });
        }
    }

    /**
     * @param Builder $query
     */
    public function scopeDistinct($query, $value)
    {
        if ($value !== '') {
            $query->groupBy($value);
        }
    }

    public function cate()
    {
        return $this->hasOne(StorageCategory::class, 'id', 'cid');
    }

    public function record()
    {
        return $this->hasMany(StorageRecord::class, 'storage_id', 'id');
    }
}
