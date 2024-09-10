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

namespace App\Http\Service\Frame;

use App\Constants\CacheEnum;
use App\Http\Dao\Frame\FrameAssistDao;
use App\Http\Service\Admin\AdminService;
use App\Http\Service\BaseService;
use App\Http\Service\System\RolesService;
use App\Http\Service\System\RoleUserService;
use App\Task\frame\FrameCensusTask;
use Hhxsv5\LaravelS\Swoole\Task\Task;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Class FrameAssistService.
 * @method array getFrameUser(array $where, array $field = ['*']) 根据条件查询出相关组织架构用户信息
 * @method int getUserSingleCount(int $frameId, int $entid) 获取单个部门用户数量
 * @method int insert(array $data) 批量新增数据
 * @method bool updateFrameAdmin(int $frame_id, int $userId, int $superior_uid) 修改部门主管
 * @method bool deleteFrameAdmin(int $frame_id) 删除部门主管
 */
class FrameAssistService extends BaseService
{
    public function __construct(FrameAssistDao $entity)
    {
        $this->dao = $entity;
    }

    /**
     * 批量添加.
     * @param mixed $frameIds
     * @param mixed $superUid
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function batchAdd($frameIds, array $userIds, int $masterId, int $entId = 1, int $isAdmin = 0, $superUid = 0): bool
    {
        if (! is_array($frameIds)) {
            $frameIds = [$frameIds];
        }
        $data   = [];
        $update = null;
        foreach ($frameIds as $frameId) {
            foreach ($userIds as $userId) {
                $isMaster = $masterId == $frameId;
                if ($this->dao->exists(['frame_id' => $frameId, 'user_id' => $userId, 'entid' => $entId])) {
                    if ($isMaster) {
                        $update = [
                            'frame_id'   => $frameId,
                            'user_id'    => $userId,
                            'entid'      => $entId,
                            'is_mastart' => 1,
                        ];
                    }
                    continue;
                }
                $data[] = [
                    'entid'        => $entId,
                    'frame_id'     => $frameId,
                    'user_id'      => $userId,
                    'is_mastart'   => $isMaster ? 1 : 0,
                    'is_admin'     => $isMaster ? $isAdmin : 0,
                    'superior_uid' => $isAdmin && $isMaster ? $superUid : 0,
                    'created_at'   => now()->toDateTimeString(),
                ];
            }
        }
        if ($update) {
            // 修改用户部门主管
            $this->dao->update($update, ['is_admin' => $isAdmin]);
        }
        return $this->dao->insert($data);
    }

    /**
     * 设置用户所在部门.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function setUserFrame(array|int $frameIds, int $userId, int $masterId, bool $isAdmin = false, int $superUid = 0, array $manageFrameIds = []): bool
    {
        if (! is_array($frameIds)) {
            $frameIds = [$frameIds];
        }
        $this->dao->forceDelete(['not_frame_id' => $frameIds, 'user_id' => $userId]);
        $this->dao->restore(['frame_id' => $frameIds, 'user_id' => $userId]);
        $res = $this->transaction(function () use ($frameIds, $userId, $masterId, $isAdmin, $superUid, $manageFrameIds) {
            $isAdmin && $this->dao->update(['frame_id' => $manageFrameIds, 'is_admin' => 1], ['is_admin' => 0]);
            foreach ($frameIds as $frameId) {
                $this->dao->updateOrCreate(['frame_id' => $frameId, 'user_id' => $userId], [
                    'frame_id'     => $frameId,
                    'user_id'      => $userId,
                    'is_mastart'   => $frameId == $masterId ? 1 : 0,
                    'is_admin'     => in_array($frameId, $manageFrameIds) ? (int) $isAdmin : 0,
                    'superior_uid' => $superUid ?: 0,
                ]);
            }
            return true;
        });
        return $res && Task::deliver(new FrameCensusTask());
    }

    /**
     * 角色添加部门权限.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function addFrameRole(int $id, int $masterId, int $entId = 1): void
    {
        $frame = app()->get(FrameService::class)->get($masterId, ['id', 'role_id']);
        if (! $frame) {
            throw $this->exception('主部门不存在');
        }
        if ($frame->role_id) {
            $roles = app()->get(RoleUserService::class)->column(['user_id' => $id, 'entid' => $entId], 'role_id');
            if (in_array($frame->role_id, $roles)) {
                return;
            }
            app()->get(RolesService::class)->changeUserRole($entId, $id, array_unique(array_merge($roles, [$frame->role_id])));
        }
    }

    /**
     * 获取某个部门下的所有成员id.
     * @return array
     * @throws \ReflectionException
     * @throws BindingResolutionException
     */
    public function frameIdByUserId(array $frameIds, int $entid)
    {
        $userIds = [];
        foreach ($frameIds as $frameId) {
            $userIds = array_merge($userIds, $this->dao->getFrameUserIds($frameId, $entid));
        }
        return array_merge(array_unique($userIds));
    }

    /**
     * 查找当前部门下的所有人数.
     * @param mixed $status
     * @return int
     * @throws \ReflectionException
     * @throws BindingResolutionException
     */
    public function getUserCount(int $frameId, int $entid = 1, $status = 1)
    {
        $frameIds   = app()->get(FrameService::class)->setEntValue($entid)->scopeFrames($frameId);
        $frameIds[] = $frameId;
        $uids       = $this->dao->select(['entid' => $entid, 'frame_ids' => $frameIds], ['*'], [
            'card' => fn ($q) => $q->whereIn('type', [1, 2, 3])->select(['id', 'type']),
        ])->map(function ($item) {
            return ! is_null($item->card) ? $item['user_id'] : 0;
        })->filter()->all();
        return app()->get(AdminService::class)->count(['ids' => $uids, 'status' => $status]);
    }

    /**
     * 获取用户部门.
     * @param mixed $uuid
     * @param mixed $entid
     * @return null|array|Model
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function getUserFrames($uuid, $entid = 1)
    {
        return Cache::tags([CacheEnum::TAG_FRAME])->remember(
            'user_frames' . $entid . '_' . $uuid,
            (int) sys_config('system_cache_ttl', 3600),
            function () use ($uuid) {
                $userId = app()->get(AdminService::class)->value(['uid' => $uuid], 'id');
                return $this->dao->setDefaultSort('is_mastart')->select(['user_id' => $userId], ['*'], [
                    'frame' => fn ($q) => $q->select(['id', 'name']),
                ]);
            }
        );
    }

    /**
     * 获取部门主管
     * @throws BindingResolutionException
     */
    public function getFrameAdminUserId(int $frameId): int
    {
        return intval($this->dao->value(['is_admin' => 1, 'frame_id' => $frameId], 'user_id'));
    }

    /**
     * 获取用户直属下级用户ID.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getSubUid(int $uid): array
    {
        $frameIds = $this->dao->column(['user_id' => $uid, 'is_admin' => 1], 'frame_id');
        if ($frameIds) {
            $uid1   = $this->dao->column(['frame_id' => $frameIds, 'is_mastart' => 1, 'is_admin' => 0], 'user_id');
            $notUid = $this->dao->column(['frame_id' => $frameIds, 'is_mastart' => 1, 'is_admin' => 1, 'not_superior_uid' => $uid], 'user_id');
            $uid1   = array_diff($uid1, $notUid);
        } else {
            $uid1 = [];
        }
        $uid2 = $this->dao->column(['superior_uid' => $uid], 'user_id');
        return array_unique(array_merge($uid1, $uid2));
    }
}
