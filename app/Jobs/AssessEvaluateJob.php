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

namespace App\Jobs;

use App\Constants\CacheEnum;
use App\Http\Service\Assess\UserAssessService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AssessEvaluateJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private int $limit = 20) {}

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
            $where   = ['status' => [0, 1, 2, 3], 'intact' => 1];
            $service = app()->get(UserAssessService::class);
            $entList = $service->getUserAssessEntListCache($where);
            foreach ($entList as $item) {
                $where['entid'] = $item['entid'];
                $count          = Cache::tags([CacheEnum::TAG_FRAME])->remember(UserAssessService::class, (int) sys_config('system_cache_ttl', 3600), fn () => $service->count($where));
                $sumPage        = ceil($count / $this->limit);
                for ($i = 1; $i <= $sumPage; ++$i) {
                    $service->timer($i, $this->limit, $where);
                }
            }
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . ':' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTrace()]);
        }
    }
}
