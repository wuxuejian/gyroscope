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

namespace App\Http\Model\Assess;

use App\Http\Model\BaseModel;
use App\Http\Model\User\UserEnterprise;
use crmeb\traits\model\TimeDataTrait;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class AssessSpace.
 */
class AssessSpace extends BaseModel
{
    use TimeDataTrait;
    use SoftDeletes;

    /**
     * 表名.
     * @var string
     */
    protected $table = 'assess_space';

    /**
     * 主键.
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * 一对多远程关联用户.
     * @return HasManyThrough
     */
    public function check()
    {
        return $this->hasManyThrough(
            UserEnterprise::class,
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
            UserEnterprise::class,
            AssessPlanUser::class,
            'planid',
            'id',
            'id',
            'test_uid',
        );
    }

    /**
     * 一对多关联用户id.
     * @return HasMany
     */
    public function user()
    {
        return $this->hasMany(AssessPlanUser::class, 'planid', 'id')->groupBy('check_uid');
    }

    /**
     * 一对多关联指标.
     * @return HasMany
     */
    public function target()
    {
        return $this->hasMany(AssessTarget::class, 'spaceid', 'id');
    }

    public function scopeCheckUid($query, $value)
    {
        if (is_array($value)) {
            $query->whereIn('id', $value);
        }
    }

    public function scopeTestUid($query, $value)
    {
        if (is_array($value)) {
            $query->whereIn('id', $value);
        }
    }

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
     * id作用域
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeAssessid($query, $value)
    {
        if (is_array($value)) {
            $query->whereIn('assessid', $value);
        } else {
            $query->where('assessid', $value);
        }
    }

    /**
     * id作用域
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeTargetid($query, $value)
    {
        if (is_array($value)) {
            $query->whereIn('targetid', $value);
        } elseif ($value !== '') {
            $query->where('targetid', $value);
        }
    }
}
