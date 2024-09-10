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

namespace App\Http\Controller\AdminApi\Approve;

use App\Http\Controller\AdminApi\AuthController;
use App\Http\Requests\enterprise\approve\ApproveHolidayTypeRequest;
use App\Http\Service\Approve\ApproveHolidayTypeService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Spatie\RouteAttributes\Attributes\Delete;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Prefix;
use Spatie\RouteAttributes\Attributes\Put;

/**
 * 假期类型.
 */
#[Prefix('ent/approve/holiday_type')]
#[Middleware(['auth.admin', 'ent.auth', 'ent.log'])]
class ApproveHolidayTypeController extends AuthController
{
    public function __construct(ApproveHolidayTypeService $services)
    {
        parent::__construct();
        $this->service = $services;
    }

    /**
     * 展示数据.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    #[Get('/list', '获取假期类型列表接口')]
    public function index(): mixed
    {
        $where = $this->request->getMore($this->getSearchField());
        return $this->success($this->service->getList($where));
    }

    /**
     * 获取详情.
     * @throws \ReflectionException
     * @throws BindingResolutionException
     */
    #[Get('info/{id}', '获取假期类型详情接口')]
    public function info($id): mixed
    {
        if (! $id) {
            return $this->fail($this->message['edit']['emtpy']);
        }
        return $this->success($this->service->getInfo(['id' => $id]));
    }

    /**
     * 保存.
     * @throws BindingResolutionException
     */
    #[Post('/', '保存假期类型接口')]
    public function store(ApproveHolidayTypeRequest $request): mixed
    {
        $this->service->saveHolidayType($request->postMore($this->getRequestFields()));
        return $this->success(__('common.insert.succ'));
    }

    /**
     * 修改.
     * @throws BindingResolutionException
     */
    #[Put('{id}', '修改假期类型接口')]
    public function update($id, ApproveHolidayTypeRequest $request): mixed
    {
        if (! $id) {
            return $this->fail(__('common.empty.attrs', ['attr' => 'id']));
        }

        $this->service->updateHolidayType((int) $id, $request->postMore($this->getRequestFields()));
        return $this->success(__('common.update.succ'));
    }

    /**
     * 删除.
     * @throws \ReflectionException
     * @throws BindingResolutionException
     */
    #[Delete('{id}', '删除假期类型接口')]
    public function delete($id): mixed
    {
        if (! $id) {
            return $this->fail(__('common.empty.attrs'));
        }
        $this->service->deleteHolidayType((int) $id);
        return $this->success('common.delete.succ');
    }

    /**
     * 下拉数据.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    #[Get('select', '获取假期类型下拉数据接口')]
    public function select(): mixed
    {
        return $this->success($this->service->getSelectList(auth('admin')->id()));
    }

    /**
     * 提取字段.
     */
    protected function getRequestFields(): array
    {
        return [
            ['name', ''],
            ['new_employee_limit', 0],
            ['new_employee_limit_month', 1],
            ['duration_type', 0],
            ['duration_calc_type', 0],
            ['sort', 0],
        ];
    }

    /**
     * 搜索字段.
     */
    protected function getSearchField(): array
    {
        return [
            ['name', '', 'name_like'],
        ];
    }
}
