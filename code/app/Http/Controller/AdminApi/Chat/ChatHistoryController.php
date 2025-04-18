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

namespace App\Http\Controller\AdminApi\Chat;

use App\Http\Controller\AdminApi\AuthController;
use App\Http\Requests\ApiRequest;
use App\Http\Service\Chat\ChatApplicationsService;
use App\Http\Service\Chat\ChatHistoryService;
use App\Http\Service\Chat\ChatRecordService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Response;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\SimpleCache\InvalidArgumentException;
use Spatie\RouteAttributes\Attributes\Delete;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Prefix;
use Spatie\RouteAttributes\Attributes\Put;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * 历史对话.
 */
#[Prefix('ent/chat/history')]
#[Middleware(['auth.admin', 'ent.auth', 'ent.log'])]
class ChatHistoryController extends AuthController
{
    public function __construct(ChatHistoryService $services)
    {
        parent::__construct();
        $this->service = $services;
    }

    /**
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Get('list', '获取历史会话')]
    public function index()
    {
        $where['is_show']    = 1;
        $where['user_id']    = auth('admin')->id();
        $topUpList           = $this->service->getTopUpList($where['user_id']);
        $where['not_id']     = array_column($topUpList, 'id');
        $data                = $this->service->getList(where: $where, sort: 'id');
        $data['top_up_list'] = $topUpList;
        return $this->success($data);
    }

    /**
     * @return mixed
     */
    #[Get('app', '获取应用列表')]
    public function app(ChatApplicationsService $service)
    {
        return $this->success($service->getUserAppList(auth('admin')->id()));
    }

    /**
     * 应用详情.
     * @return mixed
     */
    #[Get('app_info/{id}', '获取应用详情')]
    public function appInfo(ChatApplicationsService $service, $id)
    {
        return $this->success(['appInfo' => $service->get($id)?->toArray()]);
    }

    /**
     * 创建历史对话.
     * @return mixed
     * @throws BindingResolutionException
     */
    #[Post('save', '创建历史对话')]
    public function save(ApiRequest $request)
    {
        $data = $request->postMore([
            ['title', ''],
            ['chat_application_id', 0],
            ['is_show', 0],
        ]);

        if (! $data['title']) {
            return $this->fail('请输入标题');
        }

        $data['user_id'] = auth('admin')->id();

        $appInfo = app()->make(ChatApplicationsService::class)->get($data['chat_application_id']);
        if (! $appInfo) {
            return $this->fail('应用不存在');
        }

        $res = $this->service->create($data);

        return $this->success(['id' => $res->id, 'prologue_text' => $appInfo->prologue_text, 'prologue_list' => $appInfo->prologue_list]);
    }

    /**
     * 更新历史对话.
     * @return mixed
     */
    #[Put('update/{id}', '更新历史对话')]
    public function update(ApiRequest $request, $id)
    {
        $data = $request->postMore([
            ['title', ''],
            ['top_up', ''],
        ]);
        if (! $id) {
            return $this->fail('缺少历史对话ID');
        }
        if (! $data['title'] && $data['top_up'] === '') {
            return $this->fail('请输入标题');
        }

        if ($data['title']) {
            $this->service->update($id, ['title' => $data['title']]);
        }
        if ($data['top_up'] !== '') {
            $this->service->update($id, ['top_up' => $data['top_up'] ? date('Y-m-d H:i:s') : null]);
        }

        return $this->success('修改成功');
    }

    /**
     * 删除历史对话.
     * @return mixed
     */
    #[Delete('delete/{id}', '删除历史对话')]
    public function delete($id)
    {
        if (! $id) {
            return $this->fail('缺少历史对话ID');
        }
        $info = $this->service->get($id);
        if (! $info) {
            return $this->fail('对话记录不存在');
        }
        if ($info->user_id != auth('admin')->id()) {
            return $this->fail('无权限');
        }
        $this->service->delete($id);

        return $this->success('删除成功');
    }

    /**
     * 对话.
     * @return mixed|StreamedResponse
     */
    #[Post('/dialog', '对话')]
    public function dialog(ApiRequest $request)
    {
        [$message, $historyId, $chatRecordUuid, $isShow] = $request->postMore([
            ['message', ''],
            ['history_id', 0],
            ['chat_record_uuid', ''],
            ['is_show', 1],
        ], true);
        if (! $message) {
            return $this->fail('请输入内容');
        }
        if (! $historyId) {
            return $this->fail('缺少历史对话ID');
        }

        return Response::stream(function () use ($message, $historyId, $chatRecordUuid, $isShow) {
            $this->service->dialogV2($message, (int) $historyId, auth('admin')->id(), $chatRecordUuid, $isShow);
        }, 200, [
            'Content-Type'      => 'text/event-stream',
            'Cache-Control'     => 'no-cache',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    /**
     * 对话中断.
     * @return mixed
     * @throws InvalidArgumentException
     */
    #[Post('interrupt', '对话中断')]
    public function interrupt()
    {
        [$chatRecordUuid] = $this->request->postMore([
            ['chat_record_uuid', ''],
        ], true);
        if (! $chatRecordUuid) {
            return $this->fail('缺少对话记录ID');
        }

        return $this->success(['interrupt' => $this->service->interrupt($chatRecordUuid)]);
    }

    /**
     * 清理对话.
     * @return mixed
     * @throws BindingResolutionException
     */
    #[Post('clean_up_dialog/{historyId}', '清理对话')]
    public function cleanUpDialog($historyId)
    {
        if (! $historyId) {
            return $this->fail('缺少对话ID');
        }

        $history = $this->service->get($historyId);
        if (! $history) {
            return $this->fail('对话记录不存在');
        }
        if ($history->user_id !== auth('admin')->id()) {
            return $this->fail('无权限');
        }
        $this->service->cleanUpDialog((int) $historyId);

        return $this->success('清理成功');
    }

    /**
     * 对话记录.
     * @return mixed
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    #[Get('record/{uuid}', '对话记录')]
    public function reecord(ChatRecordService $service, $uuid)
    {
        if (! $uuid) {
            return $this->fail('缺少对话记录ID');
        }
        return $this->success($service->getRecord($uuid, auth('admin')->id()));
    }
}
