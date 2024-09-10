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
use App\Http\Service\Approve\ApproveProcessService;
use App\Http\Service\Approve\ApproveService;
use App\Http\Service\Approve\ApproveUserService;
use App\Http\Service\Crud\SystemCrudApproveProcessService;
use App\Http\Service\Crud\SystemCrudApproveService;
use crmeb\utils\MessageType;
use Hhxsv5\LaravelS\Swoole\Task\Task;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Log;

/**
 * 【业务类型】审批提醒
 * Class BusinessApprovalRemind.
 */
class BusinessApprovalRemind extends Task
{
    public function __construct(protected int $entId, protected int $applyId) {}

    public function handle()
    {
        try {
            $applyInfo = app()->get(ApproveApplyService::class)->get(
                $this->applyId,
                ['card_id', 'user_id', 'node_id', 'status', 'approve_id', 'created_at', 'crud_id', 'link_id'],
                [
                    'card' => fn ($q) => $q->with(['frame']),
                ]
            )?->toArray();
            if (! $applyInfo) {
                return;
            }
            if ($applyInfo['crud_id']) {
                $process = app()->get(SystemCrudApproveProcessService::class)->get(['approve_id' => $applyInfo['approve_id'], 'uniqued' => $applyInfo['node_id']])?->toArray();
            } else {
                $process = app()->get(ApproveProcessService::class)->get(['approve_id' => $applyInfo['approve_id'], 'uniqued' => $applyInfo['node_id']])?->toArray();
            }
            if ($process && $process['types'] != 1) {
                return;
            }

            $approveUserService = app()->get(ApproveUserService::class);
            if ($applyInfo['crud_id']) {
                $approveName = app()->get(SystemCrudApproveService::class)->value($applyInfo['approve_id'], 'name');
            } else {
                $approveName = app()->get(ApproveService::class)->value($applyInfo['approve_id'], 'name');
            }
            $selfUserInfo['date_time']  = $applyInfo['created_at'];
            $selfUserInfo['apply_name'] = $applyInfo['card']['name'] ?? '';
            $selfUserInfo['frame_name'] = $applyInfo['card']['frame']['name'] ?? '';
            switch ($process['settype']) {
                case 1:// 指定成员
                    switch ($process['examine_mode']) {
                        case 1:
                        case 2:// 或签/会签都是同时发送给多人
                            $approveUser = $approveUserService->select([
                                'node_id'    => $applyInfo['node_id'],
                                'apply_id'   => $this->applyId,
                                'status'     => 0,
                                'approve_id' => $applyInfo['approve_id'],
                            ], ['*']);

                            $this->sendMessage($selfUserInfo, $approveUser, $approveName, true);
                            break;
                        case 3:// 依次审批
                            $approveUser = $approveUserService->get([
                                'node_id'    => $applyInfo['node_id'],
                                'apply_id'   => $this->applyId,
                                'status'     => 0,
                                'approve_id' => $applyInfo['approve_id'],
                            ], ['*'], [], ['sort' => 'asc']);
                            $this->sendMessage($selfUserInfo, $approveUser, $approveName);
                            break;
                    }
                    break;
                case 2:// 指定上级
                    $approveUser = $approveUserService->get([
                        'node_id'    => $applyInfo['node_id'],
                        'apply_id'   => $this->applyId,
                        'status'     => 0,
                        'approve_id' => $applyInfo['approve_id'],
                    ]);

                    $this->sendMessage($selfUserInfo, $approveUser, $approveName);
                    break;
                case 7:// 连续多部门
                    if ($process['director_order']) {
                        $order = ['sort' => 'desc'];
                    } else {
                        $order = ['sort' => 'asc'];
                    }
                    $approveUser = $approveUserService->get([
                        'node_id'    => $applyInfo['node_id'],
                        'apply_id'   => $this->applyId,
                        'status'     => 0,
                        'approve_id' => $applyInfo['approve_id'],
                    ], ['*'], [], $order);

                    $this->sendMessage($selfUserInfo, $approveUser, $approveName);
                    break;
                case 5:// 申请人自己
                    break;
                case 4:// 申请人自选
                    if ((int) $process['select_mode'] === 2) {
                        // 多选
                        switch ($process['examine_mode']) {
                            case 1:
                            case 2:// 或签/会签都是同时发送给多人
                                $approveUser = $approveUserService->select([
                                    'node_id'    => $applyInfo['node_id'],
                                    'apply_id'   => $this->applyId,
                                    'status'     => 0,
                                    'approve_id' => $applyInfo['approve_id'],
                                ], ['*']);

                                $this->sendMessage($selfUserInfo, $approveUser, $approveName, true);
                                break;
                            case 3:// 依次审批（按顺序依次审批）
                                $approveUser = $approveUserService->get([
                                    'node_id'    => $applyInfo['node_id'],
                                    'apply_id'   => $this->applyId,
                                    'status'     => 0,
                                    'approve_id' => $applyInfo['approve_id'],
                                ], ['*'], [], ['sort' => 'asc']);

                                $this->sendMessage($selfUserInfo, $approveUser, $approveName);
                                break;
                        }
                    } else {
                        // 单选
                        $approveUser = $approveUserService->get([
                            'node_id'    => $applyInfo['node_id'],
                            'apply_id'   => $this->applyId,
                            'status'     => 0,
                            'approve_id' => $applyInfo['approve_id'],
                        ]);

                        $this->sendMessage($selfUserInfo, $approveUser, $approveName);
                    }
                    break;
            }
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . ':' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTrace()]);
            Log::error('【业务类型】审批提醒发送失败:' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
        }
    }

    /**
     * 发送消息.
     * @param mixed $selfUserInfo
     * @param mixed $approveUser
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function sendMessage($selfUserInfo, $approveUser, string $approveName, bool $isList = false)
    {
        if (! $approveUser) {
            return;
        }

        $approveData = [];
        if (! $isList) {
            $approveData[] = $approveUser->toArray();
        } else {
            $approveData = $approveUser;
        }
        if (! $approveData) {
            return;
        }
        $userService = app()->get(AdminService::class);
        foreach ($approveData as $item) {
            $userInfo = $userService->get($item['user_id'])?->toArray();
            $task     = new MessageSendTask(
                entid: $this->entId,
                i: $this->entId,
                type: MessageType::BUSINESS_APPROVAL_TYPE,
                toUid: ['to_uid' => $userInfo['id'], 'phone' => $userInfo['phone'] ?? ''],
                params: [
                    '申请人部门' => $selfUserInfo['frame_name'] ?? '',
                    '申请人'   => $selfUserInfo['apply_name'] ?? '',
                    '申请时间'  => $selfUserInfo['date_time'],
                    '业务类型'  => $approveName,
                ],
                other: [
                    'id' => $this->applyId,
                ],
                linkId: $this->applyId,
                linkStatus: (int) $item['status'],
            );
            Task::deliver($task);
            Task::deliver(new StatusChangeTask(ApproveEnum::LINK_NOTICE, $item['status'], $this->entId, $this->applyId));
        }
    }
}
