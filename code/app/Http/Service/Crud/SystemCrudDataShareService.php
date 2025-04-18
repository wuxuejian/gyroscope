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

namespace App\Http\Service\Crud;

use App\Http\Dao\Crud\SystemCrudDataShareDao;
use App\Http\Service\BaseService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * 数据共享记录.
 */
class SystemCrudDataShareService extends BaseService
{
    public function __construct(SystemCrudDataShareDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取共享的记录id.
     * @return array
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function getUserShareDataIds(int $crudId, int $userId)
    {
        if (! $userId) {
            return [];
        }

        return $this->dao->column(['crud_id' => $crudId, 'user_id' => $userId], 'data_id');
    }

    /**
     * 根据共享id获取数据id.
     * @return array
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function shareIdByDataIds(int $crudId, array $shareIds)
    {
        if (! $shareIds) {
            return [];
        }

        return $this->dao->column(['crud_id' => $crudId, 'share_id' => $shareIds], 'data_id');
    }
}
