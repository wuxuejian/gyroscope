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

namespace App\Http\Controller\AdminApi\Cloud;

use App\Http\Controller\AdminApi\AuthController;
use App\Http\Requests\Cloud\CloudSpaceRequest;
use App\Http\Service\Cloud\CloudFileService;
use App\Http\Service\Cloud\CloudShareService;
use App\Http\Service\Cloud\CloudSpaceService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Spatie\RouteAttributes\Attributes\Delete;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Prefix;
use Spatie\RouteAttributes\Attributes\Put;

/**
 * 云盘空间.
 */
#[Prefix('ent/cloud/space')]
#[Middleware(['auth.admin', 'ent.auth', 'ent.log'])]
final class CloudSpaceController extends AuthController
{
    public function __construct(CloudSpaceService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    /**
     * 获取云盘空间列表.
     */
    #[Get('list', '云盘空间列表')]
    public function list()
    {
        return $this->success($this->service->spaceList(['uid' => auth('admin')->id(), 'pid' => 0]));
    }

    /**
     * 目录列表.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    #[Get('dir', '目录列表')]
    public function dirList(): mixed
    {
        return $this->success($this->service->dirList(auth('admin')->id()));
    }

    /**
     * 最近文件.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    #[Get('lately', '最近文件')]
    public function latelyList(CloudFileService $service): mixed
    {
        $where = $this->request->getMore([
            ['sort_type', ''],
            ['sort_by', ''],
            ['file_type', ''],
            ['keyword', ''],
            ['pid', ''],
        ]);
        return $this->success($service->latelyFileList(auth('admin')->id(), $where));
    }

    /**
     * 回收站文件.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    #[Get('recycle', '回收站文件')]
    public function recycleList(CloudFileService $service): mixed
    {
        $where = $this->request->getMore([
            ['sort_type', 'deleted_at'],
            ['sort_by', 'desc'],
            ['file_type', ''],
            ['keyword', ''],
            ['is_del', 1],
            ['time', ''],
            ['type', 0],
        ]);
        return $this->success($service->deleteList(auth('admin')->id(), $where));
    }

    /**
     * 创建云盘空间.
     * @return mixed
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     * @throws \Throwable
     */
    #[Post('create', '创建云盘空间')]
    public function create(CloudSpaceRequest $request)
    {
        $data = $request->postMore([
            ['name', ''],
            ['rule', []],
        ]);
        $this->service->createSpace(auth('admin')->id(), $data['name'], $data['rule']);
        return $this->success('创建成功');
    }

    /**
     * 获取云盘空间权限.
     * @return mixed
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    #[Get('rules/{id}', '获取云盘空间权限')]
    public function rules(CloudShareService $service, $id)
    {
        if (! $id && ! $this->service->exists(['type' => 1, 'id' => $id, 'user_id' => auth('admin')->id()])) {
            return $this->fail('暂无权限获取权限');
        }
        return $this->success($service->shareUserLst(['folder_id' => (int) $id]));
    }

    /**
     * 更新云盘空间.
     * @return mixed
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     * @throws \Throwable
     */
    #[Put('update/{id}', '更新云盘空间')]
    public function update(CloudSpaceRequest $request, $id)
    {
        $data = $request->postMore([
            ['name', ''],
            ['rule', []],
        ]);
        $this->service->updateSpace((int) $id, $data['name'], $data['rule'], auth('admin')->id());
        return $this->success('修改成功');
    }

    /**
     * 删除云盘空间.
     * @return mixed
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    #[Delete('delete/{id}', '删除云盘空间')]
    public function delete($id)
    {
        if (! $id) {
            return $this->fail('缺少必要参数');
        }
        $this->service->deleteSpace((int) $id, auth('admin')->id());
        return $this->success('删除成功');
    }

    /**
     * 彻底删除云盘文件.
     */
    #[Delete('force_delete/{id}', '彻底删除云盘文件')]
    public function deleteFile(CloudFileService $service, $id): mixed
    {
        if (! $id) {
            return $this->fail('缺少必要参数');
        }
        $service->deleteFile((int) $id, auth('admin')->id());
        return $this->success('删除成功');
    }

    /**
     * 批量彻底删除云盘文件.
     */
    #[Delete('force_deletes', '批量彻底删除云盘文件')]
    public function batchDeleteFile(CloudFileService $service): mixed
    {
        $id = $this->request->post('id', []);
        if (! $id) {
            return $this->fail('缺少必要参数');
        }
        $success = $service->batchDeleteFile((array) $id, auth('admin')->id());
        return $this->success('已成功删除' . $success . '个文件');
    }

    /**
     * 恢复云盘文件.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    #[Put('recovery/{id}', '恢复云盘文件')]
    public function recovery(CloudFileService $service, $id): mixed
    {
        if (! $id) {
            return $this->fail('缺少必要参数');
        }
        $service->recoveryFile((int) $id, auth('admin')->id());
        return $this->success('操作成功');
    }

    /**
     * 批量恢复云盘文件.
     */
    #[Put('batch_recovery', '批量恢复云盘文件')]
    public function batchRecovery(CloudFileService $service): mixed
    {
        $id = $this->request->post('id', []);
        if (! $id) {
            return $this->fail('缺少必要参数');
        }
        $service->batchRecoveryFile((array) $id, auth('admin')->id());
        return $this->success('操作成功');
    }

    /**
     * 转让云盘空间.
     * @return mixed
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Post('transfer/{id}', '转让云盘空间')]
    public function transfer($id)
    {
        $toUid = $this->request->input('to_uid', '');
        $this->service->transfer((int) $id, auth('admin')->id(), $toUid);
        return $this->success('转让成功');
    }
}
