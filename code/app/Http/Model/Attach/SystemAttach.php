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

namespace App\Http\Model\Attach;

use App\Http\Model\Admin\Admin;
use App\Http\Model\BaseModel;
use crmeb\traits\model\TimeDataTrait;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Query\Builder;

/**
 * Class SystemAttach.
 */
class SystemAttach extends BaseModel
{
    use TimeDataTrait;

    /*sss
     * 表名
     * @var string
     */
    protected $table = 'system_attach';

    /**
     * 主键.
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * 附件路径.
     * @param mixed $value
     */
    public function getAttDirAttribute($value): string
    {
        return $value ? link_file($value) : '';
    }

    /**
     * 压缩路径.
     * @param mixed $value
     */
    public function getThumbDirAttribute($value): string
    {
        return $value ? link_file($value) : '';
    }

    /**
     * 附件路径.
     * @param mixed $value
     */
    public function getSrcAttribute($value): string
    {
        return $value ? link_file($value) : '';
    }

    /**
     * 附件路径.
     * @param mixed $value
     */
    public function getUrlAttribute($value): string
    {
        return $value ? link_file($value) : '';
    }

    /**
     * ID作用域
     * @param mixed $query
     * @param mixed $value
     * @return string
     */
    public function scopeId($query, $value)
    {
        if (is_array($value)) {
            return $query->whereIn('id', $value);
        }
        if ($value !== '') {
            return $query->where('id', $value);
        }
        return null;
    }

    /**
     * 分类id作用域
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeCid($query, $value)
    {
        return $value !== '' ? $query->where('cid', $value) : null;
    }

    /**
     * 分类企业id作用域
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeEntids($query, $value)
    {
        return $value !== '' ? $query->where('entid', $value)->orWhere('entid', 0) : null;
    }

    /**
     * 分类企业id作用域
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeEntid($query, $value)
    {
        return $value !== '' ? $query->where('entid', $value) : null;
    }

    /**
     * name作用域
     * @param Builder $query
     * @param mixed $value
     * @return mixed
     */
    public function scopeName($query, $value)
    {
        $query->where(function ($query) use ($value) {
            $query->where('name', 'LIKE', '%' . $value . '%')->orWhere('real_name', 'LIKE', '%' . $value . '%');
        });
    }

    /**
     * relation_type作用域
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeRelationType($query, $value)
    {
        if (is_array($value)) {
            $query->whereIn('relation_type', $value);
        } elseif ($value !== '') {
            $query->where('relation_type', $value);
        }
    }

    /**
     * file_ext作用域
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeFileExt($query, $value)
    {
        if (is_array($value)) {
            $query->whereIn('file_ext', $value);
        } else {
            $query->where('file_ext', $value);
        }
    }

    /**
     * 一对一关联创建人.
     * @return HasOne
     */
    public function card()
    {
        return $this->hasOne(Admin::class, 'uid', 'uid')->select(['id', 'uid', 'name', 'avatar', 'phone']);
    }
}
