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

namespace crmeb\interfaces;

use Illuminate\Database\Query\Builder;

/**
 * Interface TimeDataInterface.
 */
interface TimeDataInterface
{
    /**
     * 设置时间查询字段.
     * @return $this
     */
    public function setTimeField(string $timeField);

    public function getTimeField(): string;

    /**
     * 时间查询作用域
     * @param Builder $query
     * @param mixed $value
     * @return mixed
     */
    public function scopeTime($query, $value);
}
