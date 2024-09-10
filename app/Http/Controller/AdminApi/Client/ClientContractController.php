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

use App\Http\Contract\Client\ClientContractSubscribeInterface;
use App\Http\Controller\AdminApi\AuthController;
use App\Http\Requests\enterprise\client\ClientContractRequest;
use App\Http\Service\Client\ContractService;
use crmeb\traits\ResourceControllerTrait;
use Illuminate\Contracts\Container\BindingResolutionException;

/**
 * 客户合同
 * Class ClientContractController.
 */
class ClientContractController extends AuthController
{
    use ResourceControllerTrait;

    /**
     * ClientContractController constructor.
     */
    public function __construct(ContractService $services)
    {
        parent::__construct();
        $this->service = $services;
    }

    /**
     * 批量设置标签.
     * @param mixed $cid
     * @return mixed
     * @throws BindingResolutionException
     */
    public function setStatus($cid)
    {
        if (! $cid) {
            return $this->fail('common.empty.attrs');
        }
        [$status, $type] = $this->request->postMore([
            ['status', ''],
            ['types', 0],
        ], true);

        // 关注
        if ($type == 1) {
            app()->get(ClientContractSubscribeInterface::class)->subscribe($this->entId, uuid_to_uid($this->uuid), (int) $cid, (int) $status);
        } else {
            $this->service->resourceStatusUpdate($cid, $status, $type);
        }
        return $this->success(__('common.operation.succ'));
    }

    /**
     * @param mixed $eid
     * @throws \ReflectionException
     * @throws BindingResolutionException
     */
    public function getBillCate($eid)
    {
        if (! $eid) {
            return $this->fail('common.empty.attrs');
        }
        $data = $this->service->getBillCatePathById((int) $eid);
        return $this->success($data);
    }

    /**
     * 展示数据.
     */
    public function index(): mixed
    {
        $where = $this->request->getMore($this->getSearchField());
        $field = ['id', 'uid', 'eid', 'title', 'contract_no', 'price', 'received', 'surplus', 'start_date', 'end_date',
            'mark', 'renew', 'follow', 'up_follow', 'is_abnormal', 'sign_status', 'category_id', 'created_at', ];
        return $this->success($this->service->getList($where, $field));
    }

    /**
     * 详情.
     * @param mixed $id
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function info($id): mixed
    {
        if (! $id) {
            return $this->fail('common.empty.attrs');
        }
        return $this->success($this->service->getInfoAndCategory(['id' => $id]));
    }

    /**
     * 下拉列表.
     * @param mixed $eid
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function select($eid): mixed
    {
        if (! $eid) {
            return $this->fail('common.empty.attrs');
        }
        return $this->success($this->service->getSelectList((int) $eid, $this->uuid, $this->entId));
    }

    /**
     * 合同统计
     * @throws BindingResolutionException
     */
    public function num(): mixed
    {
        [$types] = $this->request->getMore([
            ['types', 1],
        ], true);
        return $this->success($this->service->getNum($this->uuid, $this->entId, (int) $types));
    }

    /**
     * 搜索字段.
     * @return array|\string[][]
     */
    protected function getSearchField(): array
    {
        return [
            ['pay_status', ''],
            ['abnormal', ''],
            ['types', ''],
            ['renew', ''],
            ['name', '', 'title'],
            ['eid', ''],
            ['uid', $this->uuid],
            ['sort', ''],
            ['time', '', 'time_data'],
            ['category_id', ''],
            ['start_date', ''],
            ['salesman_id', ''],
            ['date', ''],
            ['sign_status', ''],
            ['salesman_id', ''],
            ['time_field', 'time'],
            ['status', ''],
            ['follows', ''],
        ];
    }

    protected function getRequestClassName(): string
    {
        return ClientContractRequest::class;
    }

    /**
     * 提取字段.
     */
    protected function getRequestFields(): array
    {
        return [
            ['eid', 0],
            ['title', ''],
            ['price', ''],
            ['start_date', ''],
            ['end_date', ''],
            ['mark', ''],
            ['is_abnormal', 0],
            ['category_id', []],
            ['contract_no', ''],
            ['sign_status', 1],
        ];
    }
}
