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

namespace App\Http\Controller\AdminApi\Attendance;

use App\Http\Controller\AdminApi\AuthController;
use App\Http\Service\Attendance\CalendarConfigService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Prefix;
use Spatie\RouteAttributes\Attributes\Put;

/**
 * 日历配置
 * Class CalendarConfigController.
 */
#[Prefix('ent/attendance/calendar')]
#[Middleware(['auth.admin', 'ent.auth', 'ent.log'])]
class CalendarConfigController extends AuthController
{
    /**
     * CalendarConfigController constructor.
     * @throws \Throwable
     */
    public function __construct(CalendarConfigService $services)
    {
        parent::__construct();
        $this->service = $services;
    }

    /**
     * 获取列表.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    #[Get('{time}', '考勤日历配置详情')]
    public function index($time): mixed
    {
        return $this->success($this->service->getRestList($time));
    }

    /**
     * 修改.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    #[Put('{date}', '考勤日历配置保存')]
    public function update($date): mixed
    {
        if (! $date) {
            return $this->fail(__('common.empty.attrs'));
        }

        [$data] = $this->request->postMore([
            ['data', []],
        ], true);

        $this->service->updateCalendar($date, $data, config('app.timezone'));
        return $this->success('common.operation.succ');
    }
}
