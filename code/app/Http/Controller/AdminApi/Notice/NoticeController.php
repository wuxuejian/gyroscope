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

namespace App\Http\Controller\AdminApi\Notice;

use App\Http\Contract\Notice\NoticeInterface;
use App\Http\Controller\AdminApi\AuthController;
use App\Http\Service\Notice\NoticeSubscribeService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Class NoticeController.
 */
class NoticeController extends AuthController
{
    /**
     * NoticeController constructor.
     */
    public function __construct(NoticeInterface $services)
    {
        parent::__construct();
        $this->service = $services;
    }

    /**
     * 消息列表.
     * @return mixed
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function index()
    {
        [$cateId,$title,$isRead] = $this->request->getMore([
            ['cate_id', 0],
            ['title', ''],
            ['is_read', ''],
            ['cate_id', 0],
        ], true);
        return $this->success($this->service->getMessageList($this->uuid, $this->entId, $cateId, $title, $isRead));
    }

    /**
     * 修改消息状态
     * @return mixed
     */
    public function update($id, $isRead)
    {
        if (! $id) {
            return $this->fail('common.empty.attrs');
        }
        $messageInfo = $this->service->get($id);
        if (! $messageInfo) {
            return $this->fail('消息不存在');
        }
        $messageInfo->is_read = $isRead;
        if ($messageInfo->save()) {
            return $this->success('common.update.succ');
        }
        return $this->fail('common.update.fail');
    }

    /**
     * 修改处理状态
     * @return mixed
     */
    public function updateHandle($id, $isHandle)
    {
        if (! $id) {
            return $this->fail('common.empty.attrs');
        }
        $messageInfo = $this->service->get($id);
        if (! $messageInfo) {
            return $this->fail('消息不存在');
        }
        $messageInfo->is_handle = $isHandle;
        if ($messageInfo->save()) {
            return $this->success('common.update.succ');
        }
        return $this->fail('common.update.fail');
    }

    /**
     * 批量修改.
     * @return mixed
     */
    public function batchUpdate($isRead)
    {
        $ids = $this->request->post('ids', []);
        if (! $ids) {
            return $this->fail('缺少参数');
        }

        if ($this->service->update(['ids' => $ids], ['is_read' => $isRead])) {
            return $this->success('修改成功');
        }
        return $this->fail('修改失败');
    }

    /**
     * 批量删除.
     * @return mixed
     */
    public function batchDelete()
    {
        $ids = $this->request->post('ids', []);
        if (! $ids) {
            return $this->fail('缺少参数');
        }
        if ($this->service->delete(['ids' => $ids])) {
            return $this->success('删除成功');
        }
        return $this->fail('删除失败');
    }

    /**
     * 用户订阅列表.
     * @return mixed
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function subscribe()
    {
        $where = $this->request->getMore([
            ['cate_id', 0],
            ['title', ''],
            ['entid', 1],
        ]);

        return $this->success($this->service->getListForUser($this->uuid, $this->entId, $where));
    }

    /**
     * 用户订阅/取消订阅消息.
     * @return mixed
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function toSubscribe(NoticeSubscribeService $services, $id)
    {
        $status = $this->request->post('status', 0);
        $services->saveSubscribe($this->uuid, $this->entId, (int) $id, (int) $status);
        return $this->success('操作成功');
    }
}
