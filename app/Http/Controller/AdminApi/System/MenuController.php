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

namespace App\Http\Controller\AdminApi\System;

use App\Http\Contract\System\MenusInterface;
use App\Http\Controller\AdminApi\AuthController;
use App\Http\Middleware\AuthAdmin;
use App\Http\Middleware\AuthEnterprise;
use App\Http\Middleware\CheckRuleCompany;
use App\Http\Requests\system\SystemMenusRequest;
use App\Http\Service\Crud\SystemCrudService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Prefix;
use Spatie\RouteAttributes\Attributes\Resource;

/**
 * 企业菜单.
 */
#[Prefix('ent/system/menus')]
#[Resource('/', false, names: [
    'index'   => '系统菜单列表',
    'create'  => '系统菜单创建表单',
    'store'   => '系统菜单创建',
    'show'    => '系统菜单显示隐藏',
    'edit'    => '系统菜单修改表单',
    'update'  => '系统菜单修改',
    'destroy' => '系统菜单删除',
], parameters: ['' => 'id'])]
#[Middleware(['auth.admin', 'ent.auth', 'ent.log'])]
class MenuController extends AuthController
{
    public function __construct(MenusInterface $services)
    {
        parent::__construct();
        $this->service = $services;
        $this->middleware([AuthAdmin::class, AuthEnterprise::class, CheckRuleCompany::class])->except(['getNotSaveMenus']);
    }

    /**
     * 获取菜单列表.
     */
    public function index(): mixed
    {
        $where = $this->request->getMore([
            ['menu_name', ''],
        ]);
        return $this->success($this->service->getAllMenusList($where));
    }

    /**
     * 创建菜单.
     */
    public function create(): mixed
    {
        return $this->success($this->service->getCreateForm($this->entId));
    }

    /**
     * 保存菜单.
     */
    public function store(): mixed
    {
        $request = app()->get(SystemMenusRequest::class);
        $data    = $request->postMore([
            ['menu_name', ''],
            ['type', 0],
            ['methods', ''],
            ['path', []],
            ['pid', 0],
            ['menu_path', ''],
            ['list_path', ''],
            ['dash_path', ''],
            ['menu_type', 0],
            ['crud_id', 0],
            ['api', ''],
            ['icon', ''],
            ['sort', 0],
            ['is_show', 0],
            ['status', 0],
            ['unique_auth', ''],
            ['position', 0],
            ['uni_path', ''],
            ['uni_img', ''],
            ['component', ''],
        ]);
        $data['menu_path'] = match ((int) $data['menu_type']) {
            1       => $data['list_path'],
            2       => $data['dash_path'],
            default => $data['menu_path'],
        };
        $data['component'] = match ((int) $data['menu_type']) {
            1       => 'develop/module/index',
            2       => 'develop/dashboard/index',
            default => $data['component'],
        };
        unset($data['list_path'],$data['dash_path']);
        if ($data['type'] == 0) {
            $request->scene('menu')->check(['menu_name' => $data['menu_name'], 'menu_path' => $data['menu_path']]);
        } else {
            $request->scene('api')->check(['menu_name' => $data['menu_name'], 'api' => $data['api'], 'methods' => $data['methods']]);
        }

        $path = $data['path'];
        if ($path) {
            $data['pid']   = $path[count($path) - 1];
            $data['level'] = count($path);
        }
        return $this->success('添加菜单成功', $this->service->saveMenu($data, $this->entId));
    }

    /**
     * 修改菜单状态.
     * @throws BindingResolutionException
     */
    public function show($id): mixed
    {
        if (! $id) {
            return $this->fail('修改的菜单的ID不能为空');
        }
        $is_show = $this->request->input('is_show');
        if ($is_show === null) {
            return $this->fail('修改的菜单的状态为必填项');
        }
        if ($this->service->isShow($id, ['is_show' => $is_show])) {
            return $this->success('common.update.succ');
        }
        return $this->fail('common.update.fail');
    }

    /**
     * 获取菜单修改表单详情.
     * @throws BindingResolutionException
     */
    public function edit($id): mixed
    {
        if (! $id) {
            return $this->fail('修改的菜单的ID不能为空');
        }
        return $this->success($this->service->getUpdateForm($id, $this->entId));
    }

    /**
     * 保存菜单修改.
     * @throws BindingResolutionException
     */
    public function update($id): mixed
    {
        $request = app()->get(SystemMenusRequest::class);
        $data    = $request->postMore([
            ['menu_name', ''],
            ['type', 0],
            ['methods', ''],
            ['path', []],
            ['pid', 0],
            ['menu_path', ''],
            ['list_path', ''],
            ['dash_path', ''],
            ['menu_type', 0],
            ['crud_id', 0],
            ['api', ''],
            ['icon', ''],
            ['sort', 0],
            ['is_show', 0],
            ['status', 0],
            ['unique_auth', ''],
            ['position', 0],
            ['uni_path', ''],
            ['uni_img', ''],
            ['component', ''],
        ]);
        $data['menu_path'] = match ((int) $data['menu_type']) {
            1       => $data['list_path'],
            2       => $data['dash_path'],
            default => $data['menu_path'],
        };
        unset($data['list_path'],$data['dash_path']);
        $data['component'] = match ((int) $data['menu_type']) {
            1       => 'develop/module/index',
            2       => 'develop/dashboard/index',
            default => $data['component'],
        };
        if (! $id) {
            return $this->fail('修改的菜单ID不能为空');
        }
        if ($data['type'] == 0) {
            $request->scene('menu')->check([
                'menu_name' => $data['menu_name'],
                'menu_path' => $data['menu_path'],
            ]);
        } else {
            $request->scene('api')->check([
                'menu_name' => $data['menu_name'],
                'api'       => $data['api'],
                'methods'   => $data['methods'],
            ]);
        }
        if (! $data['unique_auth']) {
            $data['unique_auth'] = uniqid('menus');
        }
        $path = array_filter($data['path'], fn ($val) => (bool) $val);
        if ($path) {
            $data['pid']   = $path[count($path) - 1];
            $data['level'] = count($path);
        }
        //        获取菜单的crud_id
        if ($data['menu_path'] && $data['menu_type'] == 1) {
            $tableName = str_replace(['/crud/module/', '/list'], '', $data['menu_path']);
            if ($tableName) {
                $crudId           = app()->make(SystemCrudService::class)->value(['table_name_en' => $tableName], 'id');
                $data['crud_id']  = $crudId ?: 0;
                $data['uni_path'] = '/pages/module/list?tablename=' . $tableName;
            }
        }
        $this->service->update($id, $data);
        return $this->success('common.update.succ');
    }

    /**
     * 删除菜单.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function destroy($id): mixed
    {
        if (! $id) {
            return $this->fail('删除的菜单的ID不能为空');
        }
        $this->service->destroy($id);
        return $this->success('删除成功');
    }

    /**
     * 获取菜单列表树.
     */
    public function getTree(): mixed
    {
        return $this->success($this->service->getMenusForCompany($this->entId));
    }

    /**
     * 获取菜单列表树.
     */
    public function saveMenu(): mixed
    {
        return $this->success($this->service->saveMenusForCompany($this->entId));
    }

    /**
     * 获取没有保存的权限.
     */
    #[Get('not_save', '获取没有保存的权限')]
    public function getNotSaveMenus(): mixed
    {
        return $this->success($this->service->getNoSaveMenusList());
    }
}
