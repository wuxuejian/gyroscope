<?php

namespace App\Jobs;

use App\Constants\Crud\CrudTriggerEnum;
use App\Http\Service\Crud\CrudModuleService;
use App\Http\Service\Crud\SystemCrudEventService;
use App\Http\Service\Crud\SystemCrudService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CrudEventRunGetDataJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(protected int $crudId, protected string $action, protected array $event, protected int $page)
    {
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {

            $crudService = app()->make(SystemCrudService::class);
            $crudInfo = $crudService->get($this->crudId)?->toArray();
            if (!$crudInfo) {
                return;
            }
            $modelService = app()->make(CrudModuleService::class);

            $join = $crudService->getJoinCrudData($this->crudId);
            $model = $modelService->model(crudId: $this->crudId)->setJoin($join)->withTrashed($this->action === CrudTriggerEnum::TRIGGER_DELETED);
            app()->make(SystemCrudEventService::class)->runGetData($modelService, $model, $crudInfo, $join, $this->crudId, $this->action, $this->event, [], 0, ['page' => $this->page]);
        } catch (\Throwable $e) {
            Log::error('实体触发器执行定获取数据报错：' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
        }
    }


}
