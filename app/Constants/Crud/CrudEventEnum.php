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
 * 低代码：触发器类型.
 */
final class CrudEventEnum extends Enum
{
    /**
     * 发送通知.
     */
    public const EVENT_SEND_NOTICE = 'send_notice';

    /**
     * 数据校验.
     */
    public const EVENT_DATA_CHECK = 'data_check';

    /**
     * 自动创建.
     */
    public const EVENT_AUTO_CREATE = 'auto_create';

    /**
     * 分组聚合.
     */
    public const EVENT_GROUP_AGGREGATE = 'group_aggregate';

    /**
     * 字段聚合.
     */
    public const EVENT_FIELD_AGGREGATE = 'field_aggregate';

    /**
     * 字段更新.
     */
    public const EVENT_FIELD_UPDATE = 'field_update';

    /**
     * 自动审核.
     */
    public const EVENT_AUTH_APPROVE = 'auto_approve';

    /**
     * 自动撤销审批.
     */
    public const EVENT_AUTO_REVOKE_APPROVE = 'auto_revoke_approve';

    /**
     * 获取数据.
     */
    public const EVENT_GET_DATA = 'get_data';

    /**
     * 推送数据
     */
    public const EVENT_PUSH_DATA = 'push_data';

    /**
     * 日程待办.
     */
    public const EVENT_TO_DO_SCHEDULE = 'to_do_schedule';
}
