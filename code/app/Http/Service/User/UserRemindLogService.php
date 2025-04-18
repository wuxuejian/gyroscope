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

namespace App\Http\Service\User;

use App\Http\Dao\User\UserRemindLogDao;
use App\Http\Service\BaseService;
use Illuminate\Contracts\Container\BindingResolutionException;

/**
 * 用户消息提醒日志
 * Class UserRemindLogService.
 *
 * @method insert(array $data)
 */
class UserRemindLogService extends BaseService
{
    /**
     * UserRemindLogService constructor.
     */
    public function __construct(UserRemindLogDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * @return array
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function batchUserRemindList(array $where)
    {
        return $this->dao->column($where, 'id', 'user_id');
    }
}
