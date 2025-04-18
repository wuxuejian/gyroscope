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

namespace App\Http\Service\System;

use App\Constants\CacheEnum;
use App\Constants\MenuEnum;
use App\Constants\RuleEnum;
use App\Http\Contract\System\MenusInterface;
use App\Http\Contract\System\RolesInterface;
use App\Http\Dao\Auth\SystemMenusDao;
use App\Http\Service\Admin\AdminService;
use App\Http\Service\BaseService;
use App\Http\Service\Crud\SystemCrudDashboardService;
use App\Http\Service\Crud\SystemCrudService;
use crmeb\services\FormService as Form;
use crmeb\traits\service\MenusRouteTrait;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Cache;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * 菜单.
 */
class MenusService extends BaseService implements MenusInterface
{
    use MenusRouteTrait;

    private array $founderRule = [];

    private array $founderApi = [];

    private array $initUserRule = [];

    private array $initUserApi = [];

    private array $initCompanyRule = [];

    private array $initCompanyApi = [];

    public function __construct(SystemMenusDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取菜单列表不分页.
     */
    public function getMenusList(array $where, int $page = 0, int $limit = 0, array $field = ['*'], array|string $sort = 'id', array $with = []): array
    {
        $where['type'] = 0;
        $menuList      = $this->dao->getMenusList($where, ['id as value', 'pid', 'menu_name as label', 'status'], 0, 0, ['sort' => 'desc', 'id' => 'asc']);
        $menuStatusIds = $this->dao->column($where);
        return ['tree' => get_tree_children($menuList, 'children', 'value'), 'ids' => $menuStatusIds];
    }

    /**
     * 获取全部菜单列表.
     */
    public function getAllMenusList(array $where): array
    {
        $menuList = $this->dao->setDefaultSort('sort')->select($where, ['id', 'pid', 'menu_name', 'is_show', 'type', 'sort'])?->toArray();
        return get_tree_children($menuList);
    }

    /**
     * 获取企业全部菜单.
     * @param int $entId 企业ID
     * @return mixed
     * @throws \ReflectionException
     * @throws BindingResolutionException
     */
    public function getMenusForCompany(int $entId, bool $disableId = false): array
    {
        /** @var RolesInterface $roleService */
        $roleService  = app()->get(RolesInterface::class);
        $where['ids'] = $menuStatusIds = $roleService->getCompanySuperRole($entId);
        if (isset($where['ids'])) {
            $where['ids'] = $this->ruleByMenusIds($where['ids']);
        }
        $where['type'] = MenuEnum::TYPE_MENU;
        $menuList      = $this->dao->getMenusList($where, ['id as value', 'pid', 'menu_name as label', 'status'], 0, 0, ['sort' => 'desc', 'id' => 'asc']);
        if ($menuList && $disableId) {
            foreach ($menuList as &$value) {
                $value['disabled'] = $disableId;
            }
        }
        return ['tree' => get_tree_children($menuList, 'children', 'value'), 'ids' => array_map('intval', $menuStatusIds)];
    }

    /**
     * @throws BindingResolutionException
     */
    public function saveMenusForCompany(int $entId, array $rules = [], array $apis = []): bool
    {
        /** @var RolesInterface $roleService */
        $roleService = app()->get(RolesInterface::class);
        return $roleService->saveSystemRole($entId, $rules, $apis);
    }

    /**
     * 获取初始用户菜单.
     * @param string $uuid 用户ID
     * @return mixed
     */
    public function getMenusForInitialUser(string $uuid): array
    {
        return [];
    }

    /**
     * 获取初始企业用户菜单.
     * @return mixed
     */
    public function getMenusForCompanyUser(string $uuid): array
    {
        return [];
    }

    /**
     * 获取用户全部菜单.
     * @return mixed
     */
    public function getMenusForUser(string $uuid, int $entId, bool $isTree = true, array|string $field = ['id', 'pid', 'menu_name', 'menu_path', 'uni_path', 'uni_img', 'icon', 'path', 'entid', 'position', 'unique_auth', 'is_show', 'component']): array
    {
        $roleService     = app()->get(RolesInterface::class);
        $where['type']   = MenuEnum::TYPE_MENU;
        $where['status'] = 1;
        if (! app()->get(AdminService::class)->value(['uid' => $uuid], 'is_admin')) {
            $where['ids'] = $roleService->getRolesForUser($uuid, $entId, withApi: false);
        }
        if (isset($where['ids'])) {
            $where['ids'] = $this->ruleByMenusIds($where['ids']);
        }
        if (is_string($field)) {
            return $this->dao->column($where, $field);
        }
        $menuList = toArray($this->dao->getMenusList($where, $field, 0, 0, 'sort'));
        if ($isTree) {
            return $this->getTreeChildren($menuList);
        }

        return $menuList;
    }

    /**
     * 获取移动端用户菜单.
     * @return mixed
     */
    public function getMenusForUni(string $uuid, int $entId): array
    {
        $userService = app()->get(AdminService::class);
        $ident       = $userService->value(['uid' => $uuid], 'is_admin');
        /** @var RolesInterface $roleService */
        $roleService = app()->get(RolesInterface::class);
        if ($ident) {
            $ids = $roleService->getCompanySuperRole($entId, withApi: false);
        } else {
            $ids = $roleService->getRolesForUser($uuid, $entId, withApi: false);
        }
        $ids      = $this->ruleByMenusIds($ids);
        $topMenus = $this->dao->getMenusList([
            'ids'    => $ids,
            'type'   => 0,
            'pid'    => 0,
            'status' => 1,
        ], ['id as value', 'menu_name'], 0, 0, ['sort' => 'desc', 'id' => 'asc']);
        $menus = [];
        foreach ($topMenus as &$topMenu) {
            $topMenu['children'] = $this->dao->getMenusList([
                'path_like' => $topMenu['value'],
                'ids'       => $ids,
                'type'      => 0,
                'status'    => 1,
                'is_show'   => 1,
                'uni_path'  => true,
            ], ['id as value', 'menu_name', 'uni_path', 'uni_img'], 0, 0, ['sort' => 'desc', 'id' => 'asc']);
            if ($topMenu['children']) {
                $menus[] = $topMenu;
            }
        }
        return $menus;
    }

    /**
     * 保存菜单.
     * @throws BindingResolutionException
     */
    public function saveMenu(array $menuInfo, int $entId, int $id = 0): array
    {
        $info = $id ? $this->dao->get($id)?->toArray() : [];
        if (! $menuInfo['unique_auth']) {
            $menuInfo['unique_auth'] = uniqid('menus');
        }
        //        $res                               = app()->get(BaiduTranslateService::class)->query($menuInfo['menu_name'], 'en')->all();
        //        $menuInfo['other']['menu_name_en'] = $res['trans_result'][0]['dst'] ?? '';
        if ($menuInfo['menu_path'] && ! $menuInfo['crud_id'] && $menuInfo['menu_type'] == 1) {
            $tableName = str_replace(['/crud/module/', '/list'], '', $menuInfo['menu_path']);
            if ($tableName) {
                $crudId               = app()->make(SystemCrudService::class)->value(['table_name_en' => $tableName], 'id');
                $menuInfo['crud_id']  = $crudId ?: 0;
                $menuInfo['uni_path'] = '/pages/module/list?tablename=' . $tableName;
            }
        }
        if ($menuInfo['menu_path'] && ! $menuInfo['crud_id'] && $menuInfo['menu_type'] == 2) {
            $crudId               = str_replace(['/crud/module/', '/dashboard'], '', $menuInfo['menu_path']);
            $menuInfo['crud_id']  = $crudId ?: 0;
            $menuInfo['uni_path'] = '/pages/module/dashboard?id=' . $menuInfo['crud_id'];
        }
        if ($id) {
            $this->dao->update($id, $menuInfo);
            $menuInfo['id'] = $id;
            $menus          = $menuInfo;
        } else {
            $menus = $this->dao->create($menuInfo)?->toArray();
        }
        if (! $menus) {
            throw $this->exception('保存菜单数据失败');
        }
        $roleService = app()->get(RolesService::class);
        if ($menuInfo['status']) {
            $roleService->saveMenuRole($menus, $info);
        } else {
            $roleService->saveMenuRole(oldInfo: $info);
        }
        // 更新菜单数据
        $roleService->updateSuperRole($menus);
        Cache::tags([CacheEnum::TAG_ROLE, 'rules', 'api-rule'])->flush();
        return $menus;
    }

    public function getEditForm(): array
    {
        return [];
    }

    /**
     * 删除菜单.
     */
    public function deleteMenu(int $id, int $entId): bool
    {
        if ($this->dao->count(['pid' => $id])) {
            throw $this->exception('请先删除下级菜单');
        }
        $info = $this->dao->get($id);
        app()->get(RolesService::class)->saveMenuRole(oldInfo: $info?->toArray());
        return $info->delete() && Cache::tags([CacheEnum::TAG_ROLE, 'rules', 'api-rule'])->flush();
    }

    /**
     * 修改菜单.
     * @param mixed $id
     * @return int|mixed
     * @throws BindingResolutionException
     */
    public function update($id, array $data)
    {
        $path    = $this->dao->value($id, 'path');
        $path    = implode('/', $path);
        $newPath = implode('/', $data['path']);

        if ($data['menu_path'] && ! $data['crud_id'] && $data['menu_type'] == 1) {
            $tableName = str_replace(['/crud/module/', '/list'], '', $data['menu_path']);
            if ($tableName) {
                $crudId           = app()->make(SystemCrudService::class)->value(['table_name_en' => $tableName], 'id');
                $data['crud_id']  = $crudId ?: 0;
                $data['uni_path'] = '/pages/module/list?tablename=' . $tableName;
            }
        }

        return $this->transaction(function () use ($id, $data, $path, $newPath) {
            $this->dao->update($id, $data);
            if (isset($data['is_show'], $data['status'])) {
                $ids = $this->getAllSubMenus($id);
                if ($ids) {
                    $this->dao->update(['id' => $ids], [
                        'is_show' => $data['is_show'],
                        'status'  => $data['status'],
                    ]);
                }
            }
            if ($path != $newPath) {
                $this->dao->setPathField('path')->updatePath((int) $id, $path, $newPath);
            }
            Cache::tags([CacheEnum::TAG_ROLE, 'rules', 'api-rule'])->flush();
            return true;
        });
    }

    public function isShow($id, $edit)
    {
        return $this->dao->update($id, $edit);
    }

    /**
     * @param array $menuIds
     * @param mixed $pid
     * @return array
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getAllSubMenus($pid, $menuIds = [])
    {
        if (! $pid) {
            return [];
        }
        $menuId  = ($menuId = $this->dao->column(['pid' => $pid], 'id')) ? $menuId : [];
        $menuIds = array_merge($menuIds, $menuId);
        if (count($menuId) && $this->dao->exists(['pid' => $menuId])) {
            return $this->getAllSubMenus($menuId, $menuIds);
        }
        return $menuIds;
    }

    /**
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getMenuUnique(array|object $ids, bool $isRule = true): array
    {
        if ($isRule) {
            return $this->dao->column(['id' => $ids], 'unique_auth');
        }
        $data = [];
        foreach ($ids as $key => $val) {
            $data[$this->dao->value(['id' => $key], 'unique_auth')] = $this->dao->column(['id' => $val], 'unique_auth');
        }
        return $data;
    }

    /**
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getMenuId(array|object $uniques, bool $isRule = true): array
    {
        if ($isRule) {
            return $this->dao->column(['unique_auth' => array_filter($uniques)], 'id');
        }
        $data = [];
        foreach ($uniques as $key => $val) {
            $data[$this->dao->value(['unique_auth' => $key], 'id')] = $this->dao->column(['unique_auth' => $val], 'id');
        }
        return $data;
    }

    /**
     * 获取没有添加的菜单.
     * @return string
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function getNoSaveMenusList(int $entId = 0)
    {
        return Cache::tags([CacheEnum::TAG_ROLE])->remember(md5('no-save-menus-list' . $entId), (int) sys_config('system_cache_ttl', 3600), function () use ($entId) {
            $menusList = $this->dao->getMenusList(['type' => MenuEnum::TYPE_API, 'entid' => $entId], ['menu_path', 'methods as method', 'menu_name as name', 'api']);
            $ent       = array_merge($this->diffAssoc($menusList, fn ($val) => str_contains($val['uri'], 'api/ent')));
            $uni       = array_merge($this->diffAssoc($menusList, fn ($val) => str_contains($val['uri'], 'api/uni')));
            return compact('ent', 'uni');
        });
    }

    /**
     * 删除菜单.
     * @return null|bool
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function destroy(string $id)
    {
        if (! $this->dao->exists(['id' => $id])) {
            throw $this->exception('未找到相关菜单');
        }
        if ($this->dao->count(['pid' => $id])) {
            throw $this->exception('请先删除下级菜单');
        }
        Cache::tags([CacheEnum::TAG_ROLE, 'rules', 'api-rule'])->flush();
        return $this->dao->delete($id);
    }

    /**
     * 获取阶梯型菜单数据.
     * @param mixed $disabled
     * @param mixed $isDefault
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getCascaderMenus(array $where = ['status' => 1], $disabled = false, $isDefault = []): array
    {
        if (isset($where['ids'])) {
            $where['ids'] = $this->ruleByMenusIds($where['ids']);
        }
        $menuList = $this->dao->getMenusList($where, ['id as value', 'pid', 'menu_name as label', 'entid', 'component', 'type'], 0, 0, ['sort' => 'desc', 'id' => 'desc']);
        foreach ($menuList as &$value) {
            $value['disabled']   = (bool) $disabled;
            $value['is_default'] = in_array($value['value'], $isDefault);
        }
        return $this->getMenusTree($menuList, 'children', 'value');
    }

    /**
     * 获取创建菜单表单.
     */
    public function getCreateForm(int $entid): array
    {
        return $this->elForm('创建菜单', $this->createForm([], $entid), '/admin/system/menus?entid=' . $entid, 'post');
    }

    /**
     * 修改获取菜单表单.
     * @return array
     * @throws BindingResolutionException
     */
    public function getUpdateForm(string $id, int $entid)
    {
        $menu = $this->dao->get($id);
        if (! $menu) {
            throw $this->exception('修改的菜单数据不存在');
        }
        $menuPath          = $menu->getAttributes()['menu_path'];
        $menu              = $menu->toArray();
        $menu['menu_path'] = $menuPath;
        return $this->elForm('修改菜单', $this->createForm($menu, $entid), '/admin/system/menus/' . $id . '?entid=' . $entid, 'put');
    }

    /**
     * 获取登录后的菜单.
     * @param mixed $isTree
     * @return array
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getLoginMenus(int $entid = 0, array $where = [], $isTree = true)
    {
        $defaultWhere = ['status' => 1, 'entid' => $entid];

        if (isset($where['ids'])) {
            $where['ids'] = $this->ruleByMenusIds($where['ids']);
        }
        if ($where) {
            $defaultWhere = array_merge($defaultWhere, $where);
        }
        $menuList   = $this->dao->getMenusList($defaultWhere + ['type' => 0], ['id', 'pid', 'menu_name', 'menu_path', 'icon', 'path', 'entid', 'position', 'unique_auth', 'is_show'], 0, 0, 'sort');
        $uniqueAuth = collect($this->dao->column($defaultWhere, 'unique_auth'))->uniqueStrict()->filter(function ($val) {
            return (bool) $val;
        })->all();
        if ($isTree) {
            return ['menus' => $this->getTreeChildren($menuList), 'unique_auth' => $uniqueAuth];
        }

        return $menuList;
    }

    /**
     * 获取某个菜单下的权限.
     * @return array
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getPidMenusRule(int $pid, array $field = ['menu_name', 'id', 'status'])
    {
        return $this->dao->getList(['pid' => $pid, 'type' => 1], $field, 0, 0, 'sort');
    }

    /**
     * 获取某个管理员角色下的权限.
     * @return mixed
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getMenusAuth(array $roles, bool $ent = false)
    {
        $menusId        = app()->get(RolesService::class)->getAdminRole($roles, 'apis');
        $menusId        = $this->ruleByMenusIds($menusId);
        $defaultMenusId = $this->getDefaultRuleIds($ent);
        $menusId        = array_unique(array_merge($menusId, $defaultMenusId));
        return $this->getAuthApis($menusId, $ent);
    }

    /**
     * 用菜单id获取接口权限并缓存.
     * @return mixed
     */
    public function getAuthApis(array $apiIds, bool $ent = false)
    {
        return Cache::tags(['api-rule'])->remember(md5(implode(',', $apiIds)), (int) sys_config('system_cache_ttl', 3600), function () use ($apiIds, $ent) {
            return $this->dao->column(['ids' => $apiIds, 'entid' => $ent ? 1 : 0, 'type' => 1], 'api,methods', 'id');
        });
    }

    /**
     * 获取权限选中的完整菜单.
     * @return array
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function ruleByMenusIds(array $menusId)
    {
        $paths = $this->dao->column(['ids' => $menusId], 'path');
        $ids   = [];
        foreach ($paths as $path) {
            $ids = array_merge($ids, $path);
        }
        return array_filter(array_unique(array_merge($ids, $menusId)));
    }

    /**
     * 获取超级管理员权限.
     * @param int $entid
     * @return array
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getSuperMenusAll($entid = 0)
    {
        $ruleIds = app()->get(RolesService::class)->getSuperRoleAll();
        return $this->getCascaderMenus(['status' => 1, 'ids' => $ruleIds, 'type' => 0]);
    }

    /**
     * 获取企业顶级菜单列表.
     * @param mixed $entId
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getSuperMenus($entId): array
    {
        $services = app()->get(RolesService::class);
        // 默认企业权限
        $allRule = $services->get(['entid' => 0, 'type' => RuleEnum::ENTERPRISE_TYPE], ['rules', 'apis']);
        // 当前企业权限
        $rules = $services->get(['entid' => $entId, 'type' => RuleEnum::ENTERPRISE_TYPE, 'level' => 0], ['rules', 'apis']);
        $paths = $this->dao->column(['ids' => $allRule['rules']], 'path');
        $ids   = [];
        foreach ($paths as $path) {
            $ids = array_merge($ids, $path);
        }
        $menuIds  = array_map('intval', array_filter(array_unique(array_merge($ids, $allRule['rules']))));
        $menuList = $this->dao->getList(['ids' => $menuIds, 'status' => 1], ['id as value', 'id', 'pid', 'menu_name as label', 'entid'], 0, 0, ['sort' => 'desc', 'id' => 'asc']);
        $menus    = get_tree_children($menuList, 'children', 'value');
        return compact('menus', 'rules');
    }

    /**
     * 获取默认权限id.
     * @return array
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getDefaultRuleIds(bool $ent = false)
    {
        return Cache::tags(['rules'])->remember('DefaultRuleIds' . ($ent ? 1 : 0), (int) sys_config('system_cache_ttl', 3600), function () use ($ent) {
            return $this->dao->column(['type' => 1, 'entid' => $ent ? 1 : 0]);
        });
    }

    /**
     * 获取tree型数据.
     *
     * @param array $data 数据
     * @param string $childrenName 子数据名
     * @param string $keyName 数据key名
     * @param string $pidName 数据上级key名
     */
    public function getTreeChildren(array $data, string $childrenName = 'children', string $keyName = 'id', string $pidName = 'pid', string $positionChildName = 'top_position', string $positionName = 'position'): array
    {
        $tree = $list = [];
        foreach ($data as $value) {
            $list[$value[$keyName]] = $value;
        }
        foreach ($list as $item) {
            if (isset($list[$item[$pidName]])) {
                $list[$item[$pidName]][$item[$positionName] == 1 ? $positionChildName : $childrenName][] = &$list[$item[$keyName]];
            } else {
                $tree[] = &$list[$item[$keyName]];
            }
        }

        return $tree;
    }

    public function syncCompanyMenus()
    {
        //        /** @var Common $service */
        //        $service = app()->get(Common::class);
        //        $menus   = $service->syncMenus();
        //        /** @var RolesService $roleService */
        $roleService = app()->get(RolesService::class);
        // //        DB::table('system_menus')->truncate();
        //        $roleService->roleDao->delete(['entid_like' => 0]);
        //        $this->initMenus($this->dao, $menus);
        // //        return $menus;
        //        $res = $this->transaction(function () use ($menus, $roleService) {
        // //            $roleService->roleDao->create([
        // //                'rules'     => $this->founderRule,
        // //                'apis'      => $this->founderApi,
        // //                'type'      => 0,
        // //                'role_name' => '企业超级角色(创始人)',
        // //            ]);
        //            return $roleService->roleDao->update(['entid_like' => true], [
        //                'rules' => $this->founderRule,
        //                'apis'  => $this->founderApi,
        //            ]);
        // //            $roleService->roleDao->create([
        // //                'rules'     => $this->initUserRule,
        // //                'apis'      => $this->initUserApi,
        // //                'type'      => 1,
        // //                'role_name' => '初始角色(无企业)',
        // //            ]);
        // //            $roleService->roleDao->create([
        // //                'rules'     => $this->initCompanyRule,
        // //                'apis'      => $this->initCompanyApi,
        // //                'type'      => 2,
        // //                'role_name' => '初始角色(有企业)',
        // //            ]);
        //            return true;
        //        });
        //        if ($res) {
        //            $roleService->syncRoles();
        //        }
        $roleService->syncRoles();
        return true;
    }

    protected function initMenus($menuDao, $menus, $path = [], $pid = 0, $level = 0)
    {
        foreach ($menus as $menu) {
            if ($level == $menu['level']) {
                array_walk($menu, function (&$item, $key) use ($pid, $path) {
                    if ($key == 'other') {
                        $item = json_encode($item);
                    }
                    if ($key == 'pid') {
                        $item = $pid;
                    }
                    if ($key == 'path') {
                        $item = $path;
                    }
                });
                unset($menu['id']);
                $children    = $menu['children'] ?? [];
                $founder     = $menu['founder'];
                $initUser    = $menu['initUser'];
                $initCompany = $menu['initCompany'];
                unset($menu['children'], $menu['founder'], $menu['initUser'], $menu['initCompany']);
                $res = $menuDao->create($menu);
                if ($founder['rule']) {
                    $this->pushRuleId($res->id);
                }
                if ($founder['api']) {
                    $this->pushApiId($pid, $res->id);
                }
                if ($initUser['rule']) {
                    $this->pushRuleId($res->id, 1);
                }
                if ($initUser['api']) {
                    $this->pushApiId($pid, $res->id, 1);
                }
                if ($initCompany['rule']) {
                    $this->pushRuleId($res->id, 2);
                }
                if ($initCompany['api']) {
                    $this->pushApiId($pid, $res->id, 2);
                }
                if ($children) {
                    $this->initMenus($menuDao, $children, array_merge($path, [$res->id]), $res->id, $menu['level'] + 1);
                }
            }
        }
    }

    /**
     * 创建表单.
     * @return array
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    protected function createForm(array $menu = [], int $entid = 0)
    {
        $cascader = $this->getCascaderMenus(['type' => MenuEnum::TYPE_MENU]);
        if (isset($menu['path'])) {
            $menu['path'] = array_map('intval', $menu['path']);
        }
        $cascader    = array_merge([['value' => 0, 'label' => '顶级菜单']], $cascader);
        $path        = isset($menu['path']) ? ($menu['path'] ?: [0]) : [0];
        $menu_path   = isset($menu['menu_path']) ? [str_replace(['/crud/module/', '/list'], '', $menu['menu_path'])] : [];
        $crudService = app()->get(SystemCrudService::class);
        $crudTree    = $crudService->getCrudTree(['crud_id' => 0, 'not_name' => $this->dao->getModelMenusName(other: $menu_path)]);
        foreach ($crudTree as &$value) {
            if (isset($value['children'])) {
                foreach ($value['children'] as &$children) {
                    $children['value'] = '/crud/module/' . $children['table_name_en'] . '/list';
                }
            }
        }
        $listValue = [];
        if ($menu && $menu['crud_id']) {
            $crudCate = $crudService->get($menu['crud_id'], ['cate_ids', 'table_name_en'])?->toArray();
            if ($crudCate['cate_ids']) {
                $listValue = [$crudCate['cate_ids'][0], '/crud/module/' . $crudCate['table_name_en'] . '/list'];
            } else {
                $listValue = [0, '/crud/module/' . $crudCate['table_name_en'] . '/list'];
            }
        }
        return [
            Form::cascader('path', '选择上级菜单')
                ->options($cascader)->value($path)->props(['props' => ['checkStrictly' => true]])->col(24),
            Form::select('type', '权限类型', $menu['type'] ?? 'M')->col(24)->options([
                ['value' => 'M', 'label' => '菜单'],
                ['value' => 'B', 'label' => '按钮'],
                ['value' => 'A', 'label' => '接口'],
            ])->control([
                [
                    'value' => 'M',
                    'rule'  => [
                        Form::input('menu_name', '菜单名称', $menu['menu_name'] ?? '')->required(),
                        Form::frameInput('icon', '菜单图标', get_roule_mobu() . '/setting/icons?field=icon', $menu['icon'] ?? '')->icon('el-icon-circle-plus-outline')->height('590px')->width('1250px')->modal(['modal' => true]),
                        Form::radio('menu_type', '路由类型', $menu['menu_type'] ?? 0)->options([['value' => 0, 'label' => '系统链接'], ['value' => 1, 'label' => '关联实体'], ['value' => 2, 'label' => '数据看板']])->control([
                            [
                                'value' => 0,
                                'rule'  => [
                                    Form::input('menu_path', '路由路径', isset($menu['menu_path']) && $menu['menu_type'] == 0 ? $menu['menu_path'] : '')->required(),
                                    Form::input('component', '前端路径', isset($menu['component']) && $menu['menu_type'] == 0 ? $menu['component'] : ''),
                                    //                                    Form::radio('position', '菜单位置', $menu['position'] ?? 0)->options([['value' => 0, 'label' => '侧方'], ['value' => 1, 'label' => '顶部']]),
                                    Form::input('uni_path', '移动端路径', isset($menu['uni_path']) && $menu['menu_type'] == 0 ? $menu['uni_path'] : '')->maxlength(200),
                                    Form::frameImage('uni_img', '移动端图标', get_image_frame_url(['field' => 'uni_img']), $menu['uni_img'] ?? '')->handleIcon(false)->width('1250px')->height('590px')->modal(['modal' => false, 'showCancelButton' => false, 'showConfirmButton' => false]),
                                ],
                            ],
                            [
                                'value' => 1,
                                'rule'  => [
                                    Form::cascader('list_path', '关联实体')->value((isset($menu['menu_path']) && $menu['menu_type'] == 1) ? $listValue : '')->props([
                                        'props' => [
                                            'emitPath' => false,
                                        ],
                                    ])->options($crudTree)->col(24)->clearable(true),
                                    //                                    Form::radio('position', '菜单位置', $menu['position'] ?? 0)->options([['value' => 0, 'label' => '侧方'], ['value' => 1, 'label' => '顶部']]),
                                    Form::frameImage('uni_img', '移动端图标', get_image_frame_url(['field' => 'uni_img']), $menu['uni_img'] ?? '')->handleIcon(false)->width('1250px')->height('590px')->modal(['modal' => false, 'showCancelButton' => false, 'showConfirmButton' => false]),
                                ],
                            ],
                            [
                                'value' => 2,
                                'rule'  => [
                                    Form::select('dash_path', '关联看板', isset($menu['menu_path']) && $menu['menu_type'] == 2 ? $menu['menu_path'] : '')->options(toArray(app()->get(SystemCrudDashboardService::class)->select([
                                        'not_id' => $this->dao->getModelMenusName('dashboard', $menu_path),
                                    ], ['id', 'name as label'])->each(function ($item) {
                                        $item['value'] = '/crud/module/' . $item['id'] . '/dashboard';
                                    })))->required()->filterable(true)->col(24)->clearable(true),
                                    //                                    Form::radio('position', '菜单位置', $menu['position'] ?? 0)->options([['value' => 0, 'label' => '侧方'], ['value' => 1, 'label' => '顶部']]),
                                    Form::frameImage('uni_img', '移动端图标', get_image_frame_url(['field' => 'uni_img']), $menu['uni_img'] ?? '')->handleIcon(false)->width('1250px')->height('590px')->modal(['modal' => false, 'showCancelButton' => false, 'showConfirmButton' => false]),
                                ],
                            ],
                        ]),
                        Form::number('sort', '排序', $menu['sort'] ?? 0)->min(0)->max(999999),
                        Form::switches('is_show', '隐藏菜单', $menu['is_show'] ?? 0)->inactiveValue(0)->activeValue(1)->inactiveText('隐藏')->activeText('展示'),
                    ],
                ], [
                    'value' => 'B',
                    'rule'  => [
                        Form::input('menu_name', '按钮名称', $menu['menu_name'] ?? '')->required(),
                        Form::input('unique_auth', '权限标识', $menu['unique_auth'] ?? '')->required(),
                        Form::number('sort', '排序', $menu['sort'] ?? 0)->min(0)->max(999999),
                    ],
                ], [
                    'value' => 'A',
                    'rule'  => [
                        Form::frameInput('menu_name', '权限名称', get_roule_mobu() . '/setting/auth?field=rule&entid=' . $entid, $menu['menu_name'] ?? '')->icon('el-icon-s-grid')->height('590px')->width('1250px')->modal(['modal' => true])->required()->validate(['type' => 'string']),
                        Form::input('api', '权限路由', $menu['api'] ?? '')->required(),
                        Form::select('methods', '请求方式', $menu['methods'] ?? '')->options([
                            ['value' => 'GET', 'label' => 'GET'],
                            ['value' => 'PUT', 'label' => 'PUT'],
                            ['value' => 'POST', 'label' => 'POST'],
                            ['value' => 'DELETE', 'label' => 'DELETE'],
                        ])->required(),
                        Form::number('sort', '排序', $menu['sort'] ?? 0)->min(0)->max(999999),
                    ],
                ],
            ]),
            Form::switches('status', '是否可用', $menu['status'] ?? 1)->inactiveValue(0)->activeValue(1)->inactiveText('禁用')->activeText('启用'),
        ];
    }

    private function pushRuleId($ruleId, $type = 0)
    {
        switch ($type) {
            case 0:
                $this->founderRule[] = $ruleId;
                break;
            case 1:
                $this->initUserRule[] = $ruleId;
                break;
            case 2:
                $this->initCompanyRule[] = $ruleId;
                break;
        }
    }

    private function pushApiId($pid, $apiId, $type = 0)
    {
        switch ($type) {
            case 0:
                $this->founderApi[$pid][] = $apiId;
                break;
            case 1:
                $this->initUserApi[$pid][] = $apiId;
                break;
            case 2:
                $this->initCompanyApi[$pid][] = $apiId;
                break;
        }
    }

    /**
     * 获取tree型数据.
     *
     * @param array $data 数据
     * @param string $childrenname 子数据名
     * @param string $keyName 数据key名
     * @param string $pidName 数据上级key名
     * @param mixed $apiName
     *
     * @return array
     */
    private function getMenusTree(array $data, string $childrenname = 'children', string $keyName = 'id', string $pidName = 'pid', $apiName = 'apis')
    {
        $list = [];
        foreach ($data as $value) {
            $list[$value[$keyName]] = $value;
        }
        $tree = []; // 格式化好的树
        foreach ($list as $item) {
            if (isset($list[$item[$pidName]])) {
                if ($list[$item[$keyName]]['type'] == MenuEnum::TYPE_MENU) {
                    $list[$item[$pidName]][$childrenname][] = &$list[$item[$keyName]];
                } else {
                    $list[$item[$pidName]][$apiName][] = &$list[$item[$keyName]];
                }
            } else {
                $tree[] = &$list[$item[$keyName]];
            }
        }

        return $tree;
    }
}
