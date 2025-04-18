<?php

declare(strict_types=1);
/**
 *  +----------------------------------------------------------------------
 *  | 陀螺匠 [ 赋能开发者，助力企业发展 ]
 *  +----------------------------------------------------------------------
 *  | Copyright (c) 2016~2025 https://www.tuoluojiang.com All rights reserved.
 *  +----------------------------------------------------------------------
 *  | Licensed 陀螺匠并不是自由软件，未经许可不能去掉陀螺匠相关版权
 *  +----------------------------------------------------------------------
 *  | Author: 陀螺匠 Team <admin@tuoluojiang.com>
 *  +----------------------------------------------------------------------
 */

namespace App\Http\Controller\AdminApi\Notepad;

use App\Http\Controller\AdminApi\AuthController;
use App\Http\Requests\user\UserMemorialRequest;
use App\Http\Service\Notepad\NotepadService;
use crmeb\interfaces\ResourceControllerInterface;
use crmeb\traits\ResourceControllerTrait;
use Illuminate\Contracts\Container\BindingResolutionException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Prefix;
use Spatie\RouteAttributes\Attributes\Resource;

/**
 * 备忘录列表控制器
 * Class NotepadController.
 */
#[Prefix('ent/user/memorial')]
#[Resource('/', false, except: ['show', 'create', 'edit'], names: [
    'index'   => '获取备忘录列表接口',
    'store'   => '保存备忘录接口',
    'edit'    => '获取备忘录信息接口',
    'update'  => '修改备忘录接口',
    'destroy' => '删除备忘录接口',
], parameters: ['' => 'id'])]
#[Middleware(['auth.admin', 'ent.auth', 'ent.log'])]
class NotepadController extends AuthController implements ResourceControllerInterface
{
    use ResourceControllerTrait;

    public function __construct(NotepadService $services)
    {
        parent::__construct();
        $this->service = $services;
    }

    /**
     * 最新分组列表.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    #[Get('group', '最新分组列表')]
    public function group(): mixed
    {
        $where = $this->request->getMore([
            ['pid', ''],
            ['title', ''],
            ['uid', $this->uuid],
        ]);
        return $this->success($this->service->groupList($where, false));
    }

    /**
     * 添加.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function store(): mixed
    {
        $data = $this->request()->postMore($this->getRequestFields());
        $this->service->saveData($data);
        return $this->success(__('common.insert.succ'), tips: 0);
    }

    /**
     * 修改数据.
     * @param mixed $id
     * @return mixed
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function update($id)
    {
        $this->service->resourceUpdate($id, $this->request->postMore($this->getRequestFields()));
        return $this->success('保存成功');
    }

    /**
     * 搜索字段.
     * @return array|string[][]
     */
    protected function getSearchField(): array
    {
        return [
            ['pid', 0],
            ['title', ''],
            ['uid', $this->uuid],
        ];
    }

    /**
     * 设置.
     */
    protected function getRequestClassName(): string
    {
        return UserMemorialRequest::class;
    }

    /**
     * 提取字段.
     */
    protected function getRequestFields(): array
    {
        return [
            ['pid', 0],
            ['title', ''],
            ['content', ''],
            ['uid', $this->uuid],
        ];
    }
}
