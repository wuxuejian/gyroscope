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

namespace App\Http\Service\User;

use App\Http\Dao\User\UserPendingDao;
use App\Http\Service\BaseService;
use Illuminate\Contracts\Container\BindingResolutionException;

/**
 * 待办.
 */
class UserPendingService extends BaseService
{
    /**
     * UserPendingServices constructor.
     */
    public function __construct(UserPendingDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取待办列表.
     *
     * @return array
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getPendingList(array $where)
    {
        return $this->dao->getList($where, ['*'], 0, 0, 'id');
    }
}
