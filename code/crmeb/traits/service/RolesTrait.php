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

namespace crmeb\traits\service;

use App\Constants\MenuEnum;
use App\Http\Service\System\RolesService;

trait RolesTrait
{
    public function saveMenuRole(array $menuInfo = [], array $oldInfo = [])
    {
        try {
            if ($menuInfo && $oldInfo) {
                $this->handleEditMenuRole($menuInfo, $oldInfo);
            } elseif ($menuInfo) {
                $this->handleNewMenuRole($menuInfo);
            } else {
                $this->handleDelMenuRole($oldInfo);
            }
        } catch (\Exception $e) {
            // 记录日志或回滚操作
            throw new \RuntimeException('Failed to save menu role: ' . $e->getMessage(), 0, $e);
        }
    }

    public function getRolesByPermission($object, $action)
    {
        // 获取所有策略
        $policies = app('enforcer')->getPolicy();
        $roles    = [];
        foreach ($policies as $policy) {
            // 假设策略格式为 [角色, 对象, 操作]
            if ($policy[1] === $object && $policy[2] === $action) {
                $roles[] = $policy[0];
            }
        }
        // 去除重复的角色
        return array_unique($roles);
    }

    private function handleEditMenuRole(array $menuInfo, array $oldInfo)
    {
        if ($oldInfo['type'] == MenuEnum::TYPE_API) {
            $this->processApi($oldInfo, $menuInfo);
        }
    }

    private function processMenuPaths(array $oldInfo, array $menuInfo, array $roles = [])
    {
        if ($oldInfo['menu_path']) {
            $roles = array_merge($roles, $this->getRolesByPermission($oldInfo['menu_path'], 'MENU_ROUTE'));
            app('enforcer')->deletePermission($oldInfo['menu_path'], 'MENU_ROUTE');
        }
        if ($oldInfo['uni_path']) {
            $roles = array_merge($roles, $this->getRolesByPermission($oldInfo['uni_path'], 'UNI_ROUTE'));
            app('enforcer')->deletePermission($oldInfo['uni_path'], 'UNI_ROUTE');
        }
        $roleIds = app()->get(RolesService::class)->column(['rule_api' => $menuInfo['id']], 'id');
        foreach ($roleIds as $roleId) {
            $roles[] = 'role_' . $roleId;
        }
        if (! $roles) {
            $oldInfo['menu_path'] && app('enforcer')->addPermissionForUser('role_all', $menuInfo['menu_path'], 'MENU_ROUTE');
            $oldInfo['uni_path'] && app('enforcer')->addPermissionForUser('role_all', $menuInfo['uni_path'], 'UNI_ROUTE');
        } else {
            foreach ($roles as $role) {
                $oldInfo['menu_path'] && app('enforcer')->addPermissionForUser($role, $menuInfo['menu_path'], 'MENU_ROUTE');
                $oldInfo['uni_path'] && app('enforcer')->addPermissionForUser($role, $menuInfo['uni_path'], 'UNI_ROUTE');
            }
        }
    }

    private function processApi(array $oldInfo, array $menuInfo, array $roles = [])
    {
        if ($oldInfo['api']) {
            $roles = $this->getRolesByPermission($oldInfo['api'], $oldInfo['methods']);
            app('enforcer')->deletePermission($oldInfo['api'], $oldInfo['methods']);
        }
        $roleIds = app()->get(RolesService::class)->column(['rule_api' => $menuInfo['id']], 'id');
        foreach ($roleIds as $roleId) {
            $roles[] = 'role_' . $roleId;
        }
        if ($menuInfo['api']) {
            if (! $roles) {
                app('enforcer')->addPermissionForUser('role_all', $menuInfo['api'], $menuInfo['methods']);
            } else {
                foreach ($roles as $role) {
                    app('enforcer')->addPermissionForUser($role, $menuInfo['api'], $menuInfo['methods']);
                }
            }
        }
    }

    private function handleNewMenuRole(array $menuInfo)
    {
        if ($menuInfo['type'] == MenuEnum::TYPE_API) {
            $menuInfo['api'] && app('enforcer')->addPermissionForUser('role_all', $menuInfo['api'], $menuInfo['methods']);
        }
    }

    private function handleDelMenuRole(array $oldInfo)
    {
        //        if ($oldInfo['menu_path']) {
        //            app('enforcer')->deletePermission($oldInfo['menu_path'], 'MENU_ROUTE');
        //        }
        //        if ($oldInfo['uni_path']) {
        //            app('enforcer')->deletePermission($oldInfo['uni_path'], 'UNI_ROUTE');
        //        }
        if ($oldInfo['api']) {
            app('enforcer')->deletePermission($oldInfo['api'], $oldInfo['methods']);
        }
    }
}
