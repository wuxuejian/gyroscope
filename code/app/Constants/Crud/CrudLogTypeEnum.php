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

namespace App\Constants\Crud;

use MyCLabs\Enum\Enum;

class CrudLogTypeEnum extends Enum
{
    /**
     * 新增.
     */
    public const CREATE_TYPE = 'create';

    /**
     * 修改.
     */
    public const UPDATE_TYPE = 'update';

    /**
     * 删除.
     */
    public const DELETE_TYPE = 'delete';

    /**
     * 创建共享.
     */
    public const SHARE_CREATE = 'share_create';

    /**
     * 删除共享.
     */
    public const SHARE_DELETE_TYPE = 'share_delete';

    /**
     * 分享修改.
     */
    public const SHARE_UPDATE_TYPE = 'share_update';

    /**
     * 转移.
     */
    public const TRANSFER_TYPE = 'transfer';

    /**
     * 审批.
     */
    public const APPROVE_TYPE = 'approve';
}
