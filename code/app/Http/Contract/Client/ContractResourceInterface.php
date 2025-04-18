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

namespace App\Http\Contract\Client;

use Illuminate\Contracts\Container\BindingResolutionException;

interface ContractResourceInterface
{
    /**
     * 获取列表.
     */
    public function getList(array $where, array $field = ['*'], array|string $sort = 'id', array $with = []): array;

    /**
     * 获取详情.
     */
    public function getInfo(array $where, array $field = ['*'], array $with = []): array;

    /**
     * 保存.
     * @throws BindingResolutionException
     */
    public function save(array $data): mixed;

    /**
     * 修改.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function update(int $id, array $data): mixed;
}
