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
use App\Http\Service\Admin\AdminService;
use App\Http\Service\Company\CompanyService;
use App\Http\Service\News\NewsService;
use Carbon\Carbon;
use Hhxsv5\LaravelS\Swoole\Task\Task;
use Illuminate\Support\Facades\Log;

/**
 * 企业动态通知.
 */
class NewsRemind extends Task
{
    public function __construct(protected $entId, protected $newsId) {}

    public function handle()
    {
        try {
            $news = toArray(app()->get(NewsService::class)->get($this->newsId, ['id', 'title', 'push_type', 'push_time', 'status']));
            if (! $news) {
                return;
            }
            if ($news['status'] != 1) {
                return;
            }
            $userIds = app()->get(AdminService::class)->column(['status' => 1], 'id');
            if ($news['push_type']) {
                $delay = Carbon::make($news['push_time'])->timestamp > time() ? bcsub((string) Carbon::make($news['push_time'])->timestamp, (string) time()) : '';
            } else {
                $delay = '';
            }
            $task = new MessageSendTask(
                entid: $this->entId,
                i: $this->entId,
                type: NoticeEnum::COMPANY_NEWS,
                bathTo: $userIds,
                params: [
                    '企业名称' => app()->get(CompanyService::class)->value(1, 'enterprise_name') ?? '',
                    '文章标题' => $news['title'] ?? '',
                ],
                other: ['id' => $this->newsId],
                linkId: $this->newsId,
                linkStatus: $news['status'],
                setDelay: $delay
            );
            Task::deliver($task);
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . ':' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTrace()]);
        }
    }
}
