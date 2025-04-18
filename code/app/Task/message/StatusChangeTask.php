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

use App\Constants\NoticeEnum;
use App\Http\Service\Notice\NoticeRecordService;
use Hhxsv5\LaravelS\Swoole\Task\Task;
use Illuminate\Support\Facades\Log;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * 通知关联状态修改.
 */
class StatusChangeTask extends Task
{
    protected NoticeRecordService $service;

    /**
     * @param array $tempTypes 通知类型
     * @param int $newStatus 关联状态
     * @param int $entId 企业ID
     * @param array|int|string $linkId 关联ID
     * @param array|int|string $userId 用户ID
     * @param array|string $time 时间
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(protected array $tempTypes, protected int $newStatus, protected int $entId = 1, protected array|int|string $linkId = 0, protected array|int|string $userId = 0, protected array|string $time = '')
    {
        $this->service = app()->get(NoticeRecordService::class);
    }

    /**
     * @return mixed|void
     */
    public function handle()
    {
        try {
            $where['entid'] = $this->entId;
            if ($this->userId) {
                $where['to_uid'] = $this->userId;
            }
            $temps = [];
            foreach ($this->tempTypes as $tempType) {
                if (NoticeEnum::search($tempType)) {
                    $temps[] = $tempType;
                }
            }
            $where['template_type'] = $temps;
            if ($this->linkId) {
                if ($this->time) {
                    $where['time'] = $this->time;
                } else {
                    $where['link_id'] = $this->linkId;
                }
            }
            $this->service->update($where, ['link_status' => $this->newStatus]);
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . ':' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTrace()]);
        }
    }
}
