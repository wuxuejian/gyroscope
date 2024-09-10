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

namespace App\Task\system;

use App\Http\Service\Config\DictDataService;
use Hhxsv5\LaravelS\Swoole\Task\Task;
use Illuminate\Support\Facades\Log;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class BatchUpdateDictTask extends Task
{
    /**
     * @var DictDataService|mixed
     */
    private mixed $service;

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(protected int $id)
    {
        $this->service = app()->get(DictDataService::class);
    }

    public function handle()
    {
        try {
            $info = $this->service->get($this->id, ['status', 'value', 'type_name']);
            if ($info && ! $info['status']) {
                $subIds = $this->getSubId($info['value'], $info['type_name']);
                $this->service->update(['id' => $subIds], ['status' => 0]);
            }
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . ':' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTrace()]);
        }
    }

    private function getSubId(array|int|string $value, string $typeName, array $param = [])
    {
        $sub = $this->service->column(['pid' => $value, 'type_name' => $typeName], ['id', 'value']);
        if (! $sub) {
            return $param;
        }
        $param = array_merge(array_column($sub, 'id'), $param);
        return $this->getSubId(array_column($sub, 'value'), $typeName, $param);
    }
}
