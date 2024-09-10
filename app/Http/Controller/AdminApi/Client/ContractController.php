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

use App\Constants\CustomEnum\ContractEnum;
use App\Http\Contract\Client\ClientContractSubscribeInterface;
use App\Http\Controller\AdminApi\AuthController;
use App\Http\Service\Client\ContractService;
use App\Http\Service\Config\FormService;
use crmeb\traits\SearchTrait;
use Illuminate\Contracts\Container\BindingResolutionException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Prefix;
use Spatie\RouteAttributes\Attributes\Put;
use Spatie\RouteAttributes\Attributes\Resource;

/**
 * 合同管理
 * Class ContractController.
 */
#[Prefix('ent/client/contracts')]
#[Resource('/', false, except: ['show'], names: [
    'index'   => '合同列表',
    'create'  => '合同新增表单',
    'store'   => '新增合同',
    'edit'    => '合同修改表单',
    'update'  => '修改合同',
    'destroy' => '删除合同',
], parameters: ['' => 'id'])]
#[Middleware(['auth.admin', 'ent.auth', 'ent.log'])]
class ContractController extends AuthController
{
    use SearchTrait;

    public function __construct(ContractService $services)
    {
        parent::__construct();
        $this->service = $services;
    }

    /**
     * 列表数据.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function index(): mixed
    {
        $types       = (int) $this->request->get('types', 6);
        $scope_frame = $this->request->get('scope_frame', '');
        if (! $scope_frame) {
            switch ($types) {
                case 5:
                    $this->request->merge([
                        'scope_frame' => 'all',
                    ]);
                    break;
                case 6:
                    $this->request->merge([
                        'scope_frame' => 'self',
                    ]);
                    break;
            }
        }
        $this->withScopeFrame();
        $where = $this->request->getMore($this->service->searchField($types));
        return $this->success($this->service->getListByType($where, (bool) $this->request->get('is_export', 0)));
    }

    /**
     * 保存表单.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function create(FormService $service): mixed
    {
        return $this->success($service->getFormDataWithType(ContractEnum::CONTRACT));
    }

    /**
     * 保存.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function store(FormService $service): mixed
    {
        $data = $this->request->postMore($service->getRequestFields(ContractEnum::CONTRACT));
        $res  = $this->service->saveContract($data);
        return $this->success('common.insert.succ', ['id' => $res->id]);
    }

    /**
     * 修改.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function update($id, FormService $service): mixed
    {
        if (! $id) {
            return $this->fail($this->message['update']['emtpy']);
        }
        $data = $this->request->postMore($service->getRequestFields(ContractEnum::CONTRACT));
        $this->service->updateContract($data, (int) $id);
        return $this->success(__('common.update.succ'));
    }

    /**
     * 详情.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    #[Get('info/{id}', '合同详情')]
    public function info($id): mixed
    {
        if (! $id) {
            return $this->fail($this->message['update']['emtpy']);
        }

        return $this->success($this->service->getInfo((int) $id, $this->uuid));
    }

    /**
     * 修改表单.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function edit($id): mixed
    {
        if (! $id) {
            return $this->fail($this->message['update']['emtpy']);
        }

        return $this->success($this->service->getEditInfo((int) $id, $this->uuid));
    }

    /**
     * 删除.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function destroy($id): mixed
    {
        if (! $id) {
            return $this->fail('common.empty.attrs');
        }
        $this->service->deleteContract((int) $id, $this->uuid);
        return $this->success('common.delete.succ');
    }

    /**
     * 列表统计
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    #[Get('list_statistics', '合同列表统计')]
    public function listStatistics(): mixed
    {
        $types = $this->request->get('types', 5);
        return $this->success($this->service->getListStatistics((int) $types, $this->uuid));
    }

    /**
     * 修改关注状态
     * @throws BindingResolutionException
     */
    #[Post('subscribe/{id}/{status}', '修改合同关注状态')]
    public function subscribe($id, $status, ClientContractSubscribeInterface $clientSubscribeService): mixed
    {
        if (! $id) {
            return $this->fail('common.empty.attrs');
        }
        $clientSubscribeService->subscribe(uuid_to_uid($this->uuid), (int) $id, (int) $status);
        return $this->success(__('common.operation.succ'));
    }

    /**
     * 下拉列表.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    #[Get('select', '合同下拉列表')]
    public function select(): mixed
    {
        [$eid] = $this->request->getMore([
            ['data', []],
        ], true);

        if (! $eid) {
            return $this->fail('common.empty.attrs');
        }
        return $this->success($this->service->getSelectList((array) $eid, $this->uuid));
    }

    /**
     * 异常状态
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    #[Put('abnormal/{id}/{status}', '修改合同异常状态')]
    public function abnormal($id, $status): mixed
    {
        if (! $id) {
            return $this->fail('common.empty.attrs');
        }
        $this->service->abnormal((int) $id, (int) $status, $this->uuid);
        return $this->success(__('common.operation.succ'));
    }

    /**
     * 合同转移.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    #[Put('shift', '合同转移')]
    public function shift(): mixed
    {
        [$ids, $toUid, $invoice] = $this->request->postMore([
            ['data', []],
            ['to_uid', 0],
            ['invoice', 0],
        ], true);
        if (! $ids) {
            return $this->fail('common.empty.attrs');
        }
        $this->service->shift($ids, (int) $toUid, (int) $invoice);
        return $this->success(__('common.operation.succ'));
    }

    /**
     * 导入.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    #[Post('import', '合同导入')]
    public function import(): mixed
    {
        $this->withScopeFrame();
        [$data, $uids] = $this->request->postMore([
            ['data', []],
            ['uid', []],
        ], true);
        $this->service->batchImport((array) $data, $uids);
        return $this->success('common.operation.succ');
    }
}
