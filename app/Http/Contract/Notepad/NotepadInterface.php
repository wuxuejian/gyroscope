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

namespace App\Http\Contract\Notepad;

/**
 * 个人笔记.
 */
interface NotepadInterface
{
    /**
     * 获取列表.
     */
    public function getList(array $where, array $field = ['*'], array|string $sort = 'updated_at', array $with = []): array;

    /**
     * 保存数据.
     */
    public function saveData($data): bool;

    /**
     * 通过ID修改.
     */
    public function updateById(int $id, array $data): bool;

    /**
     * 获取记录详情.
     */
    public function getInfoById(int $id): array;

    /**
     * 分组数据.
     */
    public function getGroupList(array $where): array;

    /**
     * 移动端列表.
     */
    public function getListForApp(array $where, array $field = ['*'], array|string $sort = 'updated_at', array $with = []): array;
}
