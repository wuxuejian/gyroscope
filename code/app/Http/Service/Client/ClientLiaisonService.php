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

use App\Http\Contract\Client\ClientLiaisonInterface;
use App\Http\Dao\Client\ClientLiaisonDao;
use App\Http\Service\BaseService;
use App\Http\Service\Frame\FrameService;
use crmeb\traits\service\ResourceServiceTrait;
use Illuminate\Contracts\Container\BindingResolutionException;

/**
 * 客户联系人.
 */
class ClientLiaisonService extends BaseService implements ClientLiaisonInterface
{
    use ResourceServiceTrait;

    public $dao;

    public function __construct(ClientLiaisonDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取列表.
     * @param array|string[] $field
     * @param null $sort
     */
    public function getList(array $where, array $field = ['id', 'uid', 'eid', 'name', 'job', 'gender', 'tel', 'mail', 'wechat', 'mark'], $sort = ['id'], array $with = []): array
    {
        $frameService = app()->get(FrameService::class);
        $uid          = uuid_to_uid($where['uid'], 1);
        $uids1        = $frameService->getLevelSub($uid);
        $uids2        = $frameService->getAllSubCardIds($where['uid'], 1);
        $uids         = array_merge($uids1, $uids2);
        $where['uid'] = array_merge($uids, [$uid]);
        return parent::getList($where, $field, $sort, $with);
    }

    /**
     * 保存.
     * @return mixed
     * @throws BindingResolutionException
     */
    public function resourceSave(array $data)
    {
        $uid             = uuid_to_uid($data['uid']);
        $data['creator'] = $data['uid'] = $uid;
        if (! $data['eid']) {
            throw $this->exception(__('common.empty.attr', ['attr' => '客户ID']));
        }
        if ($this->dao->exists(['eid' => $data['eid'], 'tel' => $data['tel'], 'name' => $data['name']])) {
            throw $this->exception(__('common.operation.exists'));
        }
        return $res = $this->dao->create($data);
    }

    /**
     * 修改.
     * @param mixed $id
     * @return int
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function resourceUpdate($id, array $data)
    {
        $uid     = uuid_to_uid($this->uuId(false));
        $uids    = app()->get(FrameService::class)->getLevelSub($uid);
        $infoUid = $this->dao->value($id, 'uid');
        if ($infoUid != $uid && ! in_array($infoUid, $uids)) {
            throw $this->exception('common.operation.noPermission');
        }
        unset($data['uid'], $data['creator'], $data['eid']);
        return $this->dao->update($id, $data);
    }

    /**
     * 删除.
     * @param mixed $id
     * @return int
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function resourceDelete($id, ?string $key = null)
    {
        $uid     = uuid_to_uid($this->uuId(false));
        $uids    = app()->get(FrameService::class)->getLevelSub($uid);
        $infoUid = $this->dao->value($id, 'uid');
        if ($infoUid != $uid && ! in_array($infoUid, $uids)) {
            throw $this->exception('common.operation.noPermission');
        }
        return $this->dao->delete($id);
    }

    /**
     * 详情.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getInfo(array $where, array $field = ['*'], array $with = []): array
    {
        $info = $this->dao->get($where, $field, $with);
        if (! $info) {
            throw $this->exception('客户数据不存在');
        }
        return $info->toArray();
    }
}
