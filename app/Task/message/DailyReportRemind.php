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

use App\Constants\DailyEnum;
use App\Http\Service\Admin\AdminService;
use App\Http\Service\Frame\FrameService;
use crmeb\utils\MessageType;
use Hhxsv5\LaravelS\Swoole\Task\Task;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Log;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * 日报汇报提醒
 * Class DailyReportRemind.
 */
class DailyReportRemind extends Task
{
    public function __construct(protected $entId, protected $uuid, protected $id, protected $data, protected $isStore = false) {}

    /**
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function handle()
    {
        try {
            $service  = app()->get(FrameService::class);
            $userList = $service->getUserFrameAdminList($this->uuid);
            if ($userList) {
                $adminService = app()->get(AdminService::class);
                $userData     = $adminService->select(['id' => $userList], ['id', 'phone'])?->toArray();
                if (! $userData) {
                    return;
                }
                $userInfo = $adminService->get(['uid' => $this->uuid], with: ['frame'])?->toArray();
                $typeStr  = match ((int) $this->data['types']) {
                    1       => '周报',
                    2       => '月报',
                    3       => '汇报',
                    default => '日报',
                };
                $messageDate = [];
                foreach ($userData as $item) {
                    $messageDate[] = ['to_uid' => $item['id'], 'phone' => $item['phone']];
                }

                $task = new MessageSendTask(
                    entid: $this->entId,
                    i: $this->entId,
                    type: $this->isStore ? MessageType::DAILY_SHOW_REMIND_TYPE : MessageType::DAILY_UPDATE_REMIND_TYPE,
                    bathTo: $messageDate,
                    params: [
                        '汇报人'   => $userInfo['name'],
                        '汇报人部门' => $userInfo['frame']['name'] ?? '',
                        '汇报类型'  => $typeStr,
                        '工作内容'  => implode("\n", $this->data['finish']),
                        '工作计划'  => implode("\n", $this->data['plan']),
                        '备注内容'  => $this->data['mark'],
                    ],
                    other: ['id' => $this->id],
                    linkId: (int) $this->id,
                    linkStatus: 1
                );
                Task::deliver($task);
                Task::deliver(new StatusChangeTask(DailyEnum::LINK_NOTICE, DailyEnum::DAILY_SUB, $this->entId, $this->id));
                Task::deliver(new StatusChangeTask(DailyEnum::Not_Link_Notice, DailyEnum::DAILY_SUB, $this->entId, $this->id, uuid_to_uid($this->uuid, $this->entId), 'today'));
            }
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . ':' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTrace()]);
        }
    }
}
