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

namespace App\Http\Model\User;

use App\Http\Model\BaseModel;

/**
 * 用户消息提醒日志
 * Class UserRemindLog.
 */
class UserRemindLog extends BaseModel
{
    /**
     * @var string
     */
    protected $table = 'user_remind_log';

    /**
     * @var string
     */
    protected $primaryKey = 'id';
}
