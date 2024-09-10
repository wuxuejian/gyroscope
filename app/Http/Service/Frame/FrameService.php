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
use App\Constants\RuleEnum;
use App\Http\Contract\Frame\FrameInterface;
use App\Http\Dao\Frame\FrameAssistDao;
use App\Http\Dao\Frame\FrameDao;
use App\Http\Model\BaseModel;
use App\Http\Service\Admin\AdminInfoService;
use App\Http\Service\Admin\AdminService;
use App\Http\Service\Assess\UserScopeService;
use App\Http\Service\BaseService;
use App\Http\Service\System\RolesService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Concerns\BuildsQueries;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * 组织架构.
 */
class FrameService extends BaseService implements FrameInterface
{
    protected array $roleList = [
        RuleEnum::DATA_ALL => [
            'label' => '全部',
            'name'  => '全部',
            'value' => 'all',
            'id'    => 'all',
            'pid'   => 0,
        ],
        RuleEnum::DATA_SELF => [
            'label' => '仅本人',
            'name'  => '仅本人',
            'value' => 'self',
            'id'    => 'self',
            'pid'   => 0,
        ],
        RuleEnum::DATA_CURRENT => [
            'label' => '本部门',
            'name'  => '本部门',
            'value' => 'dep',
            'id'    => 'dep',
            'pid'   => 0,
        ],
        RuleEnum::DATA_SUB => [
            'label' => '直属下级',
            'name'  => '直属下级',
            'value' => 'sub',
            'id'    => 'sub',
            'pid'   => 0,
        ],
    ];

    public function __construct(FrameDao $dao, protected FrameAssistDao $assistDao)
    {
        $this->dao = $dao;
    }

    /**
     * 分级排序列表.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getDepartmentTreeList(array $where, array $field = ['*'], null|array|string $sort = null, array $with = []): array
    {
        $key = md5(json_encode(compact('where', 'field', 'sort', 'with')));
        return Cache::tags([CacheEnum::TAG_FRAME])
            ->remember($key, (int) sys_config('system_cache_ttl', 3600), function () use ($where, $field, $with, $sort) {
                $list = $this->dao->getTierList(
                    $where,
                    $field,
                    $with,
                    $sort
                );
                foreach ($list as &$value) {
                    $value['isCheck'] = false;
                }
                return get_tree_children($list, 'children', 'value');
            });
    }

    /**
     * 获取组织架构详细信息.
     * @return array
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getInfo(int $id = 0)
    {
        $frameInfo = $id ? $this->dao->get($id, ['*'], [
            'super' => fn ($query) => $query->select(['admin.id', 'name', 'avatar']),
        ])?->toArray() : [];
        if ($id && ! $frameInfo) {
            throw $this->exception('组织架构不存在');
        }
        return [
            'frameInfo' => $frameInfo,
            'tree'      => $this->tree([], $id),
        ];
    }

    /**
     * 获取分类组合数据.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function tree(array $where = [], int $id = 0): array
    {
        $where['is_show'] = 1;
        $list             = $this->dao->getTierList($where, ['id as value', 'name as label', 'pid']);
        if ($id) {
            $totalIds   = $this->dao->getFrameTotalIds($id, 1);
            $totalIds[] = $id;
            foreach ($list as &$item) {
                if (in_array($item['value'], $totalIds)) {
                    $item['disabled'] = true;
                }
            }
        }

        return get_tree_children($list, 'children', 'value');
    }

    /**
     * 新建部门.
     * @throws BindingResolutionException
     */
    public function createDepartment(array $data): BaseModel|Model
    {
        if ($data['path']) {
            $data['pid'] = $data['path'][count($data['path']) - 1];
        }
        if (! $data['pid']) {
            throw $this->exception('请选择上级部门');
        }
        $data['level'] = $this->dao->value(['id' => $data['pid']], 'level') + 1;
        if ($this->dao->exists(['name' => $data['name'], 'entid' => $data['entid'], 'pid' => $data['pid']])) {
            throw $this->exception('已存在相同部门，请勿重复创建');
        }
        $res = $this->dao->create($data);
        if ($res) {
            Cache::tags([CacheEnum::TAG_FRAME, CacheEnum::TAG_APPROVE, CacheEnum::TAG_ROLE, CacheEnum::TAG_ASSESS])->flush();
            return $res;
        }
        throw $this->exception('创建部门失败');
    }

    /**
     * 部门详情.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getDepartmentInfo(array $where, array $field = ['*'], array $with = []): array
    {
        $info = toArray($this->dao->get($where, $field, $with));
        if (! $info) {
            throw $this->exception('未找到相关部门信息');
        }
        return $info;
    }

    /**
     * 部门修改.
     * @throws BindingResolutionException
     */
    public function updateDepartment(array $where, array $data): void
    {
        $info = $this->dao->get($where);
        if (! $info) {
            throw $this->exception('未找到相关部门信息');
        }
        if ($data['path']) {
            $data['pid'] = $data['path'][count($data['path']) - 1];
        }
        if (! $info->pid) {
            throw $this->exception('修改该部门需要修改企业名称！');
        }
        if (! $data['pid']) {
            throw $this->exception('请选择上级部门');
        }
        if ($data['pid'] == $where['id']) {
            throw $this->exception('本部门和上级部门不能相同');
        }
        $data['level'] = $this->dao->value(['id' => $data['pid']], 'level') + 1;
        $res           = $this->dao->update($where, $data);
        $res && Cache::tags([CacheEnum::TAG_FRAME, CacheEnum::TAG_APPROVE, CacheEnum::TAG_ROLE, CacheEnum::TAG_ASSESS])->flush();
    }

    /**
     * 部门删除.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function deleteDepartment(array $where): int
    {
        if (! $this->dao->exists($where)) {
            throw $this->exception('未找到相关部门信息');
        }
        if ($this->dao->exists(['pid' => $where['id'], 'entid' => $where['entid']])) {
            throw $this->exception('删除失败，存在下级部门');
        }
        $res = $this->dao->delete($where['id']);
        if ($res) {
            Cache::tags([CacheEnum::TAG_FRAME, CacheEnum::TAG_APPROVE, CacheEnum::TAG_ROLE, CacheEnum::TAG_ASSESS])->flush();
            Event::dispatch('frame.DeleteSuccess', [$where['entid'], $where['id']]);
            return $res;
        }
        throw $this->exception('创建部门失败');
    }

    /**
     * 获取组织架构和组织架构下的用户.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getTreeFrame(array $ids = [], bool $needUser = true): array
    {
        return Cache::tags([CacheEnum::TAG_FRAME])->remember(
            md5('frame_tree:' . $needUser . '_' . json_encode($ids)),
            (int) sys_config('system_cache_ttl', 3600),
            function () use ($ids, $needUser) {
                $where                = ['is_show' => 1, 'entid' => 1];
                $ids && $where['ids'] = $ids;
                $list                 = $this->dao->getTierList(
                    $where,
                    ['id', 'pid', 'entid', 'user_count', 'path', 'id as value', 'name as label', 'user_single_count'],
                    ['user']
                );
                $userList = [];
                if ($needUser) {
                    foreach ($list as $items) {
                        if ($items['user'] && is_array($items['user'])) {
                            $userList = array_merge($userList, $items['user']);
                        }
                    }
                }
                $userCount   = 0;
                $topFrameKey = -1;
                $topFrame    = $this->getTopFrame();
                $allUser     = [];

                $uids = app()->get(AdminService::class)->column(['status' => 1], 'id');
                foreach ($list as $key => &$item) {
                    $item['user']              = [];
                    $item['type']              = 0;
                    $item['user_single_count'] = 0;
                    $item['childFrame']        = $this->dao->count(['pid' => $item['id']]);
                    if ($needUser) {
                        $user = [];
                        foreach ($userList as $k => $value) {
                            $frameIds = $value['frame_ids'] ? array_column($value['frame_ids'], 'frame_id') : [];
                            unset($value['frame_ids']);
                            if (in_array($item['id'], $frameIds) && in_array($value['id'], $uids)) {
                                $user[]         = $value;
                                $value['pid']   = $item['id'];
                                $value['value'] = $value['id'] . '-' . $key . $k;
                                $value['label'] = $value['name'];
                                $value['type']  = 1;
                                $allUser[]      = $value;
                            }
                        }
                        $item['user']              = $user;
                        $item['user_single_count'] = count($user);
                    }
                    $item['isCheck']                               = false;
                    $item['id'] == $topFrame['id'] && $topFrameKey = $key;
                    $item['pid'] == $topFrame['id'] && $userCount += $item['user_count'];
                }
                $topFrameKey >= 0 && $list[$topFrameKey]['user_count'] = $userCount;
                $list                                                  = array_merge($list, $allUser);
                return get_tree_children($list, 'children', 'value');
            }
        );
    }

    /**
     * 获取部门树形列表.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getTree(string $uuid, int $entId, bool $withRole = true, bool $isScope = true): array
    {
        return Cache::tags([CacheEnum::TAG_FRAME, CacheEnum::TAG_ROLE])->remember(
            md5($uuid . $entId . (int) $withRole . (int) $isScope),
            (int) sys_config('system_cache_ttl', 3600),
            function () use ($uuid, $entId, $withRole, $isScope) {
                [$userFrames,$levels] = app()->get(RolesService::class)->getDataFrames($uuid, $entId, true);
                $where                = ['is_show' => 1, 'entid' => $entId];
                if ($isScope) {
                    $where['ids'] = $userFrames;
                }
                $field  = ['id', 'pid', 'entid', 'user_count', 'path', 'id as value', 'name as label', 'name', 'user_single_count'];
                $frames = toArray($this->dao->select($where, $field)->each(function (&$item) use ($withRole, $userFrames) {
                    if ($withRole) {
                        $item['disabled'] = in_array($item->id, $userFrames);
                    } else {
                        $item['disabled'] = false;
                    }
                }));
                if ($isScope) {
                    $list = [];
                    if ($levels) {
                        $count = count($frames) + count($levels) > 1;
                        if ($count) {
                            $list[] = $this->roleList[RuleEnum::DATA_ALL];
                        }
                        foreach ($levels as $level) {
                            if ($count && $level == RuleEnum::DATA_ALL) {
                                continue;
                            }
                            if (isset($this->roleList[$level])) {
                                $list[] = $this->roleList[$level];
                            }
                        }
                    } else {
                        $list[] = $this->roleList[RuleEnum::DATA_SELF];
                    }
                    $list = array_merge($list, $frames);
                    return get_tree_children($list);
                }
                return get_tree_children($frames);
            }
        );
    }

    /**
     * 获取部门人员树形列表.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function getUserTree(string $uuid, int $entId, bool $withRole = true, bool $leave = false): array
    {
        return Cache::tags(CacheEnum::TAG_FRAME)->remember(
            md5($uuid . $entId . (int) $withRole . '_' . (int) $leave),
            (int) sys_config('system_cache_ttl', 86400),
            function () use ($uuid, $entId, $withRole, $leave) {
                $where      = ['is_show' => 1, 'entid' => $entId];
                $userFrames = app()->get(RolesService::class)->getDataFrames($uuid, $entId);
                $field      = ['id', 'pid', 'entid', 'user_count', 'path', 'id as value', 'name as label', 'user_single_count'];
                $infoServce = app()->get(AdminInfoService::class);
                if ($leave) {
                    $ids = $infoServce->column(['type' => [1, 2, 3, 4]], 'id');
                } else {
                    $ids = $infoServce->column(['type' => [1, 2, 3]], 'id');
                }
                $list = $this->dao->select($where, $field, [
                    'users' => fn ($query) => $query->where(function ($query) use ($ids) {
                        $query->whereIn('admin.id', $ids);
                    })->with([
                        'user_card' => fn ($query) => $query->select(['id', 'type']),
                    ])->select(['admin.id', 'name', 'avatar', 'phone', 'job', 'uid']),
                ])?->toArray();
                $userList = [];
                foreach ($list as $value) {
                    $userList = array_merge($userList, $value['users']);
                }
                $userCount   = 0;
                $topFrameKey = -1;
                $topFrame    = $this->getTopFrame();
                $allUser     = [];
                $FrameAssist = app()->get(FrameAssistService::class);
                foreach ($list as $key => &$item) {
                    if ($withRole) {
                        $disabled = in_array($item['id'], $userFrames);
                    } else {
                        $disabled = false;
                    }
                    $item['type']              = 0;
                    $item['user_single_count'] = 0;
                    $item['disabled']          = $disabled;
                    $user                      = [];
                    foreach ($userList as $val) {
                        $frameIds = $FrameAssist->column(['user_id' => $val['id']], 'frame_id');
                        if (in_array($item['id'], $frameIds)) {
                            $user[]          = $val;
                            $val['value']    = $val['id'];
                            $val['id']       = $val['id'] . '-' . $item['id'];
                            $val['pid']      = $item['id'];
                            $val['label']    = $val['name'];
                            $val['type']     = 1;
                            $val['disabled'] = $disabled;
                            $allUser[]       = $val;
                        }
                    }
                    $item['user_single_count']                     = count($user);
                    $item['isCheck']                               = false;
                    $item['id'] == $topFrame['id'] && $topFrameKey = $key;
                    $item['pid'] == $topFrame['id'] && $userCount += $item['user_count'];
                    unset($item['users']);
                }
                $topFrameKey >= 0 && $list[$topFrameKey]['user_count'] = $userCount;
                $list                                                  = array_merge($list, $allUser);
                return get_tree_children($list);
            }
        );
    }

    /**
     * 统计企业人数.
     * @return true|void
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    final public function handleStatistics()
    {
        $this->pathArrange();
        $this->setFrameAdmin();
        $frames = $this->dao->select()?->toArray();
        if (! $frames) {
            return true;
        }
        $assistService = app()->get(FrameAssistService::class);
        $adminService  = app()->get(AdminInfoService::class);
        foreach ($frames as $item) {
            $this->dao->update($item['id'], [
                'user_single_count' => $assistService->count(['frame_id' => $item['id']]),
                'user_count'        => $this->getUserCount($item['id'], $assistService, $adminService),
            ]);
        }
        Cache::tags([CacheEnum::TAG_FRAME, CacheEnum::TAG_APPROVE])->flush();
    }

    /**
     * 获取组织人员.
     * @param mixed $frameId
     * @param mixed $entId
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getFrameUser($frameId, $entId = 1)
    {
        return Cache::tags([CacheEnum::TAG_FRAME])->remember(md5($frameId . 'FrameUser' . $entId), (int) sys_config('system_cache_ttl', 3600), function () use ($frameId, $entId) {
            $uids = app()->get(FrameAssistService::class)->column(['entid' => $entId, 'frame_id' => $frameId, 'is_mastart' => 1], 'user_id');
            return app()->get(AdminService::class)->select(['id' => $uids], ['id', 'name', 'avatar', 'uid'], [
                'isAdmin' => function ($query) {
                    $query->select(['is_admin', 'superior_uid', 'user_id']);
                },
            ])?->toArray() ?: [];
        });
    }

    /**
     * 获取所有上级主管
     * @param mixed $field
     * @param mixed $uid
     * @param mixed $entid
     * @param mixed $users
     * @param mixed $contain
     * @return array|mixed
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getAllUpUser($uid, $entid, $users = [], $contain = false, $field = ['*'])
    {
        if (strlen($uid) == 32) {
            $uid = $this->uuidToUid($uid, $entid);
        }
        $assistService = app()->get(FrameAssistService::class);
        $adminService  = app()->get(AdminService::class);
        if ($contain) {
            $user = $adminService->get($uid, $field)?->toArray();
            if ($user) {
                $users[] = $user;
            }
        }
        $assist = $assistService->get(['is_mastart' => 1, 'user_id' => $uid], ['is_admin', 'frame_id', 'superior_uid']);
        if (empty($assist)) {
            return $users;
        }
        if ($assist->superior_uid) {
            $user = $adminService->get($assist->superior_uid, $field)?->toArray() ?: [];
        } else {
            $user_id = $assistService->value(['is_mastart' => 1, 'is_admin' => 1, 'frame_id' => $assist->frame_id], 'user_id');
            $user    = $adminService->get($user_id, $field)?->toArray() ?: [];
        }
        if ($user && ! in_array($user['id'], array_column($users, 'id'))) {
            $users[] = $user;

            return $this->getAllUpUser($user['uid'], $entid, $users);
        }
        return $users;
    }

    /**
     * 获取上级主管ID.
     * @param mixed $uid
     * @param mixed $entid
     * @return mixed
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getUpUid($uid, $entid)
    {
        return Cache::tags([CacheEnum::TAG_FRAME])->remember($uid . 'UpUid' . $entid, (int) sys_config('system_cache_ttl', 3600), function () use ($uid) {
            if (strlen((string) $uid) == 32) {
                $uid = $this->uuidToUid($uid);
            }
            $assistService = app()->get(FrameAssistService::class);
            $assist        = $assistService->get(['is_mastart' => 1, 'user_id' => $uid], ['is_admin', 'frame_id', 'superior_uid']);
            if ($assist) {
                $assist = $assist->toArray();
            } else {
                return 0;
            }
            if ($assist['superior_uid']) {
                $uid = $assist['superior_uid'];
            } else {
                $assist = $assistService->get(['is_mastart' => 1, 'is_admin' => 1, 'frame_id' => $assist['frame_id']], ['user_id']);
                if ($assist) {
                    $uid = $assist['user_id'];
                } else {
                    $uid = 0;
                }
            }

            return $uid;
        });
    }

    /**
     * TODO 获取管理范围组织架构ID.
     *
     * @param mixed $type
     * @param mixed $field
     * @param mixed $uuid
     * @param mixed $entid
     *
     * @return null|array|Model
     * @throws \ReflectionException
     * @throws BindingResolutionException
     */
    public function getAllSubFrames($uuid, $entid, $type = true, bool $containManageFrame = false, $field = ['id', 'name'])
    {
        return Cache::tags([CacheEnum::TAG_FRAME])->remember(md5($uuid . 'AllSubFrames' . $entid . '_' . $type . '_' . $containManageFrame . '_' . json_encode($field)), (int) sys_config('system_cache_ttl', 3600), function () use ($uuid, $entid, $type, $containManageFrame, $field) {
            $userId        = app()->get(AdminService::class)->value(['uid' => $uuid], 'id');
            $frame         = app()->get(UserScopeService::class)->column(['uid' => $userId, 'entid' => $entid, 'types' => 0], 'link_id');
            $frames        = $this->scopeFrames($frame) ?? [];
            $assistService = app()->get(FrameAssistService::class);
            if ($containManageFrame) {
                $frame[] = $assistService->value(['user_id' => $userId, 'is_mastart' => 1, 'is_admin' => 1], 'frame_id');
            }
            $frames = array_unique(array_merge($frames, $frame));
            if ($type) {
                return $frames;
            }

            return toArray($this->dao->select(['ids' => $frames, 'is_show' => 1], $field));
        });
    }

    /**
     * TODO 获取管理范围下人员ID.
     *
     * @param mixed $types
     * @param mixed $uuid
     * @param mixed $entid
     * @param mixed $frames
     * @return array
     * @throws \ReflectionException
     * @throws BindingResolutionException
     */
    public function getAllSubCardIds($uuid, $entid, $frames = [], $types = false)
    {
        return Cache::tags([CacheEnum::TAG_FRAME])->remember(
            md5($uuid . 'AllSubCardIds' . $entid . json_encode($frames) . (int) $types),
            (int) sys_config('system_cache_ttl', 3600),
            function () use ($uuid, $entid, $frames, $types) {
                if (! $frames) {
                    $cardId = $this->uuidToCardid($uuid, $entid);
                    $frames = app()->get(UserScopeService::class)->dao->column(['uid' => $cardId, 'entid' => $entid, 'types' => 0], 'link_id');
                }
                return $this->scopeUser($frames, types: $types);
            }
        );
    }

    /**
     * 获取当前用户所在部门的主管user_id.
     * @return array|int|mixed
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getUserFrameAdminList(string $uuid, bool $isList = true)
    {
        $userId      = app()->get(AdminService::class)->value(['uid' => $uuid], 'id');
        $assist      = $this->assistDao->get(['user_id' => $userId, 'is_mastart' => 1])?->toArray();
        $superiorUid = $assist['superior_uid'];
        return $isList ? [$superiorUid] : $superiorUid;
    }

    /**
     * 获取部门主管信息.
     * @return array|string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getFrameAdmin(int $frameId, int $entId, array|string $field = ['*'])
    {
        $uid = app()->get(FrameAssistService::class)->value(['entid' => $entId, 'frame_id' => $frameId, 'is_admin' => 1], 'user_id');
        if (! $uid) {
            return [];
        }
        return app()->get(AdminService::class)->get($uid, $field);
    }

    /**
     * 获取部门无限级下级人员ID.
     * @param int $userId
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function getFrameSubUids($userId = 0): array
    {
        if (! $userId) {
            return [];
        }
        $frameAssist = app()->get(FrameAssistService::class);
        $frameId     = $frameAssist->value(['user_id' => $userId, 'is_mastart' => 1], 'frame_id');
        $frameIds    = $this->dao->column(['path' => $frameId, 'is_show' => 1], 'id');
        $frameIds[]  = $frameId;
        return array_unique($frameAssist->column(['frame_id' => $frameIds], 'user_id'));
    }

    /**
     * 获取无限下级部门.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function scopeFrames(array|int $frameId, bool $isTree = false, array|string $field = 'id'): array
    {
        $frameIds = array_merge(is_array($frameId) ? $frameId : [$frameId], $this->getMoreFrame($frameId));
        if (is_array($field)) {
            if (! $field) {
                $field = ['id', 'pid', 'name', 'path', 'user_count as count'];
            }
            $frames = $this->dao->select(['id' => $frameIds, 'is_show' => 1], $field)?->toArray();
            if ($isTree) {
                return get_tree_children($frames);
            }
            return $frames;
        }
        return $frameIds;
    }

    /**
     * 获取无限下级人员.
     * @param bool $isNormal 是否
     * @param bool $withScope 是否为管理范围部门
     * @param bool $withMaster 是否为主部门
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function scopeUser(array|int $frameId, bool $isNormal = true, bool $types = false, bool $withScope = true, bool $withMaster = false): array
    {
        $frameId            = is_array($frameId) ? $frameId : [$frameId];
        $where['frame_ids'] = $withScope ? $this->scopeFrames($frameId) : $frameId;
        if ($withMaster) {
            $where['is_mastart'] = 1;
        }
        $assistService = app()->get(FrameAssistService::class);
        if ($isNormal) {
            $where['user_id'] = app()->get(AdminService::class)->column(['status' => 1], 'id');
        }
        $userIds = $assistService->column($where, 'user_id') ?? [];
        if ($types) {
            return app()->get(AdminService::class)->column(['id' => $userIds], 'card_id');
        }
        return $userIds;
    }

    /**
     * 获取人员主部门.
     * @param mixed $userId
     * @return null|BaseModel|BuildsQueries|mixed|Model|object
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function getMasterFrame($userId, array $field = ['*'])
    {
        $frameId = app()->get(FrameAssistService::class)->value(['user_id' => $userId, 'is_mastart' => 1], 'frame_id');
        return $this->dao->get($frameId, $field);
    }

    /**
     * 判断用户是否在部门内（无限级）.
     *
     * @return bool
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function checkFrameUser(int $uid, array $frame = [])
    {
        $frames    = $this->scopeFrames($frame);
        $userFrame = app()->get(FrameAssistService::class)->value(['user_id' => $uid, 'is_mastart' => 1], 'frame_id');
        return in_array($userFrame, $frames);
    }

    /**
     * 获取用户指定级别上级用户ID.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    final public function getLevelSuper(int $uid, int $level = 1, bool $all = false, array $uids = [], int $count = 1): array|int
    {
        $assistInfo = $this->assistDao->get(['user_id' => $uid, 'is_mastart' => 1])?->toArray();
        if (! $assistInfo) {
            return $all ? $uids : 0;
        }
        $upUid = 0;
        if ($assistInfo['superior_uid']) {
            if (! in_array($assistInfo['superior_uid'], $uids)) {
                $uids[] = $assistInfo['superior_uid'];
                $upUid  = $assistInfo['superior_uid'];
            } else {
                return $all ? $uids : $upUid;
            }
        } else {
            $frameAdmin = $this->dao->value($assistInfo['frame_id'], 'user_id');
            if ($frameAdmin && ! in_array($frameAdmin, $uids)) {
                $uids[] = $frameAdmin;
                $upUid  = $frameAdmin;
            } else {
                return $all ? $uids : $upUid;
            }
        }
        if (! $level) {
            return $this->getLevelSuper($upUid, $level, $all, $uids);
        }
        if ($count == $level) {
            return $all ? $uids : $upUid;
        }
        ++$count;
        return $this->getLevelSuper($upUid, $level, $all, $uids, $count);
    }

    /**
     * 获取用户指定级别下级用户ID.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    final public function getLevelSub(array|int $uid, int $level = 1, bool $all = false, array $uids = [], int $count = 1, bool $isNormal = true): array
    {
        $userIds = is_array($uid) ? $uid : [$uid];
        $subUid  = [];
        foreach ($userIds as $userId) {
            $frameIds = $this->assistDao->column(['user_id' => $userId, 'is_admin' => 1], 'frame_id');
            if ($frameIds) {
                $uid1   = $this->assistDao->column(['frame_id' => $frameIds, 'is_mastart' => 1, 'is_admin' => 0], 'user_id');
                $notUid = $this->assistDao->column(['frame_id' => $frameIds, 'is_mastart' => 1, 'is_admin' => 1, 'not_superior_uid' => $userId], 'user_id');
                $uid1   = array_diff($uid1, $notUid);
            } else {
                $uid1 = [];
            }
            $uid2   = $this->assistDao->column(['superior_uid' => $userId], 'user_id');
            $subUid = array_unique(array_merge($subUid, array_merge($uid1, $uid2)));
        }
        if (! $subUid) {
            return $all ? $uids : [];
        }
        if ($isNormal) {
            $subUid = app()->get(AdminService::class)->column(['id' => $subUid], 'id');
        }
        $uids = array_unique(array_merge($uids, $subUid));
        if ($count == $level) {
            return $all ? $uids : $subUid;
        }
        ++$count;
        return $this->getLevelSub($subUid, $level, $all, $uids, $count);
    }

    /**
     * 获取用户指定级别上级用户.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    final public function getLevelSuperUser(int $uid, int $level = 1, bool $all = false, array $field = ['id', 'uid', 'name', 'avatar']): array
    {
        $userIds = $this->getLevelSuper($uid, $level, $all);
        if ($all) {
            $users = app()->get(AdminService::class)->select(['id' => $userIds], $field)?->toArray() ?: [];
            // 提取排序字段
            $sortOrder = array_column(array_map(function ($item) use ($userIds) {
                return [
                    'name'  => $item['name'],
                    'order' => array_search($item['id'], $userIds),
                ];
            }, $users), 'order');
            // 对排序数组排序
            array_multisort($sortOrder, SORT_ASC, $users);
            return $users;
        }
        return app()->get(AdminService::class)->get($userIds, $field)?->toArray() ?: [];
    }

    /**
     * 获取用户指定级别下级用户.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    final public function getLevelSubUser(int $uid, int $level = 1, bool $all = false, array $field = ['id', 'uid', 'name', 'avatar']): array
    {
        $userIds = $this->getLevelSub($uid, $level, $all);
        if ($all) {
            $users = app()->get(AdminService::class)->select(['id' => $userIds], $field)?->toArray() ?: [];
            // 提取排序字段
            $sortOrder = array_column(array_map(function ($item) use ($userIds) {
                return [
                    'name'  => $item['name'],
                    'order' => array_search($item['id'], $userIds),
                ];
            }, $users), 'order');
            // 对排序数组排序
            array_multisort($sortOrder, SORT_ASC, $users);
            return $users;
        }
        return app()->get(AdminService::class)->select(['id' => $userIds], $field)?->toArray() ?: [];
    }

    /**
     * 获取当前负责部门.
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getManagerFrameId(int $entId, int $uid): int
    {
        return (int) app()->get(FrameAssistService::class)->value(['entid' => $entId, 'is_mastart' => 1, 'is_admin' => 1, 'user_id' => $uid], 'frame_id');
    }

    /**
     * 根据用户获取主部门数据.
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getFrameIdByUserId(int $userId): int
    {
        $frameId = (int) app()->get(FrameAssistService::class)->value(['is_mastart' => 1, 'user_id' => $userId], 'frame_id');
        return $frameId ?: 1;
    }

    /**
     * 根据用户获取主部门数据.
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getFrameByUserIds(array $userIds): array
    {
        $assistUser = app()->get(FrameAssistService::class)->column(['entid' => 1, 'is_mastart' => 1, 'user_id' => $userIds], 'frame_id', 'user_id');
        if (! $assistUser) {
            return [];
        }

        $frameMap = [];
        foreach ($assistUser as $key => $item) {
            $frameMap[$item][] = $key;
        }
        $frames = $this->select(['ids' => array_unique(array_values($assistUser))], ['id', 'name']);
        if (! $frames) {
            return [];
        }
        $frames = $frames->toArray();
        foreach ($frames as &$frame) {
            $frame['uid'] = $frameMap[$frame['id']];
        }
        return $frames;
    }

    /**
     * 获取顶级部门.
     */
    public function getTopFrame(int $entId = 0, array $field = ['id', 'entid', 'pid', 'name']): array
    {
        return Cache::tags([CacheEnum::TAG_FRAME])->remember('top_frame:' . $entId . '_' . json_encode($field), (int) sys_config('system_cache_ttl', 3600), function () use ($entId, $field) {
            $frame = $this->get(['entid' => $entId ?: 1, 'pid' => 0], $field);
            if (! $frame) {
                throw $this->exception('部门信息获取失败');
            }
            return $frame->toArray();
        });
    }

    /**
     * 获取顶级部门id.
     */
    public function topFrameId()
    {
        return $this->dao->value(['pid' => 0], 'id') ?: 0;
    }

    /**
     * 检测部门/管理范围权限.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function hasManageScopeAuth(int $entId, string $uuid): bool
    {
        $frameAssistService = app()->get(FrameAssistService::class);
        $adminCount         = $frameAssistService->count(['entid' => $entId, 'user_id' => $this->uuidToUid($uuid, $entId), 'is_admin' => 1]);
        if ($adminCount) {
            return true;
        }

        $scopeCount = app()->get(UserScopeService::class)->count(['uid' => $this->uuidToCardid($uuid, $entId), 'entid' => $entId, 'types' => 0]);
        if ($scopeCount) {
            return true;
        }
        return false;
    }

    /**
     * TODO 获取下级用户.
     * @param int|string $uid 用户ID/UUID
     * @param int $entId 企业ID
     * @param bool $scope 是否包含管理范围
     * @param bool $isAll 是否无限级部门
     * @param bool $isNormal 是否正常员工
     * @param bool $isCard 是否返回用户名片
     * @param bool $withAdmin 是否包含下级主管
     * @param bool $withSelf 是否包含自己
     * @return array|mixed
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function subUserInfo(int|string $uid, int $entId, bool $scope = true, bool $isAll = true, bool $isNormal = true, bool $isCard = false, bool $withAdmin = false, bool $withSelf = false, array|string $field = 'id')
    {
        $key = $uid . $entId . (int) $scope . (int) $isAll . (is_array($field) ? json_encode($field) : $field) . (int) $withAdmin . (int) $isCard . (int) $withSelf;
        return Cache::tags([CacheEnum::TAG_FRAME])->remember(
            md5($key),
            (int) sys_config('system_cache_ttl', 3600),
            function () use ($uid, $entId, $scope, $isAll, $isNormal, $field, $withAdmin, $withSelf) {
                if (strlen((string) $uid) == 32) {
                    $uid = $this->uuidToUid($uid, $entId);
                }
                $frameId            = $this->subFrameInfo($uid, $entId, $scope, $isAll);
                $assistService      = app()->get(FrameAssistService::class);
                $where['frame_ids'] = $frameId;
                $userIds            = $assistService->column($where, 'user_id');

                // 是否包含自己
                if (! $withSelf) {
                    $userIds = array_diff($userIds, [$uid]);
                } else {
                    $userIds[] = $uid;
                }

                // 是否包含下级主管
                if ($withAdmin) {
                    $subAdmin = $assistService->column(['is_mastart' => 1, 'is_admin' => 1, 'superior_uid' => $uid], 'user_id') ?? [];
                    $userIds  = array_unique(array_merge($userIds, $subAdmin));
                }

                // 是否为正常用户
                $adminService = app()->get(AdminService::class);
                if ($isNormal) {
                    $normalIds = $adminService->column(['status' => 1], 'id');
                    $userIds   = array_unique(array_intersect($userIds, $normalIds));
                }

                $userIds = array_values($userIds);
                if (! $userIds) {
                    return [];
                }
                if (is_string($field)) {
                    return $userIds;
                }
                return $adminService->select(['id' => $userIds], $field)?->toArray();
            }
        );
    }

    /**
     * TODO 获取下级部门信息.
     * @param int|string $uid 用户ID/UUID
     * @param int $entId 企业ID
     * @param bool $scope 是否包含管理范围
     * @param bool $isAll 是否无限级部门
     * @param array|string $field 部门信息内容
     * @return array
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function subFrameInfo(int|string $uid, int $entId, bool $scope = true, bool $isAll = true, array|string $field = 'id')
    {
        $key = $uid . $entId . (int) $scope . (int) $isAll . (is_array($field) ? json_encode($field) : $field);
        return Cache::tags([CacheEnum::TAG_FRAME])->remember(
            md5($key),
            (int) sys_config('system_cache_ttl', 3600),
            function () use ($uid, $entId, $scope, $isAll, $field) {
                if (strlen((string) $uid) == 32) {
                    $uid = $this->uuidToUid($uid, $entId);
                }
                // 当前部门
                $frameId = $this->getManagerFrameId($entId, $uid);
                // 管理范围部门
                if ($scope) {
                    $cardId       = $this->uidToCardid($uid, $entId);
                    $scopeFrameId = $this->scopeFrameId($cardId, $entId, $isAll);
                    if ($frameId) {
                        $scopeFrameId[] = $frameId;
                    }
                    $frameId = $scopeFrameId;
                }
                if (! $frameId) {
                    return [];
                }
                if (is_string($field)) {
                    return $this->dao->column(['ids' => $frameId], $field);
                }
                return toArray($this->dao->select(['ids' => $frameId], $field));
            }
        );
    }

    /**
     * TODO 根据uuid获取企业用户ID.
     * @return int
     * @throws BindingResolutionException
     */
    public function uuidToUid(string $uuid, int $entid = 1)
    {
        $user_id = app()->get(AdminService::class)->value(['uid' => $uuid], 'id') ?: 0;
        if (! $user_id) {
            throw $this->exception('无效的用户信息');
        }
        return $user_id;
    }

    /**
     * TODO 根据uuid获取企业用户名片ID.
     * @param mixed $uuid
     * @param mixed $entid
     * @return int|mixed
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function uuidToCardid($uuid, $entid)
    {
        $user_id = app()->get(AdminService::class)->value(['uid' => $uuid], 'id') ?: 0;
        if (! $user_id) {
            throw $this->exception('无效的用户信息');
        }
        return $user_id;
    }

    /**
     * TODO 根据uid获取企业用户名片ID.
     * @return int
     */
    public function uidToCardid(int $uid, int $entId = 1)
    {
        return $uid;
    }

    /**
     * 获取默认部门ID.
     * @throws BindingResolutionException
     */
    public function getDefaultFrame(): int
    {
        return (int) $this->dao->value(['pid' => 0], 'id');
    }

    /**
     * 创建企业初级架构.
     *
     * @throws BindingResolutionException
     */
    public function entFrameInit(int $entid, string $enterpriseName): int
    {
        if ($id = $this->dao->value(['entid' => $entid, 'name' => $enterpriseName], 'id')) {
            return $id;
        }
        $entInfo = $this->dao->create(['entid' => $entid, 'name' => $enterpriseName, 'user_count' => 1, 'user_single_count' => 1]);
        if (! $entInfo) {
            throw $this->exception('创建企业初始架构失败');
        }
        return $entInfo->id;
    }

    /**
     * 分级排序列表.
     *
     * @param array|string[] $field
     * @param null|string $sort
     *
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getList(array $where, array $field = ['*'], $sort = null, array $with = []): array
    {
        return $this->dao->getTierList($where, ['pid', 'path', 'id as value', 'name as label', 'user_count', 'user_single_count'], sort: 'level');
    }

    /**
     * 整理组织架构path.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function pathArrange(): void
    {
        $frames = $this->dao->column([], ['id', 'pid', 'user_single_count', 'level']);
        foreach ($frames as $value) {
            $save['path'] = $this->getPids($value['id']);
            $this->dao->update($value['id'], $save);
        }
    }

    /**
     * 获取部门人员ID.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function getIdsByFrameIds(int $uid, array|int $frameIds = []): array
    {
        if (empty($frameIds)) {
            $frameIds[] = $this->getFrameIdByUserId($uid);
        } else {
            $frameIds = array_intersect(
                array_unique(array_merge($frameIds, $this->scopeFrames($frameIds))),
                app()->get(RolesService::class)->getDataFrames(app()->get(AdminService::class)->value($uid, 'uid'))
            );
        }
        return array_unique(array_intersect(
            $this->assistDao->column(['frame_id' => $frameIds], 'user_id'),
            app()->get(AdminService::class)->column(['status' => 1], 'id')
        ));
    }

    /**
     * 根据用户获取主部门层级.
     * @return array|int[]
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function getFrameIdsByUserId(int $userId): array
    {
        $frameId = (int) app()->get(FrameAssistService::class)->value(['is_mastart' => 1, 'user_id' => $userId], 'frame_id');
        if (! $frameId) {
            return [];
        }

        $frame = $this->dao->get($frameId, ['path']);
        if (! $frame || empty($frame->path)) {
            return [$frameId];
        }

        return array_filter(array_merge($frame->path, [$frameId]));
    }

    /**
     * 设置部门管理员.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    protected function setFrameAdmin(int $level = 0): bool
    {
        $frames = $this->dao->select(['level' => $level])?->toArray();
        if (! $frames) {
            return true;
        }
        $adminService = app()->get(AdminService::class);
        foreach ($frames as $item) {
            $adminId = $this->assistDao->value(['is_admin' => 1, 'frame_id' => $item['id']], 'user_id');
            if ($adminId) {
                $adminId = $adminService->value($adminId, 'status') == 1 ? $adminId : 0;
            }
            if ($adminId) {
                $this->dao->update($item['id'], ['user_id' => $adminId]);
            } elseif (! $level) {
                $this->dao->update($item['id'], ['user_id' => $adminService->value(['is_admin' => 1], 'id')]);
            } else {
                $this->dao->update($item['id'], ['user_id' => $this->dao->value($item['pid'], 'user_id')]);
            }
        }
        ++$level;
        return $this->setFrameAdmin($level);
    }

    /**
     * 查找当前部门下的所有人数.
     * @param mixed $assistService
     * @param mixed $adminService
     * @return int
     * @throws \ReflectionException
     * @throws BindingResolutionException
     */
    protected function getUserCount(int $frameId, $assistService, $adminService)
    {
        $frameIds = $this->scopeFrames($frameId);
        $uids     = $assistService->column(['frame_ids' => $frameIds], 'user_id');
        return $adminService->count(['id' => $uids, 'type' => [1, 2, 3]]);
    }

    /**
     * 根据部门获取组织架构无限下级部门.
     * @param mixed $frameId
     * @param mixed $frames
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    protected function getMoreFrame($frameId, $frames = []): array
    {
        if (! is_array($frameId)) {
            $frameId = [$frameId];
        }
        $frameIds = $this->dao->column(['pid' => $frameId, 'is_show' => 1], 'id');
        $frames   = array_unique(array_merge($frames, $frameIds));
        if ($frameIds) {
            return $this->getMoreFrame($frameIds, $frames);
        }
        return $frames;
    }

    /**
     * 获取上级部门.
     * @param mixed $id
     * @param mixed $ids
     * @throws BindingResolutionException
     */
    protected function getPids($id, $ids = []): array
    {
        $pid = $this->dao->value($id, 'pid') ?: '';
        if ($pid) {
            $ids[] = $pid;
            return $this->getPids($pid, $ids);
        }
        krsort($ids);
        return array_filter($ids);
    }

    /**
     * @param mixed $cardId
     * @param mixed $entId
     * @return array
     * @throws \ReflectionException
     * @throws BindingResolutionException
     */
    private function scopeFrameId($cardId, $entId, bool $isAll = false)
    {
        $frameId = app()->get(FrameScopeService::class)->column(['uid' => $cardId, 'entid' => $entId, 'types' => 0], 'link_id') ?? [];
        if (! $frameId) {
            return [];
        }
        if ($isAll) {
            $frameId = array_unique(array_merge($frameId, $this->scopeFrames($frameId)));
        }
        return $frameId;
    }
}
