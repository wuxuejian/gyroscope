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

namespace App\Http\Service\System;

use App\Constants\CacheEnum;
use App\Constants\CommonEnum;
use App\Constants\MenuEnum;
use App\Constants\RuleEnum;
use App\Http\Contract\System\MenusInterface;
use App\Http\Contract\System\RolesInterface;
use App\Http\Dao\Auth\RoleDao;
use App\Http\Dao\Auth\RoleUserDao;
use App\Http\Dao\Auth\SystemRoleDao;
use App\Http\Service\Admin\AdminService;
use App\Http\Service\BaseService;
use App\Http\Service\Crud\SystemCrudRoleService;
use App\Http\Service\Crud\SystemCrudService;
use App\Http\Service\Frame\FrameAssistService;
use App\Http\Service\Frame\FrameService;
use App\Task\system\SystemRoleTask;
use Hhxsv5\LaravelS\Swoole\Task\Task;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Cache;
use Lauthz\Facades\Enforcer;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * 权限规则.
 */
class RolesService extends BaseService implements RolesInterface
{
    public SystemRoleDao $roleDao;

    private RoleUserDao $roleUserDao;

    public function __construct(RoleDao $dao, SystemRoleDao $roleDao, RoleUserDao $roleUserDao)
    {
        $this->dao         = $dao;
        $this->roleDao     = $roleDao;
        $this->roleUserDao = $roleUserDao;
    }

    /**
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function getCompanySuperRole(int $entId, bool $withRule = true, bool $withApi = true, string $origin = CommonEnum::ORIGIN_OWN): array
    {
        if ($origin === CommonEnum::ORIGIN_OWN) {
            $types = [];
            if ($withRule) {
                $types[] = MenuEnum::TYPE_MENU;
            }
            if ($withApi) {
                $types[] = MenuEnum::TYPE_BUTTON;
                $types[] = MenuEnum::TYPE_API;
            }
            return app()->get(MenusService::class)->column(['type' => $types], 'id');
        }
        $result = toArray($this->roleDao->get(['type' => MenuEnum::TYPE_SUPER_COMPANY, 'entid_like' => $entId], ['rules', 'apis', 'id']));
        $roles  = [];
        if ($withRule) {
            $roles = array_merge($roles, $result['rules'] ?? []);
        }
        if ($withApi) {
            $roles = array_merge($roles, $result['apis']);
        }
        return array_unique($roles);
    }

    /**
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function getRolesForInitialUser(bool $withRule = true, bool $withApi = true): array
    {
        $roles  = [];
        $result = $this->roleDao->get(['type' => RuleEnum::INITIAL_USER, 'entid_like' => 0], ['rules', 'apis'])?->toArray();
        if (! $result) {
            return $roles;
        }
        if ($withRule) {
            $roles = array_merge($roles, $result['rules']);
        }
        if ($withApi) {
            $roles = array_merge($roles, $result['apis']);
        }
        return array_unique($roles);
    }

    /**
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function getRolesForCompanyUser(bool $withRule = true, bool $withApi = true): array
    {
        $roles  = [];
        $result = $this->roleDao->get(['type' => RuleEnum::INITIAL_COMPANY, 'entid_like' => 0], ['rules', 'apis'])?->toArray();
        if (! $result) {
            return $roles;
        }
        if ($withRule) {
            $roles = array_merge($roles, $result['rules']);
        }
        if ($withApi) {
            $roles = array_merge($roles, $result['rules']);
        }
        return array_unique($roles);
    }

    /**
     * 获取用户权限.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function getRolesForUser(string $uuid, int $entId, bool $withRule = true, bool $withApi = true): array
    {
        $roles = [];
        if (! $uuid) {
            return $roles;
        }
        $roles    = array_merge($roles, $this->getRolesForInitialUser($withRule, $withApi));
        $userInfo = toArray(app()->get(AdminService::class)->get(['uid' => $uuid]));
        if (! $userInfo) {
            return $roles;
        }
        $roles = array_merge($roles, $this->getRolesForCompanyUser($withRule, $withApi));
        if ($userInfo['is_admin']) {
            $roleIds = $this->getCompanySuperRole($entId, $withRule, $withApi);
            return array_merge($roles, $roleIds);
        }
        if (! $userInfo['roles']) {
            return $roles;
        }
        $roleInfo = array_map(function ($item) {
            $apis = [];
            foreach ((array) $item['apis'] as $api) {
                if (is_array($api)) {
                    $apis = array_merge($apis, $api);
                } else {
                    $apis[] = $api;
                }
            }
            $item['apis'] = $apis;
            return $item;
        }, toArray($this->dao->select(['ids' => $userInfo['roles']])));
        if ($withRule) {
            foreach ($roleInfo as $value) {
                $roles = array_merge($roles, $value['rules']);
            }
        }
        if ($withApi) {
            foreach ($roleInfo as $value) {
                $roles = array_merge($roles, $value['apis']);
            }
        }
        return array_unique($roles);
    }

    /**
     * 获取权限对应菜单ID.
     * @return array
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function getRolesId(array $roleIds)
    {
        $roles = array_map(function ($item) {
            $apis = [];
            foreach ((array) $item['apis'] as $api) {
                if (is_array($api)) {
                    $apis = array_merge($apis, $api);
                } else {
                    $apis[] = $api;
                }
            }
            $item['all'] = array_merge($item['rules'], $apis);
            return $item;
        }, toArray($this->dao->select(['id' => $roleIds], ['rules', 'apis'])));
        $roleIds = [];
        foreach ($roles as $role) {
            $roleIds = array_merge($roleIds, $role['all']);
        }
        return $roleIds;
    }

    /**
     * 获取角色列表分页.
     * @param null $sort
     */
    public function getRolesPageList(array $where, int $page, int $limit, array $field, $sort, array $with): array
    {
        return [];
    }

    /**
     * 获取角色列表.
     */
    public function getRolesList(array $where, array $field = ['*'], array|string $sort = 'id', array $with = []): array
    {
        $service = app()->get(FrameService::class);
        $userIds = app()->get(AdminService::class)->column(['status' => 1], 'id');
        return $this->dao->setDefaultSort($sort)->select($where, $field, $with)
            ->each(function (&$item) use ($service, $userIds) {
                $item['frame']      = toArray($service->select(['ids' => $item['frame_id']], ['id', 'name']));
                $item['user_count'] = $this->roleUserDao->count(['role_id' => $item['id'], 'user_ids' => $userIds]);
            })?->toArray();
    }

    /**
     * 获取角色菜单树.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function getRoleInfo(int $entId, int $id = 0): array
    {
        return Cache::tags(CacheEnum::TAG_ROLE)->remember(md5('info_' . $entId . $id), (int) sys_config('system_cache_ttl', 3600), function () use ($entId, $id) {
            $rules        = $this->getCompanySuperRole($entId);
            $ruleInfo     = (object) [];
            $apis         = [];
            $frameService = app()->get(FrameService::class);
            if ($id) {
                $ruleInfo = toArray($this->dao->get($id, ['id', 'role_name', 'rules', 'apis', 'status', 'data_level', 'directly', 'frame_id']));
                if (! $ruleInfo) {
                    throw $this->exception('修改的角色不存在');
                }
                $ruleInfo['frame'] = toArray($frameService->select(['ids' => $ruleInfo['frame_id']], ['id', 'name']));
                $apis              = $ruleInfo['apis'];
            }
            $tree = app()->get(MenusInterface::class)->getCascaderMenus([
                'status' => 1,
                'ids'    => is_string($rules) ? json_decode($rules, true) : $rules,
            ], isDefault: $apis);
            $crud = app()->get(SystemCrudService::class)->select(['crud_id' => 0], ['id', 'table_name', 'table_name_en', 'cate_ids'], [
                'role' => fn ($q) => $q->where('role_id', $id)->select(['created', 'reade', 'reade_frame', 'updated', 'updated_frame', 'deleted', 'deleted_frame', 'role_id', 'crud_id']),
            ])->each(function ($item) use ($frameService, $id) {
                if (! $id) {
                    $item['role'] = [];
                }
                $item['created']        = $item['role']['created'] ?? 0;
                $item['reade']          = $item['role']['reade'] ?? 0;
                $item['reade_frame']    = $item['role']['reade_frame'] ?? [];
                $item['reade_frames']   = $frameService->select(['ids' => $item['reade_frame']], ['id', 'name', 'pid']) ?? [];
                $item['updated']        = $item['role']['updated'] ?? 0;
                $item['updated_frame']  = $item['role']['updated_frame'] ?? [];
                $item['updated_frames'] = $frameService->select(['ids' => $item['updated_frame']], ['id', 'name', 'pid']) ?? [];
                $item['deleted']        = $item['role']['deleted'] ?? 0;
                $item['deleted_frame']  = $item['role']['deleted_frame'] ?? [];
                $item['deleted_frames'] = $frameService->select(['ids' => $item['deleted_frame']], ['id', 'name', 'pid']) ?? [];
                unset($item['role']);
            });
            return ['tree' => $tree, 'rule' => $ruleInfo, 'crud' => $crud];
        });
    }

    /**
     * 添加角色权限.
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function saveRole(int $entId, string $name = '', array $rules = [], array $apis = [], int $status = 1, int $dataLevel = 1, int $directly = 0, array $frameId = [], array $crud = []): bool
    {
        $menuService = app()->get(MenusInterface::class);
        $save        = $this->transaction(function () use ($entId, $rules, $apis, $name, $status, $menuService, $dataLevel, $directly, $frameId, $crud) {
            $save = $this->dao->create([
                'rules'       => $rules,
                'rule_unique' => $menuService->getMenuUnique($rules),
                'apis'        => $apis,
                'api_unique'  => $menuService->getMenuUnique($apis),
                'entid'       => $entId,
                'role_name'   => $name,
                'status'      => $status,
                'data_level'  => $dataLevel,
                'directly'    => $directly,
                'frame_id'    => $frameId,
            ]);
            $crud && app()->get(SystemCrudRoleService::class)->saveRoles($save->id, $crud);
            return $save;
        });
        return $save && Task::deliver(new SystemRoleTask($save->id, $rules, $apis)) && Cache::tags([CacheEnum::TAG_ROLE])->flush();
    }

    /**
     * 角色启用/禁用.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function changeRole(int $entId, int $roleId, int $status): bool
    {
        $role = $this->dao->get($roleId)?->toArray();
        if (! $role) {
            throw $this->exception('未找到可修改的角色');
        }
        if ($role['status'] == $status) {
            return true;
        }
        if ($status) {
            $res = $this->transaction(function () use ($roleId, $entId, $status) {
                $userIds = toArray($this->roleUserDao->column(['role_id' => $roleId], 'user_id'));
                if ($userIds) {
                    foreach ($userIds as $userId) {
                        $uuid = uid_to_uuid((int) $userId);
                        if ($uuid) {
                            Enforcer::addRoleForUserInDomain($uuid, 'role_' . $roleId, 'ent_' . $entId);
                        }
                    }
                    $this->roleUserDao->update(['role_id' => $roleId], ['status' => $status]);
                }
                return (bool) $this->dao->update($roleId, ['status' => $status]);
            });
        } else {
            $res = $this->transaction(function () use ($roleId, $status) {
                Enforcer::deleteRole('role_' . $roleId);
                $this->roleUserDao->update(['role_id' => $roleId], ['status' => $status]);
                return (bool) $this->dao->update($roleId, ['status' => $status]);
            });
        }
        $res && Cache::tags([CacheEnum::TAG_ROLE])->flush();
        return true;
    }

    /**
     * 修改角色权限.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function updateRole(int $id, int $entId, string $name = '', array $rules = [], array $apis = [], int $status = 1, int $dataLevel = 1, int $directly = 0, array $frameId = [], array $crud = []): bool
    {
        if (! $this->dao->exists(['id' => $id, 'entid' => $entId])) {
            throw $this->exception('未找到可修改的角色');
        }
        /** @var MenusInterface $menuService */
        $menuService = app()->get(MenusInterface::class);
        $save        = $this->dao->update($id, [
            'rules'       => $rules,
            'rule_unique' => $menuService->getMenuUnique($rules),
            'apis'        => $apis,
            'api_unique'  => $menuService->getMenuUnique($apis),
            'entid'       => $entId,
            'role_name'   => $name,
            'status'      => $status,
            'data_level'  => $dataLevel,
            'directly'    => $directly,
            'frame_id'    => $frameId,
        ]);
        $crud && app()->get(SystemCrudRoleService::class)->saveRoles($id, $crud);
        Task::deliver(new SystemRoleTask($id, $rules, $apis, true));
        Cache::tags([CacheEnum::TAG_ROLE])->flush();
        return (bool) $save;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function saveSystemRole(int $entId, array $rules = [], array $apis = []): bool
    {
        Cache::tags([CacheEnum::TAG_ROLE])->flush();
        return $this->roleDao->updateOrCreate(['type' => 'enterprise', 'entid_like' => $entId], [
            'rules' => $rules,
            'apis'  => $apis,
            'entid' => $entId,
        ]);
    }

    /**
     * 删除角色.
     */
    public function deleteRole(int $id, int $entId): bool
    {
        if ($this->dao->exists(['id' => $id, 'entid' => $entId])) {
            return $this->transaction(function () use ($id) {
                Enforcer::deletePermissionsForUser('rule_' . $id);
                Enforcer::deletePermissionsForUser('api_' . $id);
                Enforcer::deletePermissionsForUser('uni_' . $id);
                Enforcer::deleteRole('role_' . $id);
                $this->roleUserDao->delete(['role_id' => $id]);
                app()->get(SystemCrudRoleService::class)->delete(['role_id' => $id]);
                Cache::tags([CacheEnum::TAG_ROLE])->flush();
                return (bool) $this->dao->delete($id);
            });
        }
        throw $this->exception('未找到可删除的角色');
    }

    /**
     * 获取角色下的用户UUID.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function getRoleUser(int $id, int $entId): array
    {
        if (! $this->dao->exists(['id' => $id, 'entid' => $entId])) {
            throw $this->exception('无效的角色ID');
        }
        $uid = $this->roleUserDao->column(['role_id' => $id, 'entid' => $entId], 'user_id');
        return app()->get(AdminService::class)->getList(['id' => $uid, 'status' => 1], with: ['frame']);
    }

    /**
     * 获取用户角色.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function getUserRole(int $entId, int $userId): array
    {
        $roleIds  = $this->roleUserDao->column(['user_id' => $userId, 'entid' => $entId], 'role_id');
        $roleList = $this->getRolesList(['entid' => $entId]);
        /** @var MenusInterface $service */
        $service = app()->get(MenusInterface::class);
        $menus   = $service->getMenusForCompany($entId, true)['tree'];
        return [
            'menus'    => $menus,
            'roles'    => $roleIds,
            'roleList' => collect($roleList)->map(function ($item) {
                return ['value' => $item['id'], 'label' => $item['role_name'], 'rules' => $item['rules'], 'apis' => $item['apis']];
            })->all(),
        ];
    }

    /**
     * 用户修改角色.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function changeUserRole(int $entId, int $userId, array $roleIds = []): bool
    {
        $adminService = app()->get(AdminService::class);
        if ($roleIds) {
            return $this->transaction(function () use ($entId, $userId, $roleIds, $adminService) {
                Enforcer::deleteRolesForUserInDomain(uid_to_uuid($userId), 'ent_' . $entId);
                $this->roleUserDao->delete(['user_id' => $userId]);
                Enforcer::addRolesForUser(uid_to_uuid($userId), array_map(function ($val) {
                    return 'role_' . $val;
                }, $roleIds), 'ent_' . $entId);
                foreach ($roleIds as $roleId) {
                    $this->roleUserDao->updateOrCreate([
                        'role_id' => $roleId,
                        'user_id' => $userId,
                    ], [
                        'role_id' => $roleId,
                        'user_id' => $userId,
                        'entid'   => $entId,
                        'status'  => 1,
                    ]);
                }
                Cache::tags([CacheEnum::TAG_ROLE])->flush();
                return (bool) $adminService->update($userId, ['roles' => $roleIds]);
            });
        } else {
            return $this->transaction(function () use ($entId, $userId, $roleIds, $adminService) {
                Enforcer::deleteRolesForUserInDomain(uid_to_uuid($userId), 'ent_' . $entId);
                $this->roleUserDao->delete(['role_id' => $roleIds]);
                Cache::tags([CacheEnum::TAG_ROLE])->flush();
                return (bool) $adminService->update($userId, ['roles' => $roleIds]);
            });
        }
    }

    /**
     * 角色添加用户.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function addRoleUser(int $entId, int $roleId, array $userIds = [], array $frameIds = []): bool
    {
        if (! $roleId) {
            throw $this->exception('角色id不能为空');
        }
        $assistService = app()->get(FrameAssistService::class);
        $userIds       = array_merge($userIds, $assistService->frameIdByUserId($frameIds, $entId));
        $userIds       = array_merge(array_unique($userIds));
        $userIdsList   = $this->roleUserDao->column(['user_ids' => $userIds, 'role_id' => $roleId], 'user_id');
        $res           = $this->transaction(function () use ($userIds, $userIdsList, $roleId, $entId) {
            $data = $newUserId = [];
            foreach ($userIds as $userId) {
                if (! in_array($userId, $userIdsList)) {
                    $data[] = [
                        'role_id' => $roleId,
                        'entid'   => $entId,
                        'user_id' => $userId,
                        'status'  => 1,
                    ];
                    $newUserId[] = $userId;
                }
            }
            if (! $data) {
                throw $this->exception('您选择的用户已全部加入该角色下');
            }
            app()->get(AdminService::class)->updateRole($newUserId, $roleId);
            foreach ($newUserId as $value) {
                $uuid = uid_to_uuid((int) $value);
                if ($uuid) {
                    Enforcer::addRoleForUserInDomain(uid_to_uuid((int) $value), 'role_' . $roleId, 'ent_' . $entId);
                }
            }
            $this->dao->inc($roleId, count($userIds), 'user_count');
            return $this->roleUserDao->insert($data);
        });
        return $res && Cache::tags([CacheEnum::TAG_ROLE])->flush();
    }

    /**
     * 修改角色用户状态
     * @param mixed $status
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function changeRoleUser(int $uid, int $entId, int $roleId, $status): bool
    {
        if ($this->roleUserDao->exists(['role_id' => $roleId, 'user_id' => $uid])) {
            if ($this->roleUserDao->value(['role_id' => $roleId, 'user_id' => $uid], 'status') == $status) {
                return true;
            }
            $res  = $this->roleUserDao->update(['role_id' => $roleId, 'user_id' => $uid], ['status' => $status]);
            $uuid = uid_to_uuid($uid);
            if (Enforcer::hasRoleForUser($uuid, 'role_' . $roleId, 'ent_' . $entId) && ! $status) {
                if ($uuid) {
                    Enforcer::deleteRoleForUserInDomain($uuid, 'role_' . $roleId, 'ent_' . $entId);
                }
            } else {
                Enforcer::addRoleForUserInDomain($uuid, 'role_' . $roleId, 'ent_' . $entId);
            }
            Cache::tags([CacheEnum::TAG_ROLE])->flush();
            return (bool) $res;
        }
        throw $this->exception('修改的成员不存在!');
    }

    /**
     * 角色删除用户.
     * @param mixed $entId
     * @return mixed
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function delRoleUser(int $uid, int $entId, int $roleId): bool
    {
        if ($this->roleUserDao->exists(['role_id' => $roleId, 'user_id' => $uid])) {
            $res = $this->transaction(function () use ($uid, $roleId, $entId) {
                $res = $this->roleUserDao->delete(['role_id' => $roleId, 'user_id' => $uid]);
                $this->dao->dec($roleId, 1, 'user_count');
                $uuid = uid_to_uuid($uid);
                if (Enforcer::hasRoleForUser($uuid, 'role_' . $roleId, 'ent_' . $entId)) {
                    Enforcer::deleteRoleForUserInDomain($uuid, 'role_' . $roleId, 'ent_' . $entId);
                }
                return (bool) $res;
            });
            return $res && Cache::tags([CacheEnum::TAG_ROLE])->flush();
        }
        throw $this->exception('删除的成员不存在!');
    }

    /**
     * 同步权限信息.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function syncRoles()
    {
        $roles = toArray($this->dao->select([]));
        /** @var MenusInterface $menuService */
        $menuService = app()->get(MenusInterface::class);
        foreach ($roles as $role) {
            $this->dao->update($role['id'], [
                'rules' => $menuService->getMenuId($role['rule_unique']),
                'apis'  => $menuService->getMenuId($role['api_unique'], false),
            ]);
        }
    }

    public function checkAuth($uri, $userInfo, $entInfo, $method)
    {
        return true;
        if (! $entInfo) {
            throw $this->exception('接口未授权,无法访问!');
        }
        $roles = Enforcer::getRolesForUserInDomain($userInfo['uid'], 'ent_' . $entInfo['id']);
        $auth  = false;
        if ($roles) {
            foreach ($roles as $role) {
                $auth = Enforcer::hasPermissionForUser('api_' . str_replace('role_', 'rule_', $role), ltrim($uri, 'api'), $method);
                if ($auth) {
                    break;
                }
            }
        }
        if (! $auth) {
            throw $this->exception('接口未授权,无法访问!');
        }
    }

    public function checkAuthStatus($uuid, $entId, $permission): bool
    {
        $menus = app()->get(MenusService::class)->getMenusForUser($uuid, $entId, false);
        //        $roles = Enforcer::getRolesForUserInDomain($uuid, 'ent_' . $entId);
        $auth = false;
        if ($menus) {
            foreach ($menus as $menu) {
                if ($permission == $menu['menu_path']) {
                    $auth = true;
                    break;
                }
                //                if (Enforcer::hasPermissionForUser(str_replace('role_', 'rule_', $role), $permission)) {
                //                    $auth = true;
                //                    break;
                //                }
            }
        }
        return $auth;
    }

    /**
     * TODO 获取某个身份下的用户user_id.
     * @return array
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function getRuleUserIds(int $entId, string $type = RuleEnum::FINANCE_TYPE)
    {
        $menusService = app()->get(MenusService::class);
        $users        = app()->get(AdminService::class)->column(['status' => 1], ['uid', 'id']);
        $menuIds      = match ($type) {
            RuleEnum::FINANCE_TYPE        => $menusService->column(['type' => 0, 'menu_path' => '/fd/'], 'id'),
            RuleEnum::PERSONNEL_TYPE      => $menusService->column(['type' => 0, 'menu_path' => '/hr/'], 'id'),
            RuleEnum::ADMINISTRATION_TYPE => $menusService->column(['type' => 0, 'menu_path' => '/administration/'], 'id'),
            default                       => $menusService->column(['type' => 0], 'id'),
        };
        $userIds = [];
        foreach ($users as $user) {
            $userRoles = $this->getRolesForUser($user['uid'], $entId, withApi: false);
            if ($userRoles && array_intersect($userRoles, $menuIds)) {
                $userIds[] = $user['id'];
            }
        }
        return $userIds;
    }

    /**
     * 验证系统默认角色权限.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function checkDefaultRule(int $userId, int $entId, string $type = RuleEnum::FINANCE_TYPE): bool
    {
        $ident = app()->get(AdminService::class)->value($userId, 'is_admin');
        return in_array($userId, $this->getRuleUserIds($entId, $type)) || $ident;
    }

    /**
     * 获取某个管理员的权限ID.
     * @return array
     */
    public function getAdminRole(array $ids, string $field = 'rules')
    {
        return Cache::tags(CacheEnum::TAG_ROLE)->remember(md5('admin_rule' . $field . implode(',', $ids)), (int) sys_config('system_cache_ttl', 3600), function () use ($ids, $field) {
            $rules   = $this->dao->column(['id' => $ids], $field, 'id');
            $newRule = [];
            $service = app()->get(SystemMenusService::class);
            if ($field === 'rules') {
                $rulesId = [];
                foreach ($rules as $rule) {
                    $rulesId = array_merge($rulesId, $rule);
                }
                $rulesId = array_merge(array_unique($rulesId));
                return $service->ruleByMenusIds($rulesId);
            }
            foreach ($rules as $rule) {
                if ($field === 'apis') {
                    foreach ($rule as $value) {
                        $newRule = array_merge($newRule, is_array($value) ? $value : [$value]);
                    }
                } else {
                    $newRule = array_merge($newRule, $rule);
                }
            }
            return array_unique($newRule);
        });
    }

    /**
     * 获取总后台超级角色权限.
     * @return array
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function getSuperRoleAll(bool $isAll = true)
    {
        $rules = $this->roleDao->column(['type' => array_keys(RuleEnum::ROLE_TYPE), 'entid_like' => 0], ['rules', 'apis', 'id']);
        $data  = [];
        $apis  = [];
        foreach ($rules as $rule) {
            $data = array_merge($data, $rule['rules']);
            foreach ($rule['apis'] as $k => $v) {
                $apis[$k] = $v;
            }
        }
        return $isAll ? array_unique($data) : [$data, $apis];
    }

    /**
     * 添加角色.
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function addSysRole(array $data): bool
    {
        return $this->roleDao->create($data) && Cache::tags([CacheEnum::TAG_ROLE])->flush();
    }

    /**
     * 更新菜单数据.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function updateSuperRole(array $menus): void
    {
        $roles = $this->roleDao->select(['type' => [0, 'enterprise']]);
        foreach ($roles as $role) {
            if ($menus['type'] == 0) {
                $role->rules = array_unique(array_merge($role->rules, [$menus['id']]));
                $role->save();
            }
        }
    }

    /**
     * 获取用户数据范围下的用户ID.
     * @param int $userId 当前用户ID
     * @param string $module 业务模块
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function getDataUids(int $userId, string $module = '', int $type = 0, int $normal = 1): array
    {
        return Cache::tags(CacheEnum::TAG_ROLE)->remember(
            md5($userId . $module . $type . $normal),
            (int) sys_config('system_cache_ttl', 3600),
            function () use ($userId, $module, $type, $normal) {
                $roleIds       = $this->roleUserDao->column(['user_id' => $userId, 'status' => 1], 'role_id');
                $assistService = app()->get(FrameAssistService::class);
                $frameService  = app()->get(FrameService::class);
                if (! $module) {
                    if (! $roleIds) {
                        return array_filter([$userId], function ($value) {
                            return $value;
                        });
                    }
                    $roles   = toArray($this->dao->select(['ids' => $roleIds, 'status' => 1], ['id', 'rules', 'data_level', 'directly', 'frame_id']));
                    $userIds = [$userId];
                    foreach ($roles as $role) {
                        $userIds = array_merge($userIds, $this->getUserids($userId, $role['data_level'], $role['frame_id'], $frameService, $assistService));
                    }
                } else {
                    $crud = app()->get(SystemCrudService::class)->get(['table_name_en' => $module], ['id', 'table_name'])?->toArray();
                    if (! $crud) {
                        throw $this->exception('无效的模块');
                    }
                    $roles   = app()->get(SystemCrudRoleService::class)->select(['role_id' => $roleIds, 'crud_id' => $crud['id']])?->toArray();
                    $userIds = [];
                    switch ($type) {
                        case 1:// 查看
                            foreach ($roles as $role) {
                                $userIds = array_merge($userIds, $this->getUserids($userId, $role['reade'], $role['reade_frame'], $frameService, $assistService));
                            }
                            break;
                        case 2:// 新增
                            foreach ($roles as $role) {
                                $userIds = array_merge($userIds, $this->getUserids($userId, $role['created'], [], $frameService, $assistService));
                            }
                            if (! in_array($userId, $userIds)) {
                                throw $this->exception('暂无权限在' . $crud['table_name'] . '模块中新增数据！');
                            }
                            break;
                        case 3:// 修改
                            foreach ($roles as $role) {
                                $userIds = array_merge($userIds, $this->getUserids($userId, $role['updated'], $role['updated_frame'], $frameService, $assistService));
                            }
                            if (! in_array($userId, $userIds)) {
                                throw $this->exception('暂无权限在' . $crud['table_name'] . '模块中更新该数据！');
                            }
                            break;
                        case 4:// 删除
                            foreach ($roles as $role) {
                                $userIds = array_merge($userIds, $this->getUserids($userId, $role['deleted'], $role['deleted_frame'], $frameService, $assistService));
                            }
                            if (! in_array($userId, $userIds)) {
                                throw $this->exception('暂无权限在' . $crud['table_name'] . '模块中删除该数据！');
                            }
                            break;
                    }
                }
                if ($normal) {
                    $userIds = array_unique(array_intersect($userIds, app()->get(AdminService::class)->column(['status' => 1], 'id')));
                }
                return array_filter($userIds, function ($value) {
                    return $value;
                });
            }
        );
    }

    /**
     * 获取用户数据范围下的组织架构ID.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function getDataFrames(string $uuid, int $entId = 1, bool $withScope = false): array
    {
        $userId = uuid_to_uid($uuid, $entId);
        return Cache::tags([CacheEnum::TAG_ROLE])->remember(md5('frames_' . $userId . (int) $withScope), (int) sys_config('system_cache_ttl', 3600), function () use ($userId, $withScope) {
            $roleIds = $this->roleUserDao->column(['user_id' => $userId, 'status' => 1], 'role_id');
            if (! $roleIds) {
                return [[], []];
            }
            $roles    = toArray($this->dao->select(['ids' => $roleIds, 'status' => 1], ['id', 'rules', 'data_level', 'directly', 'frame_id']));
            $frameIds = [];
            foreach ($roles as $role) {
                $frameIds = array_merge($frameIds, $this->getFrameIds($userId, $role['data_level'], $role['frame_id']));
            }
            if ($withScope) {
                $arr = array_filter(array_column($roles, 'directly'), function ($value) {
                    return $value;
                }) ? [RuleEnum::DATA_SUB] : [];
                return [
                    array_unique($frameIds),
                    array_unique(array_merge(array_column($roles, 'data_level'), $arr)),
                ];
            }
            return array_unique($frameIds);
        });
    }

    /**
     * @param int $userId 用户ID
     * @param int $level 级别：
     * @param array $frameIds 指定部门ID
     * @param null|mixed $frameService
     * @param null|mixed $assistService
     * @return array
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    private function getUserids(int $userId, int $level, array $frameIds = [], ?FrameService $frameService = null, ?FrameAssistService $assistService = null)
    {
        $userIds = match ($level) {
            RuleEnum::DATA_SUB     => $assistService->getSubUid($userId),
            RuleEnum::DATA_ALL     => app()->get(AdminService::class)->column(['status' => 1], 'id'),
            RuleEnum::DATA_APPOINT => $assistService->column(['frame_id' => $frameIds], 'user_id'),
            RuleEnum::DATA_CURRENT => $frameService->getFrameSubUids($userId),
            RuleEnum::DATA_SELF    => [$userId],
            default                => [],
        };
        return array_unique($userIds);
    }

    private function getFrameIds($userId, $level, $frameIds = [])
    {
        $assistService = app()->get(FrameAssistService::class);
        $userFrame     = $assistService->column(['user_id' => $userId], 'frame_id');
        return match ($level) {
            RuleEnum::DATA_ALL     => app()->get(FrameService::class)->column([], 'id'),
            RuleEnum::DATA_APPOINT => $frameIds,
            RuleEnum::DATA_CURRENT => $userFrame,
            default                => [],
        };
    }
}
