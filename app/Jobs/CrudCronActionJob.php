<?php

namespace App\Jobs;

use App\Http\Service\Crud\SystemCrudEventService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CrudCronActionJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(protected $item)
    {
    }


    public function handle()
    {
        try {
            app()->make(SystemCrudEventService::class)->timerAction($this->item);
        }catch (\Throwable $e){
            Log::error('实体触发器执行定时任务报错action：' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
        }
    }

}
