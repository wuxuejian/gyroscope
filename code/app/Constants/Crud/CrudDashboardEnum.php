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

final class CrudDashboardEnum extends Enum
{
    /**
     * 统计数值
     */
    public const STATISTIC_NUMERIC = 'statistic_numeric';

    /**
     * 进度条
     */
    public const PROGRESS_BAR = 'progress_bar';

    /**
     * 柱状图.
     */
    public const BAR_CHART = 'bar_chart';

    /**
     * 条形图.
     */
    public const COLUMN_BAR = 'column_chart';

    /**
     * 折线图.
     */
    public const LINE_CHART = 'line_chart';

    /**
     * 漏斗图.
     */
    public const FUNNEL_PLOT = 'funnel_plot';

    /**
     * 饼图.
     */
    public const PIE_CHART = 'pie_chart';

    /**
     * 雷达图.
     */
    public const RADAR_CHART = 'radar_chart';

    /**
     * 数据列表.
     */
    public const DATA_LIST = 'data_list';
}
