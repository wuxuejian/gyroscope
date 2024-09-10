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

namespace App\Task\system;

use App\Http\Contract\System\MenusInterface;
use Hhxsv5\LaravelS\Swoole\Task\Task;
use Illuminate\Support\Facades\Log;
use Lauthz\Facades\Enforcer;

class SystemRoleTask extends Task
{
    public function __construct(protected int $roleId = 0, protected array $rules = [], protected array $apis = [], protected bool $isEdit = false) {}

    public function handle()
    {
        try {
            $menuService = app()->get(MenusInterface::class);
            if ($this->isEdit) {
                Enforcer::deletePermissionsForUser('rule_' . $this->roleId);
                Enforcer::deletePermissionsForUser('api_' . $this->roleId);
                Enforcer::deletePermissionsForUser('uni_' . $this->roleId);
            }
            if ($this->rules) {
                $ruleMenus = toArray($menuService->select(['ids' => $this->rules], ['id', 'menu_path', 'methods', 'uni_path', 'uni_img']));
                foreach ($ruleMenus as $menu) {
                    if ($menu['menu_path']) {
                        Enforcer::addPermissionForUser('rule_' . $this->roleId, $menu['menu_path']);
                    }
                    if ($menu['uni_path']) {
                        Enforcer::addPermissionForUser('uni_' . $this->roleId, $menu['uni_path']);
                    }
                }
            }
            if ($this->apis) {
                $apiMenus = toArray($menuService->select(['ids' => array_unique($this->apis)], ['id', 'menu_path', 'methods', 'uni_path', 'uni_img']));
                foreach ($apiMenus as $menu) {
                    Enforcer::addPermissionForUser('api_' . $this->roleId, $menu['menu_path'], $menu['methods']);
                }
            }
        } catch (\Exception $e) {
            Log::error(__CLASS__ . ':' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTrace()]);
        }
    }
}
