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

use App\Http\Contract\Client\ClientSubscribeInterface;
use App\Http\Dao\Client\ClientSubscribeDao;
use App\Http\Service\BaseService;
use crmeb\traits\service\ResourceServiceTrait;
use Illuminate\Contracts\Container\BindingResolutionException;

/**
 * 客户关注.
 */
class ClientSubscribeService extends BaseService implements ClientSubscribeInterface
{
    use ResourceServiceTrait;

    public function __construct(ClientSubscribeDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 关注.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function subscribe(int $uid, int $eid, int $status): bool
    {
        $status = $status == 1 ? 1 : 0;
        $where  = ['entid' => 1, 'uid' => $uid, 'eid' => $eid];
        $info   = $this->dao->get($where);
        if ($info) {
            $info->subscribe_status = $status;
            $res                    = $info->save();
        } else {
            $res = $this->dao->create(array_merge($where, ['subscribe_status' => $status]));
        }
        if (! $res) {
            throw $this->exception(__('common.operation.fail'));
        }
        return true;
    }

    /**
     * 客户关注状态
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getSubscribeStatusWithEid(int $uid, array $eids): array
    {
        return $this->dao->column(['uid' => $uid, 'eid' => $eids, 'subscribe_status' => 1], 'subscribe_status', 'eid');
    }

    /**
     * 关注状态
     * @throws BindingResolutionException
     */
    public function getSubscribeStatus(int $uid, int $eid): int
    {
        return (int) $this->dao->value(['uid' => $uid, 'eid' => $eid, 'subscribe_status' => 1], 'subscribe_status');
    }
}
