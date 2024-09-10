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

use App\Http\Controller\AdminApi\AuthController;
use App\Http\Service\Message\MessageService;
use App\Http\Service\Message\MessageTemplateService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Cache;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * 消息控制器
 * Class Message.
 */
class NoticeTemplateController extends AuthController
{
    public function __construct(MessageService $services)
    {
        parent::__construct();
        $this->service = $services;
    }

    /**
     * 消息列表.
     * @return mixed
     */
    public function index()
    {
        $where = $this->request->getMore([
            ['cate_id', 0],
            ['title', ''],
            ['entid', 1],
        ]);

        return $this->success($this->service->getList(where: $where, with: ['messageTemplate']));
    }

    /**
     * 分类.
     * @return mixed
     * @throws BindingResolutionException
     * @throws InvalidArgumentException
     */
    public function cate()
    {
        return $this->success($this->service->getMessageCateList($this->entId));
    }

    /**
     * 修改时间.
     * @return mixed
     */
    public function update($id)
    {
        if (! $id) {
            return $this->fail('缺少参数');
        }

        [$remindTime] = $this->request->postMore([
            ['remind_time', ''],
        ], true);

        if (! $remindTime) {
            return $this->fail('请选择时间');
        }

        $this->service->update($id, ['remind_time' => $remindTime]);
        Cache::tags('message')->clear();
        return $this->success('修改成功');
    }

    /**
     * 修改状态
     * @return mixed
     */
    public function status(MessageTemplateService $services, $id, $type)
    {
        if (! $id) {
            return $this->fail('缺少参数');
        }
        $status = $this->request->post('status', 0);

        $info = $services->get(['message_id' => $id, 'type' => $type]);

        if (! $info->relation_status) {
            return $this->fail('平台消息已被关闭，无法修改消息状态');
        }

        $message = $this->service->get($info->message_id, ['id', 'template_type']);
        if (! $message) {
            return $this->fail('消息信息获取失败');
        }

        $info->status = $status;
        $info->save();

        Cache::tags('message')->forget('message_' . $this->entId . '_' . $message->template_type);

        return $this->success('修改成功');
    }

    /**
     * 用户是否可取消订阅.
     * @return mixed
     */
    public function user_sub($id)
    {
        $status = $this->request->post('status', 0);
        $this->service->update($id, ['user_sub' => $status]);
        return $this->success('修改成功');
    }

    /**
     * 同步消息.
     * @return mixed
     * @throws BindingResolutionException
     * @throws InvalidArgumentException
     */
    public function syncMessage()
    {
        $this->service->syncMessage($this->entId);
        return $this->success('ok');
    }
}
