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

namespace App\Http\Controller\AdminApi\Message;

use App\Http\Controller\AdminApi\AuthController;
use App\Http\Service\Crud\SystemCrudEventService;
use App\Http\Service\Message\MessageService;
use App\Http\Service\Message\MessageTemplateService;
use crmeb\utils\MessageType;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Cache;
use Psr\SimpleCache\InvalidArgumentException;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Prefix;
use Spatie\RouteAttributes\Attributes\Put;

/**
 * 消息控制器
 * Class MessageController.
 */
#[Prefix('ent/system/message')]
#[Middleware(['auth.admin', 'ent.auth', 'ent.log'])]
class MessageController extends AuthController
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
    #[Get('list', '系统消息列表')]
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
    #[Get('cate', '系统消息分类')]
    public function cate()
    {
        return $this->success($this->service->getMessageCateCount($this->entId, auth('admin')->id()));
    }

    /**
     * 获取系统消息.
     * @return mixed
     */
    #[Get('find/{id}', '获取系统消息')]
    public function find($id)
    {
        if (! $id) {
            return $this->fail('缺少参数');
        }
        $message = $this->service->get($id, ['*'], ['messageTemplate'])?->toArray();
        if (! $message) {
            return $this->fail('消息不存在');
        }
        $systemTemplate = $smsTemplate = $workTemplate = $dingTemplate = $otherTemplate = [];
        foreach ($message['message_template'] as $template) {
            switch ($template['type']) {
                case MessageType::TYPE_SYSTEM:
                    $systemTemplate = $template;
                    break;
                case MessageType::TYPE_SMS:
                    $smsTemplate = $template;
                    break;
                case MessageType::TYPE_WORK:
                    $workTemplate = $template;
                    break;
                case MessageType::TYPE_DING:
                    $dingTemplate = $template;
                    break;
                case MessageType::TYPE_OTHER:
                    $otherTemplate = $template;
                    break;
            }
        }
        $message['system_template'] = $systemTemplate;
        $message['sms_template']    = $smsTemplate;
        $message['work_template']   = $workTemplate;
        $message['ding_template']   = $dingTemplate;
        $message['other_template']  = $otherTemplate;
        return $this->success($message);
    }

    /**
     * 修改时间.
     * @return mixed
     */
    #[Put('update/{id}', '系统消息修改')]
    public function update(MessageTemplateService $messageTemplateService, $id)
    {
        if (! $id) {
            return $this->fail('缺少参数');
        }

        [$remindTime, $status, $smsStatus, $templateId, $workWebhookUrl, $workStatus, $dingWebhookUrl, $dingStatus, $otherWebhookUrl, $otherStatus] = $this->request->postMore([
            ['remind_time', ''],
            ['status', ''],
            ['sms_status', ''],
            ['template_id', ''],
            ['work_webhook_url', ''],
            ['work_status', 0],
            ['ding_webhook_url', ''],
            ['ding_status', 0],
            ['other_webhook_url', ''],
            ['other_status', 0],
        ], true);

        if ($remindTime) {
            $this->service->update($id, ['remind_time' => $remindTime]);
        } else {
            $contentTemplate = $this->service->value($id, 'content');
            $message         = $messageTemplateService->get(['message_id' => $id, 'type' => 0]);
            $smsMessage      = $messageTemplateService->get(['message_id' => $id, 'type' => 1]);
            $workMessage     = $messageTemplateService->get(['message_id' => $id, 'type' => 2]);
            $dingMessage     = $messageTemplateService->get(['message_id' => $id, 'type' => 3]);
            $otherMessage    = $messageTemplateService->get(['message_id' => $id, 'type' => 4]);

            if ($message) {
                $message->status = $status;
                $message->save();
            }

            $url    = $smsMessage ? $smsMessage->url : '';
            $uniUrl = $smsMessage ? $smsMessage->uni_url : '';
            $messageTemplateService->dumpSql();
            if ($smsMessage) {
                $smsMessage->status      = $smsStatus;
                $smsMessage->template_id = $templateId;
                $smsMessage->save();

                if ($smsMessage->crud_event_id) {
                    app()->make(SystemCrudEventService::class)->update($smsMessage->crud_event_id, ['sms_status' => $dingStatus, 'sms_template_id' => $templateId]);
                }
            } else {
                $messageTemplateService->create([
                    'message_id'       => $id,
                    'template_id'      => $templateId,
                    'status'           => $smsStatus,
                    'type'             => 1,
                    'content_template' => $contentTemplate,
                    'relation_status'  => 1,
                ]);
            }
            if ($workMessage) {
                $workMessage->status      = $workStatus;
                $workMessage->webhook_url = $workWebhookUrl;
                $workMessage->save();

                if ($workMessage->crud_event_id) {
                    app()->make(SystemCrudEventService::class)->update($workMessage->crud_event_id, ['work_webhook_status' => $workStatus, 'work_webhook_url' => $workWebhookUrl]);
                }
            } else {
                $messageTemplateService->create([
                    'message_id'       => $id,
                    'webhook_url'      => $workWebhookUrl,
                    'status'           => $workStatus,
                    'type'             => 2,
                    'url'              => $url,
                    'uni_url'          => $uniUrl,
                    'content_template' => $contentTemplate,
                    'relation_status'  => 1,
                ]);
            }
            if ($dingMessage) {
                $dingMessage->status      = $dingStatus;
                $dingMessage->webhook_url = $dingWebhookUrl;
                $dingMessage->save();

                if ($dingMessage->crud_event_id) {
                    app()->make(SystemCrudEventService::class)->update($dingMessage->crud_event_id, ['ding_webhook_status' => $dingStatus, 'ding_webhook_url' => $dingWebhookUrl]);
                }
            } else {
                $messageTemplateService->create([
                    'message_id'       => $id,
                    'webhook_url'      => $dingWebhookUrl,
                    'status'           => $dingStatus,
                    'type'             => 3,
                    'url'              => $url,
                    'uni_url'          => $uniUrl,
                    'content_template' => $contentTemplate,
                    'relation_status'  => 1,
                ]);
            }
            if ($otherMessage) {
                $otherMessage->status      = $otherStatus;
                $otherMessage->webhook_url = $otherWebhookUrl;
                $otherMessage->save();

                if ($otherMessage->crud_event_id) {
                    app()->make(SystemCrudEventService::class)->update($otherMessage->crud_event_id, ['other_webhook_status' => $dingStatus, 'other_webhook_url' => $dingWebhookUrl]);
                }
            } else {
                $messageTemplateService->create([
                    'message_id'       => $id,
                    'webhook_url'      => $otherWebhookUrl,
                    'status'           => $otherStatus,
                    'type'             => 4,
                    'url'              => $url,
                    'uni_url'          => $uniUrl,
                    'content_template' => $contentTemplate,
                    'relation_status'  => 1,
                ]);
            }
        }

        Cache::tags('message')->clear();
        return $this->success('修改成功');
    }

    /**
     * 修改状态
     * @return mixed
     */
    #[Put('status/{id}/{type}', '系统消息状态')]
    public function status(MessageTemplateService $services, $id, $type)
    {
        if (! $id) {
            return $this->fail('缺少参数');
        }
        $status = $this->request->post('status', 0);

        $message = $this->service->get($id, ['id', 'template_type', 'content', 'title']);
        if (! $message) {
            return $this->fail('消息信息获取失败');
        }

        $info = $services->get(['message_id' => $id, 'type' => $type]);
        if (! $info) {
            $info = $services->create([
                'message_id'       => $id,
                'type'             => $type,
                'url'              => '',
                'relation_status'  => 1,
                'uni_url'          => '',
                'message_title'    => $message->title,
                'content_template' => $message->content,
            ]);
        }
        if (! $info->relation_status && ! $info->crud_event_id) {
            return $this->fail('平台消息已被关闭，无法修改消息状态');
        }

        $info->status = $status;
        $info->save();

        if ($info->crud_event_id) {
            $eventInfo = app()->make(SystemCrudEventService::class)->get($info->crud_event_id);
            if ($eventInfo) {
                $eventInfo->status = $status;
                $eventInfo->save();
            }
        }

        Cache::tags('message')->forget('message_' . $this->entId . '_' . $message->template_type);

        return $this->success('修改成功');
    }

    /**
     * 用户是否可取消订阅.
     * @return mixed
     */
    #[Put('subscribe/{id}', '用户是否可取消订阅')]
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
    #[Put('sync', '系统消息同步')]
    public function syncMessage()
    {
        $this->service->syncMessage($this->entId);
        return $this->success('ok');
    }
}
