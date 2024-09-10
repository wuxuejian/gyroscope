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

namespace App\Http\Model\Crud;

use App\Http\Model\BaseModel;

class SystemCrudApproveProcess extends BaseModel
{
    public $timestamps = false;

    /**
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * @var string
     */
    protected $table = 'system_crud_approve_process';

    /**
     * id作用域
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

    /**
     * 非id作用域
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeNotid($query, $value)
    {
        if (is_array($value)) {
            $query->whereNotIn('id', $value);
        }
    }

    /**
     * 非types作用域
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeNoTypes($query, $value)
    {
        if (is_array($value)) {
            $query->whereNotIn('types', $value);
        } elseif ($value !== '') {
            $query->whereNotIn('types', [$value]);
        }
    }

    /**
     * types作用域
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeTypes($query, $value)
    {
        if ($value !== '') {
            $query->where('types', [$value]);
        }
    }

    /**
     * approve_id作用域
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeApproveId($query, $value)
    {
        if (is_array($value)) {
            $query->whereIn('approve_id', $value);
        } elseif ($value !== '') {
            $query->where('approve_id', $value);
        }
    }

    /**
     * is_initial作用域
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeIsInitial($query, $value)
    {
        if ($value !== '') {
            $query->where('is_initial', $value);
        }
    }

    /**
     * groups作用域
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeGroups($query, $value)
    {
        if ($value !== '') {
            $query->where('groups', $value);
        }
    }

    /**
     * 非uniqued作用域
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeNotUniqued($query, $value)
    {
        if (is_array($value)) {
            $query->whereNotIn('uniqued', $value);
        }
    }

    /**
     * uniqued作用域
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeUniqued($query, $value)
    {
        if (is_array($value)) {
            $query->whereIn('uniqued', $value);
        } elseif ($value !== '') {
            $query->where('uniqued', $value);
        }
    }

    /**
     * parent作用域
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeParent($query, $value)
    {
        if (is_array($value)) {
            $query->whereIn('parent', $value);
        } elseif ($value !== '') {
            $query->where('parent', $value);
        }
    }

    public function scopeNameLike($query, $value)
    {
        if ($value) {
            $query->where('name', 'like', '%' . $value . '%');
        }
    }

    public function scopeLevelLt($query, $value)
    {
        $query->where('level', '<=', $value);
    }

    /**
     * 表单信息存储器.
     * @param mixed $value
     * @return false|string
     */
    protected function setInfoAttribute($value)
    {
        $this->attributes['info'] = $value ? json_encode($value) : '';
    }

    /**
     * 表单信息获取器.
     * @param mixed $value
     * @return array|mixed
     */
    protected function getInfoAttribute($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    /**
     * 表单信息存储器.
     * @param mixed $value
     * @return false|string
     */
    protected function setUserListAttribute($value)
    {
        $this->attributes['user_list'] = $value ? json_encode($value) : '';
    }

    /**
     * @param mixed $value
     * @return array|mixed
     */
    protected function getUserListAttribute($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    /**
     * 表单信息存储器.
     * @param mixed $value
     * @return false|string
     */
    protected function setConditionListAttribute($value)
    {
        $this->attributes['condition_list'] = $value ? json_encode($value) : '';
    }

    /**
     * @param mixed $value
     * @return array|mixed
     */
    protected function getConditionListAttribute($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    /**
     * 表单信息存储器.
     * @param mixed $value
     * @return false|string
     */
    protected function setDepHeadAttribute($value)
    {
        $this->attributes['dep_head'] = $value ? json_encode($value) : '';
    }

    /**
     * @param mixed $value
     * @return array|mixed
     */
    protected function getDepHeadAttribute($value)
    {
        return $value ? json_decode($value, true) : [];
    }
}
