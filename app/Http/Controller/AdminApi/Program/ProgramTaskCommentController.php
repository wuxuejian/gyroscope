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

namespace App\Http\Controller\AdminApi\Program;

use App\Http\Controller\AdminApi\AuthController;
use App\Http\Service\Program\ProgramTaskCommentService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Prefix;
use Spatie\RouteAttributes\Attributes\Resource;

/**
 * 项目任务评论.
 * Class ProgramTaskCommentController.
 */
#[Prefix('ent/task_comment')]
#[Resource('/', false, except: ['show', 'create', 'edit'], names: [
    'index'   => '项目任务评论列表',
    'store'   => '项目任务评论保存',
    'update'  => '项目任务评论修改',
    'destroy' => '项目任务评论删除',
], parameters: ['' => 'id'])]
#[Middleware(['auth.admin', 'ent.auth', 'ent.log'])]
class ProgramTaskCommentController extends AuthController
{
    public function __construct(ProgramTaskCommentService $services)
    {
        parent::__construct();
        $this->service = $services;
    }

    /**
     * 列表.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function index(): mixed
    {
        $where = $this->request->getMore([
            ['task_id', ''],
        ]);

        $where['task_id'] = (int) $where['task_id'];
        return $this->success($this->service->getList($where));
    }

    /**
     * 保存.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function store(): mixed
    {
        $res = $this->service->saveComment($this->request->postMore($this->getRequestFields()));
        return $this->success('common.insert.succ', ['id' => $res->id]);
    }

    /**
     * 修改.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function update($id): mixed
    {
        if (! $id) {
            throw $this->exception(__('common.empty.attrs'));
        }

        $data = $this->request->postMore($this->getRequestFields());
        $this->service->updateComment($data, (int) $id);
        return $this->success('common.update.succ');
    }

    /**
     * 删除.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function destroy($id): mixed
    {
        if (! $id) {
            return $this->fail(__('common.empty.attrs'));
        }

        $this->service->deleteComment((int) $id);
        return $this->success('common.delete.succ');
    }

    /**
     * 提取字段.
     */
    protected function getRequestFields(): array
    {
        return [
            ['pid', ''],
            ['task_id', ''],
            ['describe', ''],
            ['reply_uid', ''],
        ];
    }
}
