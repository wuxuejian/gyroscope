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

namespace App\Constants;

use MyCLabs\Enum\Enum;

/**
 * 考核相关.
 */
final class AssessEnum extends Enum
{
    /**
     * 考核周期：周.
     */
    public const PERIOD_WEEK = 1;

    /**
     * 考核周期：月.
     */
    public const PERIOD_MONTH = 2;

    /**
     * 考核周期：年.
     */
    public const PERIOD_YEAR = 3;

    /**
     * 考核周期：半年.
     */
    public const PERIOD_HALF_YEAR = 4;

    /**
     * 考核周期：季度.
     */
    public const PERIOD_QUARTER = 5;

    /**
     * 绩效删除.
     */
    public const ASSESS_DELETE = -1;

    /**
     * 绩效制定.
     */
    public const ASSESS_DRAFT = 0;

    /**
     * 绩效自评.
     */
    public const ASSESS_SELF_APPRAISAL = 1;

    /**
     * 绩效上级评价.
     */
    public const ASSESS_EVALUATION = 2;

    /**
     * 绩效审核.
     */
    public const ASSESS_VERIFY = 3;

    /**
     * 绩效结束
     */
    public const ASSESS_FINISH = 4;

    /**
     * 绩效申诉.
     */
    public const ASSESS_APPEAL = 5;

    /**
     * 绩效申诉驳回.
     */
    public const ASSESS_REJECT = 6;

    /**
     * 数据关联通知.
     */
    public const LINK_NOTICE = [
        NoticeEnum::ASSESS_PUBLISH_TYPE,
        NoticeEnum::ASSESS_START_TYPE,
        NoticeEnum::ASSESS_SELF_TYPE,
        NoticeEnum::ASSESS_EXAMINE_TYPE,
        NoticeEnum::ASSESS_ABNORMAK_TYPE,
        NoticeEnum::ASSESS_END_TYPE,
        NoticeEnum::ASSESS_RESULT_END_TYPE,
        NoticeEnum::ASSESS_APPEAL_TYPE,
        NoticeEnum::ASSESS_APPEAL_RESULT_TYPE,
        NoticeEnum::ASSESS_EVALUATE_RESULT_TYPE,
    ];

    /**
     * 无数据关联通知.
     */
    public const NOT_LINK_NOTICE = [
        NoticeEnum::ASSESS_TARGET_TYPE,
    ];
}
