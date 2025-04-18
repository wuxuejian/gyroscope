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

use App\Http\Model\Admin\Admin;
use App\Http\Model\BaseModel;
use App\Http\Model\Frame\Frame;
use crmeb\traits\model\TimeDataTrait;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * Class AssessPlan.
 */
class AssessPlan extends BaseModel
{
    use TimeDataTrait;

    /**
     * 表名.
     * @var string
     */
    protected $table = 'assess_plan';

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
            Admin::class,
            AssessPlanUser::class,
            'planid',
            'id',
            'id',
            'check_uid',
        )->groupBy('check_uid')->select(['admin.id', 'admin.name', 'admin.avatar', 'admin.phone', 'admin.uid']);
    }

    /**
     * 一对多远程关联组织架构.
     * @return HasManyThrough
     */
    public function testFrame()
    {
        return $this->hasManyThrough(
            Frame::class,
            AssessFrame::class,
            'planid',
            'id',
            'id',
            'test_frame_id'
        );
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
        )
            ->where(['status' => 1])->select(['admin.id', 'admin.name', 'admin.avatar', 'admin.phone', 'admin.uid', 'assess_plan_user.planid'])
            ->groupBy(['admin.id']);
    }

    /**
     * 一对多关联用户id.
     * @return HasMany
     */
    public function user()
    {
        return $this->hasMany(AssessPlanUser::class, 'planid', 'id')->groupBy('check_uid');
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

    public function scopeStatus($query, $value)
    {
        if ($value !== '') {
            $query->where('status', $value);
        }
    }

    public function scopeId($query, $value)
    {
        if (is_array($value)) {
            $query->whereIn('id', $value);
        } elseif ($value !== '') {
            $query->where('id', $value);
        }
    }

    public function scopePeriod($query, $value)
    {
        if ($value !== '') {
            $query->where('period', $value);
        }
    }

    public function scopeEntid($query, $value)
    {
        if ($value !== '') {
            $query->where('entid', $value);
        }
    }
}
