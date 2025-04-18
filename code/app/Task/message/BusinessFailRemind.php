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

namespace App\Task\message;

use App\Constants\ApproveEnum;
use App\Http\Service\Admin\AdminService;
use App\Http\Service\Approve\ApproveApplyService;
use App\Http\Service\Approve\ApproveService;
use crmeb\utils\MessageType;
use Hhxsv5\LaravelS\Swoole\Task\Task;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Log;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * 【业务类型】未通过审批醒
 * Class BusinessFailRemind.
 */
class BusinessFailRemind extends Task
{
    public function __construct(protected $entId, protected $applyId) {}

    /**
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function handle()
    {
        try {
            $applyInfo = app()->get(ApproveApplyService::class)->get($this->applyId, ['node_id', 'user_id', 'status', 'approve_id', 'created_at'])?->toArray();
            if (! $applyInfo) {
                return;
            }
            if ($applyInfo['status'] != 2) {
                return;
            }
            $selfUserInfo = app()->get(AdminService::class)->get($applyInfo['user_id'], ['*'], ['frame']);
            $approveName  = app()->get(ApproveService::class)->value($applyInfo['approve_id'], 'name');

            $task = new MessageSendTask(
                entid: $this->entId,
                i: $this->entId,
                type: MessageType::BUSINESS_FAIL_TYPE,
                toUid: ['to_uid' => $selfUserInfo['id'], 'phone' => $selfUserInfo['phone'] ?? ''],
                params: [
                    '申请人部门' => $selfUserInfo['frame']['name'] ?? '',
                    '申请人'   => $selfUserInfo['name'] ?? '',
                    '申请时间'  => $applyInfo['created_at'],
                    '业务类型'  => $approveName,
                ],
                other: ['id' => $this->applyId],
                linkId: $this->applyId,
                linkStatus: $applyInfo['status']
            );
            Task::deliver($task);
            Task::deliver(new StatusChangeTask(ApproveEnum::LINK_NOTICE, $applyInfo['status'], $this->entId, $this->applyId));
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . ':' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTrace()]);
        }
    }
}
