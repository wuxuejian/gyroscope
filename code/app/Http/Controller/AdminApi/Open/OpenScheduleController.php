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

namespace App\Http\Controller\AdminApi\Open;

use App\Constants\ScheduleEnum;
use App\Http\Controller\AdminApi\AuthController;
use App\Http\Middleware\AuthOpenApi;
use App\Http\Requests\user\ScheduleRequest;
use App\Http\Service\Admin\AdminService;
use App\Http\Service\Schedule\ScheduleService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Validation\ValidationException;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Prefix;
use Spatie\RouteAttributes\Attributes\Resource;

#[Prefix('open/schedule')]
#[Resource('/', false, except: ['show', 'edit', 'create', 'index'], names: [
    'store'   => '保存日程接口',
    'update'  => '修改日程接口',
    'destroy' => '删除日程接口',
], parameters: ['' => 'id'])]
#[Middleware([AuthOpenApi::class])]
class OpenScheduleController extends AuthController
{
    public function __construct(ScheduleService $schedule)
    {
        parent::__construct();
        $this->service = $schedule;
    }

    /**
     * 保存日程.
     * @param ScheduleRequest $request
     * @param AdminService $adminService
     * @return mixed
     * @throws BindingResolutionException
     * @throws ValidationException
     */
    public function store(ScheduleRequest $request, AdminService $adminService): mixed
    {
        $request->scene('create')->check();
        $data = $this->request->postMore([
            ['title', ''],
            ['member', []], // 参与人
            ['content', ''],
            ['cid', 0],
            ['color', ''],
            ['remind', 0],
            ['remind_time', ''],
            ['repeat', 0],
            ['period', 0],
            ['rate', 0],
            ['days', []],
            ['all_day', 0],
            ['start_time', ''],
            ['end_time', ''],
            ['fail_time', null],
        ]);

        $uid = (int)$this->request->post('uid', 0);
        if ($uid && !$adminService->exists(['id' => $uid, 'status' => 1])) {
            return $this->fail('业务员不存在');
        }

        $this->service->saveSchedule($uid, 1, $data);
        return $this->success('添加成功');
    }

    /**
     * 修改日程.
     * @param ScheduleRequest $request
     * @param $id
     * @return mixed
     * @throws BindingResolutionException
     * @throws ValidationException
     * @throws \ReflectionException
     */
    public function update(ScheduleRequest $request, $id): mixed
    {
        if (!$id) {
            return $this->fail('缺少日程ID');
        }

        $request->scene('update')->check();
        $data = $this->request->postMore([
            ['title', ''],
            ['member', []], // 参与人
            ['content', ''],
            ['cid', 0],
            ['color', ''],
            ['remind', 0],
            ['remind_time', ''],
            ['repeat', 0],
            ['period', 0],
            ['rate', 0],
            ['days', []],
            ['all_day', 0],
            ['start_time', ''],
            ['end_time', ''],
            ['fail_time', null],
            ['type', ScheduleEnum::CHANGE_ALL],
            ['start', ''],
            ['end', ''],
        ]);
        $this->service->updateSchedule(1, (int)$id, $data);
        return $this->success('修改成功');
    }

    /**
     * 删除日程.
     * @param ScheduleRequest $request
     * @param AdminService $adminService
     * @param $id
     * @return mixed
     * @throws BindingResolutionException
     * @throws ValidationException
     * @throws \ReflectionException
     */
    public function destroy(ScheduleRequest $request, AdminService $adminService, $id): mixed
    {
        if (!$id) {
            return $this->fail('缺少日程ID');
        }
        $request->scene('delete')->check();
        $data = $this->request->postMore([
            ['type', ScheduleEnum::CHANGE_ALL],
            ['start', ''],
            ['end', ''],
        ]);

        $uid = (int)$this->request->post('uid', 0);
        if ($uid && !$adminService->exists(['id' => $uid, 'status' => 1])) {
            return $this->fail('业务员不存在');
        }

        $this->service->deleteSchedule($uid, 1, (int)$id, $data);
        return $this->success('删除成功');
    }
}
