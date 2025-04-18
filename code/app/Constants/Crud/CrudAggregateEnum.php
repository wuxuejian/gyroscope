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

namespace App\Constants\Crud;

use MyCLabs\Enum\Enum;

/**
 * 低代码：聚合方式
 * Class CrudAggregateEnum.
 * @email 136327134@qq.com
 * @date 2024/3/7
 */
final class CrudAggregateEnum extends Enum
{
    /**
     * 求和.
     */
    public const AGGREGATE_SUM = 'sum';

    /**
     * 汇总.
     */
    public const AGGREGATE_COUNT = 'count';

    /**
     * 去重复求和.
     */
    public const AGGREGATE_UNIQID_COUNT = 'uniqid_count';

    /**
     * 平均值
     */
    public const AGGREGATE_AVG = 'avg';

    /**
     * 最大值
     */
    public const AGGREGATE_MAX = 'max';

    /**
     * 最小值
     */
    public const AGGREGATE_MIN = 'min';
}
