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

namespace App\Http\Model\User;

use App\Http\Model\Admin\Admin;
use App\Http\Model\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * 笔记
 * Class UserMemorial.
 */
class UserMemorial extends BaseModel
{
    /**
     * 自动写入时间.
     * @var bool
     */
    public $timestamps = false;

    /**
     * 表名.
     * @var string
     */
    protected $table = 'user_memorial';

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
     * Content获取器.
     * @return string
     */
    public function getContentAttribute($value)
    {
        return htmlspecialchars_decode($value);
    }

    /**
     * Content修改器.
     */
    public function setContentAttribute($value)
    {
        $this->attributes['content'] = htmlspecialchars($value);
    }

    /**
     * 一对一关联财务流水类型.
     * @return HasOne
     */
    public function cate()
    {
        return $this->hasOne(UserMemorialCategory::class, 'id', 'cate_id');
    }

    /**
     * types作用域
     * @param Builder $query
     * @return mixed
     */
    public function scopeTitle($query, $value)
    {
        if ($value !== '') {
            return $query->where('title', 'LIKE', '%' . $value . '%')->orWhere('content', 'LIKE', '%' . $value . '%');
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

    /**
     * pid作用域
     * @param Builder $query
     * @return mixed
     */
    public function scopePid($query, $value)
    {
        if ($value !== '') {
            return $query->where('pid', $value);
        }
    }

    /**
     * pid作用域
     * @param Builder $query
     * @return mixed
     */
    public function scopeUid($query, $value)
    {
        if ($value !== '') {
            return $query->where('uid', $value);
        }
    }

    /**
     * 时间查询作用域
     * @param Builder $query
     * @return mixed
     */
    public function scopeTime($query, $value)
    {
        $createTimeField = $this->getTimeField();
        switch ($value) {
            case 'today':// 今天
                return $query->whereDate($createTimeField, Carbon::today()->toDateString());
            case 'week':// 本周
                return $query->whereBetween($createTimeField, [Carbon::today()->startOfWeek()->toDateString(), Carbon::today()->endOfWeek()->toDateString()]);
            case 'month':// 本月
                return $query->whereBetween($createTimeField, [Carbon::today()->startOfMonth()->toDateString(), Carbon::today()->endOfMonth()->toDateString()]);
            case 'year':// 今年
                return $query->whereBetween($createTimeField, [Carbon::today()->startOfYear()->toDateString(), Carbon::today()->endOfYear()->toDateString()]);
            case 'yesterday':// 昨天
                return $query->whereDate($createTimeField, Carbon::yesterday()->toDateString());
            case 'last year':// 去年
                return $query->whereDate($createTimeField, Carbon::today()->subYear()->year);
            case 'last week':// 上周
                return $query->whereBetween($createTimeField, [Carbon::today()->subWeek()->startOfWeek()->toDateString(), Carbon::today()->subWeek()->endOfWeek()->toDateString()]);
            case 'last month':// 上个月
                return $query->whereBetween($createTimeField, [Carbon::today()->subMonth()->startOfMonth()->toDateString(), Carbon::today()->subMonth()->endOfMonth()->toDateString()]);
            case 'quarter':// 本季度
                return $query->whereBetween($createTimeField, [Carbon::today()->startOfQuarter()->toDateString(), Carbon::today()->endOfQuarter()->toDateString()]);
            case 'lately7':// 近7天
                return $query->whereBetween($createTimeField, [Carbon::today()->subDays(7)->toDateString(), Carbon::today()->toDateString()]);
            case 'lately30':
                return $query->whereBetween($createTimeField, [Carbon::today()->subDays(30)->toDateString(), Carbon::today()->toDateString()]);
            default:
                if (strstr($value, '-') !== false) {
                    [$startTime, $endTime] = explode('-', $value);
                    $startTime             = str_replace('/', '-', trim($startTime));
                    $endTime               = str_replace('/', '-', trim($endTime));
                    if ($startTime && $endTime && $startTime != $endTime) {
                        return $query->whereBetween($createTimeField, [$startTime, $endTime]);
                    }
                    if ($startTime && $endTime && $startTime == $endTime) {
                        return $query->whereBetween($createTimeField, [$startTime, date('Y-m-d H:i:s', strtotime($endTime) + 86400)]);
                    }
                    if (! $startTime && $endTime) {
                        return $query->whereTime($createTimeField, '<', $endTime);
                    }
                    if ($startTime && ! $endTime) {
                        return $query->whereTime($createTimeField, '>=', $startTime);
                    }
                } elseif (preg_match('/^lately+[1-9]{1,3}/', $value)) {
                    // 最近天数 lately[1-9] 任意天数
                    $day = (int) str_replace('lately', '', $value);
                    if ($day) {
                        return $query->whereBetween($createTimeField, [Carbon::today()->subDays($day)->toDateString(), Carbon::today()->toDateString()]);
                    }
                }
        }
    }

    /**
     * updated_at 作用域
     */
    public function scopeUpdatedAt($query, $value)
    {
        if ($value !== '') {
            $query->where(DB::raw("DATE_FORMAT(updated_at,'%Y-%m')"), $value);
        }
    }

    /**
     * 一对一关联文件夹.
     * @return HasOne
     */
    public function parent()
    {
        return $this->hasOne(UserMemorialCategory::class, 'id', 'pid');
    }
}
