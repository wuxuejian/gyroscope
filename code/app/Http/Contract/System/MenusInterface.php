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

namespace App\Http\Contract\System;

/**
 * 菜单.
 */
interface MenusInterface
{
    /**
     * @param null $sort
     */
    public function getMenusList(array $where, int $page = 0, int $limit = 0, array $field = ['*'], array|string $sort = 'id', array $with = []): array;

    /**
     * 获取企业全部菜单.
     * @param int $entId 企业ID
     * @return mixed
     */
    public function getMenusForCompany(int $entId, bool $disableId = false): array;

    /**
     * 保存企业菜单.
     * @param int $entId 企业ID
     * @return mixed
     */
    public function saveMenusForCompany(int $entId, array $rules = [], array $apis = []): bool;

    /**
     * 获取初始用户菜单.
     * @param string $uuid 用户ID
     * @return mixed
     */
    public function getMenusForInitialUser(string $uuid): array;

    /**
     * 获取初始企业用户菜单.
     * @return mixed
     */
    public function getMenusForCompanyUser(string $uuid): array;

    /**
     * 获取用户全部菜单.
     * @return mixed
     */
    public function getMenusForUser(string $uuid, int $entId): array;

    /**
     * 获取用户全部菜单.
     * @return mixed
     */
    public function getMenusForUni(string $uuid, int $entId): array;

    public function getCascaderMenus(array $where = ['status' => 1], $disabled = false, $isDefault = []): array;

    /**
     * 获取菜单新增表单.
     * @return mixed
     */
    public function getCreateForm(int $entid): array;

    /**
     * 保存菜单信息.
     */
    public function saveMenu(array $menuInfo, int $entId, int $id = 0): array;

    /**
     * 获取菜单修改表单.
     * @return mixed
     */
    public function getEditForm(): array;

    /**
     * 删除菜单.
     */
    public function deleteMenu(int $id, int $entId): bool;

    /**
     * 获取菜单唯一值
     */
    public function getMenuUnique(array|object $ids, bool $isRule = true): array;

    /**
     * 获取权限菜单ID值
     */
    public function getMenuId(array|object $uniques, bool $isRule = true): array;
}
