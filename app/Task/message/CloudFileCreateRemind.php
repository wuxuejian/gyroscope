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

use App\Constants\CloudEnum;
use App\Http\Service\Admin\AdminService;
use App\Http\Service\Cloud\CloudAuthService;
use App\Http\Service\Cloud\CloudFileService;
use crmeb\utils\MessageType;
use Hhxsv5\LaravelS\Swoole\Task\Task;
use Illuminate\Support\Facades\Log;

/**
 * 文件创建提醒
 * Class CloudFileCreateRemind.
 */
class CloudFileCreateRemind extends Task
{
    public function __construct(protected $entId, protected $uid, protected $foladInfo, protected $pid) {}

    public function handle()
    {
        try {
            $uids         = app()->get(CloudAuthService::class)->column(['folder_id' => $this->pid, 'not_uid' => $this->uid], 'uid');
            $bathToUser   = [];
            $adminService = app()->get(AdminService::class);
            $userInfo     = $adminService->select(['uid' => $uids], ['*'])?->toArray();
            foreach ($userInfo as $item) {
                $bathToUser[] = ['to_uid' => $item['id'], 'phone' => $item['phone']];
            }
            if (! $bathToUser) {
                return;
            }
            $folderPid = app()->get(CloudFileService::class)->get($this->pid, ['name', 'uid', 'created_at']);
            if (! $folderPid) {
                return;
            }
            $task = new MessageSendTask(
                entid: $this->entId,
                i: $this->entId,
                type: MessageType::CLOUD_FILE_CREATE_TYPE,
                bathTo: $bathToUser,
                params: [
                    '创建人'  => $adminService->value($this->uid, 'name'),
                    '创建时间' => date('Y-m-d H:i:s'),
                    '文件名称' => $this->foladInfo['name'],
                    '空间名称' => $folderPid['name'],
                ],
                linkId: $this->foladInfo['id'],
                linkStatus: CloudEnum::FILE_READ
            );
            Task::deliver($task);
            Task::deliver(new StatusChangeTask(CloudEnum::FILE_NOTICE, CloudEnum::FILE_READ, $this->entId, $this->foladInfo['id']));
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . ':' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTrace()]);
        }
    }
}
