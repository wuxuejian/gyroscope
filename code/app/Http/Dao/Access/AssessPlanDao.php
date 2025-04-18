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

namespace App\Http\Dao\Access;

use App\Http\Dao\BaseDao;
use App\Http\Model\Assess\AssessPlan;
use App\Http\Service\Admin\AdminService;
use App\Http\Service\Assess\AssessPlanUserService;
use App\Http\Service\Frame\FrameService;
use crmeb\traits\dao\ListSearchTrait;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Model;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class AssessPlanDao extends BaseDao
{
    use ListSearchTrait;

    /**
     * 考核计划信息.
     * @param array $with
     * @param mixed $id
     * @return array
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getPlanInfo($id, $with = [])
    {
        return $this->search([])->when($with, function ($query) use ($with) {
            $query->with($with);
        })->where('id', $id)->get()->toArray();
    }

    /**
     * 考核计划中下级用户.
     * @param mixed $planId
     * @param mixed $uid
     * @return null|array|Model
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function getPlanUser($planId, $uid)
    {
        $subUser = app()->get(FrameService::class)->getLevelSubUser((int) $uid);
        if ($subUser) {
            $uids       = app()->get(AssessPlanUserService::class)->column(['planid' => $planId], 'test_uid') ?: [];
            $normalUids = app()->get(AdminService::class)->column(['status' => 1], 'id');
            $uids       = array_intersect($uids, $normalUids);
            foreach ($subUser as $k => $v) {
                if (! in_array($v['id'], $uids)) {
                    unset($subUser[$k]);
                }
            }
        }
        return $subUser;
    }

    /**
     * 设置模型.
     * @return mixed|string
     */
    protected function setModel()
    {
        return AssessPlan::class;
    }
}
