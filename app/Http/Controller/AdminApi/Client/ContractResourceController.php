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

namespace App\Http\Controller\AdminApi\Client;

use App\Http\Contract\Client\ContractResourceInterface;
use App\Http\Controller\AdminApi\AuthController;
use App\Http\Middleware\AuthAdmin;
use App\Http\Middleware\AuthEnterprise;
use App\Http\Requests\enterprise\client\ContractResourceRequest;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Validation\ValidationException;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Prefix;
use Spatie\RouteAttributes\Attributes\Resource;

/**
 * 合同附件
 * Class ContractResourceController.
 */
#[Prefix('ent/client/resources')]
#[Resource('/', false, except: ['create', 'show', 'edit'], names: [
    'index'   => '合同附件列表',
    'store'   => '保存合同附件',
    'update'  => '修改合同附件',
    'destroy' => '删除合同附件',
], parameters: ['' => 'id'])]
#[Middleware(['auth.admin', 'ent.auth', 'ent.log'])]
class ContractResourceController extends AuthController
{
    public function __construct(ContractResourceInterface $service)
    {
        parent::__construct();
        $this->service = $service;
        $this->middleware([AuthAdmin::class, AuthEnterprise::class]);
    }

    /**
     * 展示数据.
     */
    public function index(): mixed
    {
        $where = $this->request->getMore([
            ['cid', ''],
            ['entid', 1],
        ]);

        $field = ['id', 'eid', 'cid', 'uid', 'content', 'created_at', 'updated_at'];
        return $this->success($this->service->getList($where, $field));
    }

    /**
     * 添加.
     * @throws BindingResolutionException
     * @throws ValidationException
     */
    public function store(ContractResourceRequest $request): mixed
    {
        $request->scene(__FUNCTION__)->check();
        $data = $request->postMore($this->getRequestFields());
        $this->service->save($data);
        return $this->success('common.insert.succ');
    }

    /**
     * 详情.
     */
    public function info($id): mixed
    {
        if (! $id) {
            return $this->fail('common.empty.attrs');
        }
        return $this->success($this->service->getInfo(['id' => $id, 'entid' => $this->entId]));
    }

    /**
     * 修改.
     * @throws BindingResolutionException
     * @throws ValidationException
     * @throws \ReflectionException
     */
    public function update($id, ContractResourceRequest $request): mixed
    {
        if (! $id) {
            return $this->fail('common.empty.attrs');
        }
        $request->scene(__FUNCTION__)->check();
        $data = $request->postMore($this->getRequestFields());
        $this->service->update((int) $id, $data);
        return $this->success('common.update.succ');
    }

    /**
     * 删除.
     */
    public function destroy($id): mixed
    {
        if (! $id) {
            return $this->fail('common.empty.attrs');
        }
        $this->service->delete($id);
        return $this->success('common.delete.succ');
    }

    /**
     * 提取字段.
     */
    protected function getRequestFields(): array
    {
        return [
            ['eid', 0],
            ['cid', 0],
            ['content', ''],
            ['attach_ids', []],
            ['entid', 1],
        ];
    }
}
