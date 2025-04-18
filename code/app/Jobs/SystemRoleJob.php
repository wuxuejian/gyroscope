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

namespace App\Jobs;

use App\Constants\MenuEnum;
use App\Http\Contract\System\MenusInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * 权限队列任务
 */
class SystemRoleJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     * @param mixed $roleId
     */
    public function __construct(private $roleId = 0, private array $rules = [], private array $apis = [], private bool $isEdit = false) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $menuService = app()->get(MenusInterface::class);
            if ($this->isEdit) {
                app('enforcer')->deletePermissionsForUser('role_' . $this->roleId);
            }
            $ruleMenus = $menuService->select(['ids' => $this->apis, 'type' => MenuEnum::TYPE_API], ['api', 'methods'])?->toArray();
            if ($ruleMenus) {
                foreach ($ruleMenus as $menu) {
                    $menu['api'] && app('enforcer')->addPermissionForUser('role_' . $this->roleId, $menu['api'], $menu['methods']);
                    $menu['api'] && app('enforcer')->addPermissionForUser('role_all', $menu['api'], $menu['methods']);
                }
            }
        } catch (\Exception $e) {
            Log::error(__CLASS__ . ':' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTrace()]);
        }
    }
}
