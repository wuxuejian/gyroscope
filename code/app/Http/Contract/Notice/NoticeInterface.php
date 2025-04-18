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

namespace App\Http\Contract\Notice;

use Illuminate\Contracts\Container\BindingResolutionException;

interface NoticeInterface
{
    public function sendMessage(string $type, array $prames = [], array $other = [], int $linkId = 0, int $linkStatus = 0);

    /**
     * 获取消息列表.
     * @return array
     *               获取消息列表
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getMessageList(string $uuid, int $entId, int|string $cateId = '', string $title = '', int|string $isRead = '', bool $Renlist = true, int|string $isHandle = '', bool $reverse = false): array;

    /**
     * 批量更新.
     * @throws BindingResolutionException
     */
    public function batchUpdate(string $uuid, int $entId, int $isRead, int $cateId, array $ids): int;

    /**
     * 批量删除.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function batchDelete(string $uuid, int $entId, int $cateId, array $ids): int;

    /**
     * 个人中心订阅列表.
     */
    public function getListForUser($uuId, $entId, $where): array;
}
