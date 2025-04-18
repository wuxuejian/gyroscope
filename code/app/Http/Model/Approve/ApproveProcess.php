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

use App\Http\Model\Admin\Admin;
use App\Http\Model\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Query\Builder;

/**
 * 审核流程树
 * Class ApproveProcess.
 */
class ApproveProcess extends BaseModel
{
    public $timestamps = false;

    /**
     * 表名.
     * @var string
     */
    protected $table = 'approve_process';

    /**
     * 主键.
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * 一对一关联.
     * @return HasOne
     */
    public function approve()
    {
        return $this->hasOne(Approve::class, 'id', 'approve_id');
    }

    /**
     * 一对一关联用户名片.
     * @return HasOne
     */
    public function card()
    {
        return $this->hasOne(Admin::class, 'id', 'card_id');
    }

    /**
     * id作用域
     * @param Builder $query
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
     * id作用域
     * @param Builder $query
     * @param mixed $value
     */
    public function scopeNotid($query, $value)
    {
        if (is_array($value)) {
            $query->whereNotIn('id', $value);
        }
    }

    /**
     * types作用域
     * @param Builder $query
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
     * @param Builder $query
     * @param mixed $value
     */
    public function scopeTypes($query, $value)
    {
        if (is_array($value)) {
            $query->whereIn('types', $value);
        } elseif ($value) {
            $query->where('types', $value);
        }
    }

    /**
     * approve_id作用域
     * @param Builder $query
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
     * @param Builder $query
     * @param mixed $value
     */
    public function scopeIsInitial($query, $value)
    {
        if ($value !== '') {
            $query->where('is_initial', $value);
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
     * groups作用域
     * @param Builder $query
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
     * @param Builder $query
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
     * @param Builder $query
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
     * @param Builder $query
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

    /**
     * 职级.
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
