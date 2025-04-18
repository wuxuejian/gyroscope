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

namespace App\Http\Service\Message;

use App\Http\Dao\Message\MessageDao;
use App\Http\Service\BaseService;
use App\Http\Service\Notice\MessageCateService;
use App\Http\Service\Notice\NoticeRecordService;
use crmeb\services\synchro\Message;
use crmeb\utils\MessageType;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Cache;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * 系统消息
 * Class MessageService.
 */
class MessageService extends BaseService
{
    /**
     * MessageService constructor.
     */
    public function __construct(MessageDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取列表.
     * @param null|mixed $sort
     */
    public function getList(array $where, array $field = ['*'], $sort = null, array $with = []): array
    {
        $data = parent::getList($where, $field, $sort, $with);
        foreach ($data['list'] as &$item) {
            foreach ($item['message_template'] as $value) {
                switch ($value['type']) {
                    case MessageType::TYPE_SYSTEM:
                        $item['system_template'] = $value;
                        break;
                    case MessageType::TYPE_SMS:
                        $item['sms_template'] = $value;
                        break;
                    case MessageType::TYPE_WORK:
                        $item['work_template'] = $value;
                        break;
                    case MessageType::TYPE_DING:
                        $item['ding_template'] = $value;
                        break;
                    case MessageType::TYPE_OTHER:
                        $item['other_template'] = $value;
                        break;
                }
            }
        }
        return $data;
    }

    /**
     * 同步总后台消息.
     * @throws BindingResolutionException
     * @throws InvalidArgumentException
     */
    public function syncMessage(int $entId)
    {
        /** @var Message $make */
        $make = app()->get(Message::class);
        $list = $make->setConfig($entId)->getMessageList();
        /** @var MessageTemplateService $messageService */
        $messageService = app()->get(MessageTemplateService::class);

        // sync remote cate
        app()->get(MessageCateService::class)->syncRemoteCate();

        $res = $this->transaction(function () use ($list, $messageService, $entId) {
            foreach ($list as $item) {
                $messageTemplate     = $item['message_template'] ?? [];
                $messageType         = $item['message_type'] ?? [];
                $cateName            = $item['message_cate']['cate_name'] ?? '';
                $item['relation_id'] = $relationId = $item['id'];
                unset($item['path'], $item['auth_apply'], $item['message_cate'], $item['id'], $item['deleted_at'], $item['message_id'], $item['message_template'], $item['message_type']);
                $item['template_var'] = $messageType['template_var'];
                $messageId            = $this->dao->value(['entid' => $entId, 'relation_id' => $relationId], 'id');
                $template             = [];
                $data                 = [
                    'cate_id'       => $item['cate_id'],
                    'cate_name'     => $cateName,
                    'template_type' => $item['template_type'],
                    'template_time' => $messageType['template_time'],
                    'title'         => $item['title'],
                    'content'       => $item['content'],
                    'relation_id'   => $item['relation_id'],
                    'template_var'  => $item['template_var'],
                ];
                if ($messageId) {
                    $this->dao->update(['id' => $messageId], $data);
                    foreach ($messageTemplate as $value) {
                        $value['relation_id'] = $value['id'];
                        $value['message_id']  = $messageId;
                        if ($value['button_template']) {
                            if (is_array($value['button_template'])) {
                                $value['button_template'] = json_encode($value['button_template']);
                            }
                        }
                        unset($value['id'], $value['deleted_at'], $value['sms_tem_id'], $value['template_id']);
                        $id = $messageService->value(['message_id' => $messageId, 'relation_id' => $value['relation_id']], 'id');
                        if ($id) {
                            unset($value['relation_id'], $value['status'], $value['temp_id']);
                            $messageService->update($id, $value);
                        } else {
                            if ($value['status']) {
                                $value['relation_status'] = $value['status'];
                                $value['template_id']     = $value['temp_id'];
                                unset($value['temp_id']);
                                $template[] = $value;
                            }
                        }
                    }
                } else {
                    $data['entid'] = $entId;
                    if ($data['template_time'] == 1) {
                        $data['remind_time'] = '09:00';
                        if (in_array($data['template_type'], ['clock_remind', 'clock_remind_after_work'])) {
                            $data['remind_time'] = '600';
                        }

                        if ($data['template_type'] == 'remind_work_card_short') {
                            $data['remind_time'] = '300';
                        }

                        if ($data['template_type'] == 'remind_after_work_card_short') {
                            $data['remind_time'] = '10:00';
                        }
                    }

                    $res      = $this->dao->create($data);
                    $template = [];
                    foreach ($messageTemplate as $value) {
                        $value['relation_id'] = $value['id'];
                        $value['message_id']  = $res->id;
                        if ($value['button_template']) {
                            if (is_array($value['button_template'])) {
                                $value['button_template'] = json_encode($value['button_template']);
                            }
                        }
                        unset($value['id'], $value['deleted_at'], $value['sms_tem_id'], $value['template_id']);
                        if ($value['status']) {
                            $value['relation_status'] = $value['status'];
                            $value['button_template'] = json_encode($value['button_template']);
                            $value['template_id']     = $value['temp_id'];
                            unset($value['temp_id']);
                            $template[] = $value;
                        }
                    }
                }
                if ($template) {
                    $messageService->insert($template);
                }
            }
            return true;
        });
        if ($res) {
            Cache::delete(md5('notice_cate_' . $entId));
        }
    }

    /**
     * 获取消息分类列表.
     * @return array|mixed
     * @throws BindingResolutionException
     * @throws InvalidArgumentException
     */
    public function getMessageCateList(int $entId): mixed
    {
        return Cache::remember(md5('notice_cate_' . $entId), (int) sys_config('system_cache_ttl', 3600), function () {
            return toArray(app()->get(MessageCateService::class)->select([], ['*', 'cate_name as label', 'id as value']));
        });
    }

    /**
     * 获取消息分类数量.
     * @return array|mixed
     * @throws BindingResolutionException
     * @throws InvalidArgumentException
     */
    public function getMessageCateCount(int $entId, int $toUid)
    {
        $cate   = $this->getMessageCateList($entId);
        $group  = app()->make(NoticeRecordService::class)->getMessageGroupCount($entId, $toUid);
        $column = [];
        foreach ($group as $item) {
            $column[$item['cate_id']] = $item['count'];
        }
        foreach ($cate as &$item) {
            $item['count'] = $column[$item['id']] ?? 0;
        }
        return $cate;
    }

    /**
     * 获取消息类型.
     * @return array
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getMessageContent(int $entid, string $typeStr)
    {
        return Cache::tags('message')->remember(md5('message_' . $entid . '_' . $typeStr), (int) sys_config('system_cache_ttl', 3600), function () use ($entid, $typeStr) {
            $message = toArray($this->dao->get(
                ['template_type' => $typeStr, 'entid' => $entid],
                ['id', 'template_type', 'title', 'template_var', 'remind_time', 'template_time', 'cate_id', 'cate_name'],
                [
                    'messageTemplate' => fn ($query) => $query->select([
                        'message_id', 'template_id', 'type', 'url', 'uni_url', 'image', 'message_title', 'button_template', 'content_template', 'push_rule', 'minute', 'status',
                    ]),
                ]
            ));

            if (! $message) {
                return $message;
            }
            foreach ($message['message_template'] as $index => $item) {
                preg_match_all('/(?<={\$)[^}]+|(?<={\#)[^}]+/', $item['content_template'], $match);
                $message['message_template'][$index]['template_var'] = $match[0] ?? [];
            }

            return $message;
        });
    }
}
