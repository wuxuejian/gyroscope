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

use App\Http\Contract\Client\ClientInterface;
use App\Http\Dao\Client\CustomerRecordDao;
use App\Http\Service\BaseService;
use Illuminate\Contracts\Container\BindingResolutionException;

/**
 * 客户记录.
 */
class CustomerRecordService extends BaseService implements ClientInterface
{
    /**
     * CustomerRecordService constructor.
     */
    public function __construct(CustomerRecordDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取列表.
     * @param array|string[] $field
     * @param null $sort
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getList(array $where, array $field = ['*'], $sort = 'id', array $with = ['creator']): array
    {
        [$page, $limit] = $this->getPageValue();
        $list           = $this->dao->getList($where, $field, $page, $limit, $sort, $with);
        $count          = $this->dao->count($where);
        return $this->listData($list, $count);
    }

    /**
     * 获取最新客户退回原因.
     * @return null|mixed
     * @throws BindingResolutionException
     */
    public function getLastReasonByEid(int $eid, int $type): ?string
    {
        return $this->dao->value(['eid' => $eid, 'type' => $type], 'reason') ?: '';
    }
}
