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

namespace App\Http\Controller\AdminApi\Client;

use App\Constants\CustomEnum\CustomEnum;
use App\Http\Contract\Client\ClientBillInterface;
use App\Http\Contract\Client\ClientSubscribeInterface;
use App\Http\Controller\AdminApi\AuthController;
use App\Http\Service\Client\ContractService;
use App\Http\Service\Client\CustomerService;
use App\Http\Service\Config\FormService;
use crmeb\traits\SearchTrait;
use crmeb\utils\Date;
use Illuminate\Contracts\Container\BindingResolutionException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Prefix;
use Spatie\RouteAttributes\Attributes\Resource;

/**
 * 客户管理
 * Class CustomerController.
 */
#[Prefix('ent/client/customer')]
#[Resource('/', false, except: ['show', 'index'], names: [
    'create'  => '客户新增表单',
    'store'   => '新增客户',
    'edit'    => '客户修改表单',
    'update'  => '修改客户',
    'destroy' => '删除客户',
], parameters: ['' => 'id'])]
#[Middleware(['auth.admin', 'ent.auth', 'ent.log'])]
class CustomerController extends AuthController
{
    use SearchTrait;

    public function __construct(CustomerService $services)
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
    #[Post('list', '客户列表')]
    public function index(): mixed
    {
        $types = (int) $this->request->get('types', 1);
        if ($types == 3) {
            $this->request->merge([
                'uid' => 0,
            ]);
        } else {
            $scope_frame = $this->request->get('scope_frame', '');
            if (! $scope_frame) {
                switch ($types) {
                    case 1:
                        $this->request->merge([
                            'scope_frame' => 'all',
                        ]);
                        break;
                    case 2:
                        $this->request->merge([
                            'scope_frame' => 'self',
                        ]);
                        break;
                }
            }
            $this->withScopeFrame();
        }
        $where = $this->request->postMore($this->service->searchField($types));
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
        return $this->success($service->getFormDataWithType(CustomEnum::CUSTOMER));
    }

    /**
     * 保存.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function store(FormService $service): mixed
    {
        $data            = $this->request->postMore($service->getRequestFields(CustomEnum::CUSTOMER));
        [$types, $force] = $this->request->postMore([
            ['types', 2],
            ['force', 0],
        ], true);
        $res = $this->service->saveCustomer($data, (int) $types, (int) $force);
        return $this->success('common.insert.succ', ['id' => $res->id]);
    }

    /**
     * 修改.
     * @param mixed $id
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function update($id, FormService $service): mixed
    {
        if (! $id) {
            return $this->fail($this->message['update']['empty']);
        }
        $data = $this->request->postMore($service->getRequestFields(CustomEnum::CUSTOMER));
        $this->service->updateCustomer($data, (int) $id, (int) $this->request->post('force', 0));
        return $this->success(__('common.update.succ'));
    }

    /**
     * 详情.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    #[Get('info/{id}', '客户详情')]
    public function info($id): mixed
    {
        if (! $id) {
            return $this->fail($this->message['update']['empty']);
        }

        return $this->success($this->service->getInfo((int) $id, $this->uuid));
    }

    /**
     * 修改表单.
     * @param mixed $id
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function edit($id): mixed
    {
        if (! $id) {
            return $this->fail($this->message['update']['empty']);
        }

        return $this->success($this->service->getEditInfo((int) $id, $this->uuid));
    }

    /**
     * 删除.
     * @param mixed $id
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
        $this->service->deleteCustomer((int) $id, $this->uuid);
        return $this->success('common.delete.succ');
    }

    /**
     * 列表统计
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    #[Get('list_statistics', '客户统计')]
    public function listStatistics(): mixed
    {
        $types = (int) $this->request->get('types', 1);
        if ($types == 3) {
            $this->request->merge([
                'uid' => 0,
            ]);
        } else {
            $scope_frame = $this->request->get('scope_frame', '');
            if (! $scope_frame) {
                switch ($types) {
                    case 1:
                        $this->request->merge([
                            'scope_frame' => 'all',
                        ]);
                        break;
                    case 2:
                        $this->request->merge([
                            'scope_frame' => 'self',
                        ]);
                        break;
                }
            }
            $this->withScopeFrame();
        }
        return $this->success($this->service->getListStatistics($types, auth('admin')->id(), $this->request->get('uid', [])));
    }

    /**
     * 下拉数据.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    #[Get('select', '客户下拉数据')]
    public function select(): mixed
    {
        return $this->success($this->service->getSelectList($this->uuid));
    }

    /**
     * 流失.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    #[Post('lost', '客户流失')]
    public function lost(): mixed
    {
        [$data] = $this->request->postMore([
            ['data', []],
        ], true);

        if (empty($data) || ! is_array($data)) {
            return $this->fail('common.empty.attrs');
        }

        $this->service->lost($data);
        return $this->success(__('common.operation.succ'));
    }

    /**
     * 退回.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    #[Post('return', '客户退回')]
    public function return(): mixed
    {
        [$data, $reason] = $this->request->postMore([
            ['data', []],
            ['reason', ''],
        ], true);

        if (empty($data) || ! is_array($data)) {
            return $this->fail('common.empty.attrs');
        }

        $this->service->returnHighSeas($data, $reason);
        return $this->success(__('common.operation.succ'));
    }

    /**
     * 修改关注状态
     * @throws BindingResolutionException
     */
    #[Post('subscribe/{id}/{status}', '修改关注状态')]
    public function subscribe($id, $status, ClientSubscribeInterface $clientSubscribeService): mixed
    {
        if (! $id) {
            return $this->fail('common.empty.attrs');
        }
        $clientSubscribeService->subscribe(uuid_to_uid($this->uuid), (int) $id, (int) $status);
        return $this->success(__('common.operation.succ'));
    }

    /**
     * 取消流失.
     * @param mixed $id
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    #[Post('cancel_lost/{id}', '取消流失')]
    public function cancelLost($id): mixed
    {
        if (! $id) {
            return $this->fail('common.empty.attrs');
        }
        $this->service->cancelLost((int) $id);
        return $this->success(__('common.operation.succ'));
    }

    /**
     * 业务员.
     */
    #[Get('salesman', '客户业务员')]
    public function salesman(): mixed
    {
        return $this->success($this->service->getSalesman($this->uuid));
    }

    /**
     * 领取.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    #[Post('claim', '客户领取')]
    public function claim(): mixed
    {
        [$data] = $this->request->postMore([
            ['data', []],
        ], true);

        if (empty($data) || ! is_array($data)) {
            return $this->fail('common.empty.attrs');
        }

        $this->service->claim($data, $this->uuid);
        return $this->success(__('common.operation.succ'));
    }

    /**
     * 批量设置标签.
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Post('label', '客户批量设置标签')]
    public function label(): mixed
    {
        [$data, $label] = $this->request->postMore([
            ['data', []],
            ['label', []],
        ], true);
        $this->service->label((array) $data, (array) $label);
        return $this->success(__('common.operation.succ'));
    }

    /**
     * 客户转移.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    #[Post('shift', '客户转移')]
    public function shift(): mixed
    {
        [$data, $toUid, $invoice, $contract] = $this->request->postMore([
            ['data', []],
            ['to_uid', 0],
            ['invoice', 0],
            ['contract', 0],
        ], true);
        if (! $data) {
            return $this->fail('common.empty.attrs');
        }
        $this->service->shift((array) $data, (int) $toUid, (int) $invoice, (int) $contract);
        return $this->success(__('common.operation.succ'));
    }

    /**
     * 业绩统计
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    #[Post('statistics', '客户业绩统计')]
    public function statistics(): mixed
    {
        $this->withScopeFrame();
        [$time, $userIds, $categoryIds] = $this->request->postMore([
            ['time', ''],
            ['uid', []],
            ['category_id', []],
        ], true);
        $data = $this->service->getStatistics($time, (array) $userIds, (array) $categoryIds);
        return $this->success($data);
    }

    /**
     * 合同类型分析统计.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    #[Post('contract_rank', '合同类型分析统计')]
    public function contractRank(ContractService $contractService): mixed
    {
        $this->withScopeFrame();
        [$time, $categoryIds, $categoryId, $userIds] = $this->request->postMore([
            ['time', ''],
            ['category_id', []],
            ['category', 0],
            ['uid', []],
        ], true);

        [$searchTime, $ratioTime] = Date::ringRatioTime($time);
        $data                     = $contractService->getCategoryRank($searchTime, (array) $userIds, (array) $categoryIds, (int) $categoryId);
        return $this->success($data);
    }

    /**
     * 业务员业绩排行榜.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    #[Post('ranking', '业务员业绩排行榜')]
    public function ranking(): mixed
    {
        $this->withScopeFrame();
        [$time, $categoryIds, $userIds] = $this->request->postMore([
            ['time', ''],
            ['category_id', []],
            ['uid', []],
        ], true);

        $data = $this->service->getRanking($time, (array) $userIds, (array) $categoryIds);
        return $this->success($data);
    }

    /**
     * 业绩统计
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    #[Post('trend_statistics', '业绩趋势统计')]
    public function trendStatistics(ClientBillInterface $billService): mixed
    {
        $this->withScopeFrame();
        [$time, $categoryIds, $userIds] = $this->request->postMore([
            ['time', ''],
            ['category_id', []],
            ['uid', []],
        ], true);
        $data = $billService->getTrendStatistics($time, (array) $userIds, (array) $categoryIds);
        return $this->success($data);
    }

    /**
     * 导入.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    #[Post('import', '客户导入')]
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
