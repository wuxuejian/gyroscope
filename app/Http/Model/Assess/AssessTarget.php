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

namespace App\Http\Model\Assess;

use App\Http\Model\Admin\Admin;
use App\Http\Model\BaseModel;
use crmeb\traits\model\TimeDataTrait;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder;

/**
 * 指标模板
 * Class AssessTarget.
 */
class AssessTarget extends BaseModel
{
    use TimeDataTrait;
    use SoftDeletes;

    /**
     * 表名.
     * @var string
     */
    protected $table = 'assess_target';

    /**
     * 主键.
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * 一对一关联企业用户.
     * @return HasOne
     */
    public function user()
    {
        return $this->hasOne(Admin::class, 'uid', 'uid');
    }

    /**
     * 一对一关联模板类型.
     * @return HasOne
     */
    public function cate()
    {
        return $this->hasOne(AssessTargetCategory::class, 'id', 'cate_id');
    }

    /**
     * 一对多远程关联用户.
     * @return HasManyThrough
     */
    public function check()
    {
        return $this->hasManyThrough(
            Admin::class,
            AssessPlanUser::class,
            'planid',
            'id',
            'id',
            'check_uid',
        )->groupBy('check_uid');
    }

    /**
     * 一对多远程关联用户.
     * @return HasManyThrough
     */
    public function test()
    {
        return $this->hasManyThrough(
            Admin::class,
            AssessPlanUser::class,
            'planid',
            'id',
            'id',
            'test_uid',
        );
    }

    /**
     * types作用域
     * @param Builder $query
     * @return mixed
     */
    public function scopeTypes($query, $value)
    {
        if ($value !== '') {
            return $query->where('types', $value);
        }
    }

    /**
     * name作用域
     * @param Builder $query
     * @return mixed
     */
    public function scopeName($query, $value)
    {
        if ($value !== '') {
            return $query->where('name', 'like', '%' . $value . '%')->orWhere('content', 'LIKE', '%' . $value . '%');
        }
    }

    /**
     * types作用域
     * @param Builder $query
     * @return mixed
     */
    public function scopeCateId($query, $value)
    {
        if ($value !== '') {
            return $query->where('cate_id', $value);
        }
    }

    public function scopeCheckUid($query, $value)
    {
        if (is_array($value)) {
            $query->whereIn('id', $value);
        }
    }

    public function scopeSpaceid($query, $value)
    {
        if (is_array($value)) {
            $query->whereIn('spaceid', $value);
        } elseif ($value !== '') {
            $query->where('spaceid', $value);
        }
    }

    /**
     * id作用域
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
     */
    public function scopeNotId($query, $value)
    {
        if (is_array($value)) {
            $query->whereNotIn('id', $value);
        } elseif ($value !== '') {
            $query->whereNot('id', $value);
        }
    }
}
