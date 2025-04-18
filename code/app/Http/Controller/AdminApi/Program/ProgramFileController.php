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

use App\Http\Controller\AdminApi\AuthController;
use App\Http\Service\Attach\AttachService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Spatie\RouteAttributes\Attributes\Delete;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Prefix;
use Spatie\RouteAttributes\Attributes\Put;

/**
 * 项目附件
 * Class ProgramFileController.
 */
#[Prefix('ent/program_file')]
#[Middleware(['auth.admin', 'ent.auth', 'ent.log'])]
class ProgramFileController extends AuthController
{
    /**
     * ProgramFileController constructor.
     */
    public function __construct(AttachService $services)
    {
        parent::__construct();
        $this->service = $services;
    }

    /**
     * 显示列表.
     * @return mixed
     * @throws \ReflectionException
     * @throws BindingResolutionException
     */
    #[Get('index', '项目附件列表')]
    public function index()
    {
        $where = $this->request->getMore([
            ['program_id', '0', 'relation_id'],
            ['entid', 1],
            ['name', ''],
            ['time', ''],
        ]);
        $where['relation_type'] = [9];

        return $this->success($this->service->getRelationList($where, sort: 'id'));
    }

    /**
     * 删除文件.
     * @return mixed
     * @throws \ReflectionException
     * @throws BindingResolutionException
     */
    #[Delete('{id}', '项目附件删除')]
    public function delete($id)
    {
        if (! $id) {
            return $this->fail(__('common.empty.attrs'));
        }
        $this->service->delImg([$id], $this->entId);
        return $this->success('common.delete.succ');
    }

    /**
     * 重命名.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    #[Put('real_name/{id}', '项目附件重命名')]
    public function realName($id): mixed
    {
        if (! $id) {
            return $this->fail(__('common.empty.attrs'));
        }
        [$realName] = $this->request->postMore([
            ['real_name', ''],
        ], true);
        $this->service->setRealName((int) $id, $this->entId, $realName);
        return $this->success('common.operation.succ');
    }
}
