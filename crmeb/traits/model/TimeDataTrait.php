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

namespace crmeb\traits\model;

use App\Http\Model\BaseModel;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;

/**
 * 时间搜索
 * Trait TimeDataTrait.
 *
 * @mixin  BaseModel
 */
trait TimeDataTrait
{
    /**
     * 时间查询字段名.
     *
     * @var null
     */
    protected $timeField;

    /**
     * 设置时间查询字段.
     *
     * @return $this
     */
    public function setTimeField(string $timeField)
    {
        $this->timeField = $timeField;

        return $this;
    }

    /**
     * 获取时间查询字段.
     *
     * @return null|string
     */
    public function getTimeField(): string
    {
        if (! $this->timeField) {
            $this->timeField = $this->getCreatedAtColumn();
        }

        return $this->timeField;
    }

    /**
     * 时间查询作用域
     *
     * @param Builder $query
     * @param mixed $value
     *
     * @return mixed
     */
    public function scopeTime($query, $value)
    {
        $createTimeField = $this->getTimeField();
        switch ($value) {
            case 'today':// 今天
                return $query->whereDate($createTimeField, Carbon::today()->toDateString());
            case 'week':// 本周
                return $query->whereBetween($createTimeField, [Carbon::today()->startOfWeek()->toDateTimeString(), Carbon::today()->endOfWeek()->toDateTimeString()]);
            case 'month':// 本月
                return $query->whereBetween($createTimeField, [Carbon::today()->startOfMonth()->toDateTimeString(), Carbon::today()->endOfMonth()->toDateTimeString()]);
            case 'year':// 今年
                return $query->whereBetween($createTimeField, [Carbon::today()->startOfYear()->toDateTimeString(), Carbon::today()->endOfYear()->toDateTimeString()]);
            case 'yesterday':// 昨天
                return $query->whereDate($createTimeField, Carbon::yesterday()->toDateString());
            case 'last year':// 去年
                return $query->whereDate($createTimeField, Carbon::today()->subYear()->year);
            case 'last week':// 上周
                return $query->whereBetween($createTimeField, [Carbon::today()->subWeek()->startOfWeek()->toDateTimeString(), Carbon::today()->subWeek()->endOfWeek()->toDateTimeString()]);
            case 'last month':// 上个月
                return $query->whereBetween($createTimeField, [Carbon::today()->subMonth()->startOfMonth()->toDateTimeString(), Carbon::today()->subMonth()->endOfMonth()->toDateTimeString()]);
            case 'quarter':// 本季度
                return $query->whereBetween($createTimeField, [Carbon::today()->startOfQuarter()->toDateTimeString(), Carbon::today()->endOfQuarter()->toDateTimeString()]);
            case 'lately7':// 近7天
                return $query->whereBetween($createTimeField, [Carbon::today()->subDays(7)->toDateTimeString(), Carbon::today()->toDateTimeString()]);
            case 'lately30':
                return $query->whereBetween($createTimeField, [Carbon::today()->subDays(30)->toDateTimeString(), Carbon::today()->toDateTimeString()]);
            case 'future30':
                return $query->whereBetween($createTimeField, [Carbon::today()->toDateTimeString(), Carbon::today()->addDays(30)->toDateTimeString()]);
            default:
                if (str_contains($value, '-')) {
                    [$startTime, $endTime] = explode('-', $value);
                    $startTime             = str_replace('/', '-', trim($startTime));
                    $endTime               = str_replace('/', '-', trim($endTime));
                    if (! str_contains($startTime, ':') && ! str_contains($endTime, ':')) {
                        $endDate = Carbon::parse($endTime)->timezone(config('app.timezone'))->addDay()->toDateString();
                        return $query->whereDate($createTimeField, '>=', $startTime)->whereDate($createTimeField, '<', $endDate);
                    }
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
                        return $query->whereBetween($createTimeField, [Carbon::today()->subDays($day)->toDateTimeString(), Carbon::today()->toDateTimeString()]);
                    }
                }
        }
    }
}
