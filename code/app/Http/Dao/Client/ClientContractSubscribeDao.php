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

namespace App\Http\Dao\Client;

use App\Http\Dao\BaseDao;
use App\Http\Model\BaseModel;
use App\Http\Model\Client\ClientContractSubscribe;
use App\Http\Model\Client\Contract;
use crmeb\traits\dao\JoinSearchTrait;
use crmeb\traits\dao\ListSearchTrait;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\HigherOrderWhenProxy;

class ClientContractSubscribeDao extends BaseDao
{
    use ListSearchTrait;
    use JoinSearchTrait;

    /**
     * 设置模型.
     *
     * @return BaseModel
     * @throws BindingResolutionException
     */
    public function getModel(bool $need = false)
    {
        if ($need) {
            return $this->getJoinModel('eid', 'eid');
        }
        return parent::getModel($need);
    }

    /**
     * 关联合同查询.
     * @param mixed $where
     * @return BaseModel|HigherOrderWhenProxy|mixed
     * @throws BindingResolutionException
     */
    public function contractSearch($where, ?bool $authWhere = null)
    {
        return $this->getJoinModel('eid', 'eid')->where($this->getFiled('subscribe_status'), $where['subscribe_status'])
            ->where(function ($query) use ($where) {
                $uidField          = $this->getFiled('uid', $this->aliasB);
                $subscribeUidField = $this->getFiled('uid');
                if (is_array($where['uid'])) {
                    $query->whereIn($uidField, $where['uid']);
                } else {
                    $query->where($uidField, $where['uid']);
                }
                $query->where($subscribeUidField, $where['subscribe_uid']);
            })->distinct($this->getFiled('id'));
    }

    /**
     * @return string
     */
    protected function setModel()
    {
        return ClientContractSubscribe::class;
    }

    protected function setModelB(): string
    {
        return Contract::class;
    }
}
