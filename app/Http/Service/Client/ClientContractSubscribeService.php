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

namespace App\Http\Service\Client;

use App\Http\Contract\Client\ClientContractSubscribeInterface;
use App\Http\Dao\Client\ClientContractSubscribeDao;
use App\Http\Service\BaseService;
use crmeb\traits\service\ResourceServiceTrait;
use Illuminate\Contracts\Container\BindingResolutionException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * 客户合同关注.
 */
class ClientContractSubscribeService extends BaseService implements ClientContractSubscribeInterface
{
    use ResourceServiceTrait;

    public function __construct(ClientContractSubscribeDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 关注.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function subscribe(int $uid, int $cid, int $status): bool
    {
        $where = ['entid' => 1, 'uid' => $uid, 'cid' => $cid];
        $info  = $this->dao->get($where);
        if ($info) {
            $info->subscribe_status = $status;
            return $info->save();
        }
        $contract = app()->get(ContractService::class)->get(['id' => $cid]);
        if (! $contract) {
            throw $this->exception('合同信息获取异常');
        }
        $where['eid'] = $contract->eid;
        return (bool) $this->dao->create(array_merge($where, ['subscribe_status' => $status]));
    }

    /**
     * 客户关注状态
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getSubscribeStatusWithCid(int $uid, array $cids): array
    {
        return $this->dao->column(['uid' => $uid, 'cid' => $cids, 'subscribe_status' => 1], 'subscribe_status', 'cid');
    }

    /**
     * 合同关注状态
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getSubscribeStatusWithEid(int $uid, array $eids): array
    {
        return $this->dao->column(['uid' => $uid, 'eid' => $eids, 'subscribe_status' => 1], 'subscribe_status', 'cid');
    }

    /**
     * 获取用户关注合同数量.
     * @throws BindingResolutionException
     */
    public function contractCount(array|int $uid, int $subscribeUid): int
    {
        return $this->dao->contractSearch(['uid' => $uid, 'subscribe_uid' => $subscribeUid, 'subscribe_status' => 1])->count();
    }

    /**
     * 关注状态
     * @throws BindingResolutionException
     */
    public function getSubscribeStatus(int $uid, int $cid): int
    {
        return (int) $this->dao->value(['uid' => $uid, 'cid' => $cid, 'subscribe_status' => 1], 'subscribe_status');
    }
}
