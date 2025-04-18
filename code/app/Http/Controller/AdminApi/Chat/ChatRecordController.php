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
use App\Http\Service\Chat\ChatHistoryService;
use App\Http\Service\Chat\ChatRecordService;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Prefix;

/**
 * 聊天记录.
 */
#[Prefix('ent/chat/record')]
#[Middleware(['auth.admin', 'ent.auth', 'ent.log'])]
class ChatRecordController extends AuthController
{
    public function __construct(ChatRecordService $services)
    {
        parent::__construct();
        $this->service = $services;
    }

    /**
     * @return mixed
     */
    #[Get('list', '获取聊天记录')]
    public function index(ChatHistoryService $service)
    {
        $where = $this->request->getMore([
            ['chat_history_id', ''],
        ]);

        if (! $where['chat_history_id']) {
            return $this->fail('缺少历史对话ID');
        }

        $history = $service->get($where['chat_history_id']);
        if (! $history) {
            return $this->fail('历史对话不存在');
        }

        if ($history->user_id != auth('admin')->id()) {
            return $this->fail('无权限');
        }

        $where['is_show'] = 1;
        $data             = $this->service->getList(where: $where, field: ['chat_record_uuid', 'sql_text', 'chat_history_id', 'vote_status', 'problem_text', 'id', 'answer_text', 'prompt_tokens', 'completion_tokens', 'tokens', 'run_time', 'created_at', 'uid'], sort: 'id');
        foreach ($data['list'] as &$item) {
            if (! isset($item['sql_text'])) {
                continue;
            }
            $sql = json_decode($item['sql_text'], true);

            if (! $sql) {
                continue;
            }

            if (empty($sql['list_sql']) || empty($sql['page_sql']) || empty($sql['table_fields'])) {
                continue;
            }
            if (str_contains($sql['list_sql'], '${page}')) {
                $item['is_page'] = true;
            }
        }
        return $this->success($data);
    }
}
