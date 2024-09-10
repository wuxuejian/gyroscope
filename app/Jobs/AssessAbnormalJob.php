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

use App\Http\Service\Admin\AdminService;
use App\Http\Service\Assess\AssessPlanService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AssessAbnormalJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @var AssessPlanService|mixed
     */
    private mixed $service;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->service = app()->get(AssessPlanService::class);
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
            $userIds = app()->get(AdminService::class)->column(['status' => 1], 'id');
            if ($userIds) {
                foreach ([1, 2, 3, 4, 5] as $key) {
                    $this->service->abnormalTimer(1, $userIds, $key);
                }
            }
        } catch (\Exception $e) {
            Log::error(__CLASS__ . ':' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTrace()]);
        }
    }
}
