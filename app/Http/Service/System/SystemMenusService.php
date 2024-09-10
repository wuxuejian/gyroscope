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

use App\Constants\MenuEnum;
use App\Constants\RuleEnum;
use App\Http\Dao\Auth\SystemMenusDao;
use App\Http\Service\BaseService;
use crmeb\services\BaiduTranslateService;
use crmeb\services\FormService as Form;
use crmeb\traits\service\MenusRouteTrait;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Cache;

/**
 * 菜单
 * Class SystemMenusService.
 * @method array getUserMenusList(array $where, array $field = ['*']) 获取个人中心菜单权限
 */
class SystemMenusService extends BaseService
{
    use MenusRouteTrait;

    /**
     * SystemMenusService constructor.
     */
    public function __construct(SystemMenusDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取菜单列表不分页.
     * @return array
     */
    public function getMenusList(array $where)
    {
        $where['type'] = 0;
        $menuList      = $this->dao->getMenusList($where, ['id as value', 'pid', 'menu_name as label', 'status'], 0, 0, ['sort' => 'desc', 'id' => 'asc']);
        $menuStatusIds = $this->dao->column($where);
        return ['tree' => get_tree_children($menuList, 'children', 'value'), 'ids' => $menuStatusIds];
    }

    /**
     * 保存菜单.
     * @throws BindingResolutionException
     */
    public function storeMenus(array $data)
    {
        if (! $data['unique_auth']) {
            $data['unique_auth'] = uniqid('menus');
        }
        /** @var BaiduTranslateService $service */
        $service                       = app()->get(BaiduTranslateService::class);
        $res                           = $service->query($data['menu_name'], 'en')->all();
        $data['other']['menu_name_en'] = $res['trans_result'][0]['dst'] ?? '';

        $menus = $this->dao->create($data);
        if (! $menus) {
            throw $this->exception('保存菜单数据失败');
        }
        Cache::tags(['rules', 'api-rule'])->flush();
        return $menus->toArray();
    }

    /**
     * 修改菜单.
     * @return int|mixed
     * @throws BindingResolutionException
     */
    public function update($id, array $data)
    {
        $path    = $this->dao->value($id, 'path');
        $path    = implode('/', $path);
        $newPath = implode('/', $data['path']);

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
            Cache::tags(['rules', 'api-rule'])->flush();
            return true;
        });
    }

    /**
     * @param array $menuIds
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
     * 获取没有添加的菜单.
     * @return array
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getNoSaveMenusList(int $entid = 0)
    {
        if ($this->isEnt() || $entid) {
            $this->setOption('path', 'api/ent');
        } else {
            $this->setOption('path', 'api/admin');
        }

        $menusList = $this->dao->getMenusList(['type' => 1, 'entid' => $entid], ['api', 'methods as method']);

        return array_merge($this->diffAssoc($menusList));
    }

    /**
     * 删除菜单.
     * @return null|bool
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function destroy(string $id)
    {
        if ($this->dao->count(['pid' => $id])) {
            throw $this->exception('请先删除下级菜单');
        }
        Cache::tags(['rules', 'api-rule'])->flush();
        return $this->dao->delete($id);
    }

    /**
     * 获取阶梯型菜单数据.
     * @param mixed $disabled
     * @return array
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getCascaderMenus(array $where = ['status' => 1], $disabled = false)
    {
        if (isset($where['ids'])) {
            $where['ids'] = $this->ruleByMenusIds($where['ids']);
        }
        $menuList = $this->dao->getMenusList($where, ['id as value', 'pid', 'menu_name as label', 'entid'], 0, 0, ['sort' => 'desc', 'id' => 'desc']);
        if ($menuList && $disabled) {
            foreach ($menuList as &$value) {
                $value['disabled'] = true;
            }
        }
        return get_tree_children($menuList, 'children', 'value');
    }

    /**
     * 获取创建菜单表单.
     * @return array
     */
    public function getCreateForm(int $entid)
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
     * @param mixed $isUni
     * @return array
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getLoginMenus(int $entid = 0, array $where = [], $isTree = true, $isUni = false)
    {
        $defaultWhere = ['status' => 1, 'entid' => $entid];

        if (isset($where['ids'])) {
            $where['ids'] = $this->ruleByMenusIds($where['ids']);
        }
        if ($isUni) {
            $where['uni_path'] = true;
        }
        if ($where) {
            $defaultWhere = array_merge($defaultWhere, $where);
        }
        $menuList   = toArray($this->dao->getMenusList($defaultWhere + ['type' => 0], ['id', 'pid', 'menu_name', 'menu_path', 'uni_path', 'uni_img', 'icon', 'path', 'entid', 'position', 'unique_auth', 'is_show'], 0, 0, 'sort'));
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
    public function getPidMenusRule(int $pid, array $field = ['menu_name', 'id', 'status', 'type'])
    {
        return $this->dao->getList(['pid' => $pid, 'type' => [MenuEnum::TYPE_API, MenuEnum::TYPE_BUTTON]], $field, 0, 0, 'sort');
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
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getSuperMenus(string $name = '', $entId): array
    {
        $roleService = app()->get(RolesService::class)->roleDao;
        // 默认企业权限
        $allRule = $roleService->get(['entid_like' => 0, 'type' => 0], ['rules', 'apis']);
        // 当前企业权限
        $rules = $roleService->get(['entid_like' => $entId, 'type' => RuleEnum::ENTERPRISE_TYPE, 'level' => 0], ['rules', 'apis']);
        $paths = $this->dao->column(['ids' => $allRule['rules']], 'path');
        $ids   = [];
        foreach ($paths as $path) {
            $ids = array_merge($ids, $path);
        }
        $menuIds  = array_map('intval', array_filter(array_unique(array_merge($ids, $allRule['rules']))));
        $menuList = $this->dao->getList(['ids' => $menuIds, 'status' => 1, 'name_like' => $name], ['id as value', 'id', 'pid', 'menu_name as label', 'entid'], 0, 0, ['sort' => 'desc', 'id' => 'asc']);
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

    /**
     * 创建表单.
     * @return array
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    protected function createForm(array $menu = [], int $entid = 1)
    {
        $cascader = $this->getCascaderMenus(['entid' => $entid]);
        if (isset($menu['path'])) {
            $menu['path'] = array_map('intval', $menu['path']);
        }
        return [
            Form::cascader('path', '选择上级菜单')
                ->options($cascader)->value($menu['path'] ?? [])->props(['props' => ['checkStrictly' => true]]),
            Form::select('type', '权限类型', $menu['type'] ?? 0)->options([
                ['value' => 0, 'label' => '菜单'],
                ['value' => 1, 'label' => '权限'],
            ])->control([
                [
                    'value' => 0,
                    'rule'  => [
                        Form::input('menu_name', '菜单名称', $menu['menu_name'] ?? '')->required(),
                        Form::frameInput('icon', '菜单图标', get_roule_mobu() . '/setting/icons?field=icon', $menu['icon'] ?? '')->icon('el-icon-circle-plus-outline')->height('338px')->width('700px')->modal(['modal' => true]),
                        Form::input('menu_path', '路由路径', $menu['menu_path'] ?? '')->required(),
                        Form::number('sort', '排序', $menu['sort'] ?? 0)->min(0)->max(999999),
                        Form::switches('is_show', '隐藏菜单', $menu['is_show'] ?? 0)->inactiveValue(0)->activeValue(1)->inactiveText('隐藏')->activeText('展示'),
                    ],
                ], [
                    'value' => 1,
                    'rule'  => [
                        Form::frameInput('menu_name', '权限名称', get_roule_mobu() . '/setting/auth?field=rule&entid=' . $entid, $menu['menu_name'] ?? '')->icon('el-icon-s-grid')->height('700px')->width('800px')->modal(['modal' => true])->required()->validate(['type' => 'string']),
                        Form::input('api', '权限路由', $menu['api'] ?? '')->required(),
                        Form::select('methods', '请求方式', $menu['methods'] ?? '')->options([
                            ['value' => 'get', 'label' => 'GET'],
                            ['value' => 'put', 'label' => 'PUT'],
                            ['value' => 'post', 'label' => 'POST'],
                            ['value' => 'delete', 'label' => 'DELETE'],
                        ])->required(),
                        Form::number('sort', '排序', $menu['sort'] ?? 0)->min(0)->max(999999),
                    ],
                ],
            ]),
            Form::hidden('entid', $entid),
            Form::hidden('unique_auth', $menu['unique_auth'] ?? ''),
            Form::switches('status', '是否可用', $menu['status'] ?? 1)->inactiveValue(0)->activeValue(1)->inactiveText('禁用')->activeText('启用'),
        ];
    }
}
