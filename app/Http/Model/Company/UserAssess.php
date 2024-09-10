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

namespace App\Http\Model\Company;

use App\Http\Model\Admin\Admin;
use App\Http\Model\Assess\AssessPlan;
use App\Http\Model\Assess\AssessSpace;
use App\Http\Model\Assess\AssessTarget;
use App\Http\Model\BaseModel;
use App\Http\Model\Frame\Frame;
use crmeb\traits\model\TimeDataTrait;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * 考核记录
 * Class UserAssess.
 */
class UserAssess extends BaseModel
{
    use TimeDataTrait;
    use SoftDeletes;

    /**
     * 伪删除字段.
     */
    public const DELETED_AT = 'delete';

    /**
     * 表名.
     * @var string
     */
    protected $table = 'assess';

    /**
     * 主键.
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * @return HasOne
     */
    public function userEnt()
    {
        return $this->hasOne(Admin::class, 'id', 'test_uid');
    }

    /**
     * 创建时间年查询.
     * @param Builder $query
     * @param mixed $value
     * @return Builder
     */
    public function scopeCreatedAtYear($query, $value)
    {
        return $query->whereRaw(DB::raw('YEAR(created_at) = \'' . $value . '\''));
    }

    /**
     * 部门关联.
     * @return HasOne
     */
    public function frame()
    {
        return $this->hasOne(
            Frame::class,
            'id',
            'frame_id'
        )->select([
            'frame.id',
            'frame.name',
            'frame.user_count',
        ]);
    }

    /**
     * 考核计划关联.
     * @return HasOne
     */
    public function plan()
    {
        return $this->hasOne(AssessPlan::class, 'id', 'planid');
    }

    /**
     * 考核计划详情关联.
     * @return HasOne
     */
    public function planInfo()
    {
        return $this->hasOne(AssessPlan::class, 'id', 'planid');
    }

    /**
     * 考核人.
     * @param mixed $query
     * @param mixed $value
     * @return mixed
     */
    public function scopeCheckUid($query, $value)
    {
        if (is_array($value)) {
            if (count($value)) {
                return $query->whereIn('check_uid', $value);
            }
        } elseif ($value !== '') {
            return $query->where('check_uid', $value);
        }
    }

    /**
     * 被考核人.
     * @param mixed $query
     * @param mixed $value
     * @return mixed
     */
    public function scopeTestUid($query, $value)
    {
        if (is_array($value)) {
            $query->whereIn('test_uid', $value);
        } elseif ($value !== '') {
            $query->where('test_uid', $value);
        }
    }

    /**
     * 企业ID.
     * @param mixed $query
     * @param mixed $value
     * @return mixed
     */
    public function scopeEntid($query, $value)
    {
        if (is_array($value)) {
            return $query->whereIn('entid', $value);
        }
        if ($value !== '') {
            return $query->where('entid', $value);
        }
    }

    /**
     * 考核计划ID.
     * @param mixed $query
     * @param mixed $value
     * @return mixed
     */
    public function scopePlanid($query, $value)
    {
        if (is_array($value)) {
            return $query->whereIn('planid', $value);
        }
        if ($value !== '') {
            return $query->where('planid', $value);
        }
    }

    /**
     * 考核批次ID.
     * @param mixed $query
     * @param mixed $value
     * @return mixed
     */
    public function scopeNumber($query, $value)
    {
        if (is_array($value)) {
            return $query->whereIn('number', $value);
        }
        if ($value !== '') {
            return $query->where('number', $value);
        }
    }

    /**
     * 级别.
     * @param mixed $query
     * @param mixed $value
     * @return mixed
     */
    public function scopeGrade($query, $value)
    {
        if (is_array($value)) {
            return $query->whereIn('grade', $value);
        }
        if ($value !== '') {
            return $query->where('grade', $value);
        }
    }

    /**
     * 部门ID筛选.
     * @param mixed $query
     * @param mixed $value
     * @return mixed
     */
    public function scopeFrameId($query, $value)
    {
        if (is_array($value)) {
            if ($value) {
                $query->whereIn('frame_id', $value);
            }
        } elseif ($value !== '') {
            $query->where('frame_id', $value);
        }
    }

    /**
     * 名称.
     * @param mixed $query
     * @param mixed $value
     * @return mixed
     */
    public function scopeName($query, $value)
    {
        if ($value !== '') {
            return $query->where('name', $value);
        }
    }

    /**
     * 启用状态
     * @param mixed $query
     * @param mixed $value
     * @return mixed
     */
    public function scopeIsShow($query, $value)
    {
        if ($value !== '') {
            return $query->where('is_show', $value);
        }
    }

    /**
     * 目标制定状态
     * @param mixed $query
     * @param mixed $value
     * @return mixed
     */
    public function scopeMakeStatus($query, $value)
    {
        if ($value !== '') {
            return $query->where('make_status', $value);
        }
    }

    /**
     * 当前时间筛选/执行期
     * @param mixed $query
     * @param mixed $value
     * @return mixed
     */
    public function scopeExecTime($query, $value)
    {
        if ($value !== '') {
            return $query->where('start_time', '<', now()->toDateTimeString())->where('verify_time', '>', now()->toDateTimeString());
        }
    }

    /**
     * 当前时间筛选/执行期
     * @param mixed $query
     * @param mixed $value
     * @return mixed
     */
    public function scopeEndTime($query, $value)
    {
        if ($value !== '') {
            return $query->where('end_time', '>=', $value);
        }
    }

    /**
     * 当前时间筛选/执行期
     * @param mixed $query
     * @param mixed $value
     * @return mixed
     */
    public function scopeStartTime($query, $value)
    {
        if ($value !== '') {
            return $query->where('start_time', '>=', $value);
        }
    }

    /**
     * 一对多远程关联考核内容.
     * @return HasManyThrough
     */
    public function info()
    {
        return $this->hasManyThrough(
            AssessTarget::class,
            AssessSpace::class,
            'assessid',
            'spaceid',
            'id',
            'id',
        )->groupBy('spaceid');
    }

    /**
     * 一对一关联考核人.
     * @return HasOne
     */
    public function check()
    {
        return $this->hasOne(Admin::class, 'id', 'check_uid')->select(['id', 'uid', 'name', 'phone', 'avatar']);
    }

    /**
     * 一对一关联被考核人.
     * @return HasOne
     */
    public function test()
    {
        return $this->hasOne(Admin::class, 'id', 'test_uid')->select(['id', 'uid', 'name', 'phone', 'avatar']);
    }

    public function scopeStatus($query, $value)
    {
        if (is_array($value)) {
            $query->whereIn('status', $value);
        } elseif ($value !== '') {
            $query->where('status', $value);
        }
    }

    /**
     * 上级评价状态：0、未评价；1、已评价；2、草稿。
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeCheckStatus($query, $value)
    {
        if (is_array($value)) {
            $query->whereIn('check_status', $value);
        } elseif ($value !== '') {
            $query->where('check_status', $value);
        }
    }

    /**
     * 过滤状态
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeNotStatus($query, $value)
    {
        if (is_array($value)) {
            $query->whereNotIn('status', $value);
        } elseif ($value !== '') {
            $query->where('status', '<>', $value);
        }
    }
}
