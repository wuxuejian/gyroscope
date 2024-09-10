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

namespace App\Http\Controller\AdminApi\Program;

use App\Constants\ProgramEnum\DynamicEnum;
use App\Http\Controller\AdminApi\AuthController;
use App\Http\Service\Program\ProgramDynamicService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Prefix;

/**
 * 项目动态.
 * Class ProgramDynamicController.
 */
#[Prefix('ent/program_dynamic')]
#[Middleware(['auth.admin', 'ent.auth', 'ent.log'])]
class ProgramDynamicController extends AuthController
{
    public function __construct(ProgramDynamicService $services)
    {
        parent::__construct();
        $this->service = $services;
    }

    /**
     * 项目动态列表.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    #[Get('program', '项目动态列表')]
    public function programDynamic(): mixed
    {
        $where = $this->request->getMore([
            ['uid', ''],
            ['time', ''],
            ['types', DynamicEnum::PROGRAM],
            ['relation_id', ''],
        ]);

        $field = ['id', 'uid', 'operator', 'title', 'describe', 'created_at'];
        return $this->success($this->service->getList($where, $field));
    }

    /**
     * 任务动态列表.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    #[Get('task', '任务动态列表')]
    public function taskDynamic(): mixed
    {
        $where = $this->request->getMore([
            ['uid', ''],
            ['time', ''],
            ['types', DynamicEnum::TASK],
            ['program_id', ''],
            ['relation_id', ''],
        ]);

        $field = ['id', 'uid', 'operator', 'relation_id', 'action_type', 'action_type', 'title', 'describe', 'created_at'];
        return $this->success($this->service->getList($where, $field));
    }
}
