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

namespace App\Http\Service\Attendance;

use App\Constants\AttendanceGroupEnum;
use App\Constants\CacheEnum;
use App\Http\Contract\Attendance\AttendanceGroupInterface;
use App\Http\Dao\Attendance\AttendanceGroupDao;
use App\Http\Dao\Attendance\AttendanceGroupMemberDao;
use App\Http\Dao\Attendance\AttendanceGroupShiftDao;
use App\Http\Dao\Attendance\AttendanceWhitelistDao;
use App\Http\Model\BaseModel;
use App\Http\Service\Admin\AdminService;
use App\Http\Service\BaseService;
use App\Http\Service\Frame\FrameAssistService;
use App\Http\Service\Frame\FrameService;
use App\Http\Service\System\RolesService;
use crmeb\traits\service\ResourceServiceTrait;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Concerns\BuildsQueries;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use function Aws\filter;

/**
 * 考勤组记录
 * Class AttendanceGroupService.
 */
class AttendanceGroupService extends BaseService implements AttendanceGroupInterface
{
    use ResourceServiceTrait;

    protected AttendanceGroupMemberDao $memberDao;

    protected AttendanceWhitelistDao $whitelistDao;

    protected AttendanceGroupShiftDao $shiftDao;

    public function __construct(AttendanceGroupDao $dao, AttendanceGroupMemberDao $memberDao, AttendanceGroupShiftDao $shiftDao, AttendanceWhitelistDao $whitelistDao)
    {
        $this->dao          = $dao;
        $this->memberDao    = $memberDao;
        $this->shiftDao     = $shiftDao;
        $this->whitelistDao = $whitelistDao;
    }

    /**
     * 列表.
     * @param string $sort
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getList(array $where, array $field = ['*'], $sort = 'created_at', array $with = ['shifts']): array
    {
        [$page, $limit] = $this->getPageValue();
        $list           = $this->dao->getList($where, $field, $page, $limit, $sort, $with, function ($list) {
            foreach ($list as $item) {
                if ($item->type) {
                    $frameIds      = $this->memberDao->column(['group_id' => $item->id, 'type' => AttendanceGroupEnum::FRAME], 'member');
                    $item->members = app()->get(FrameService::class)->select(['ids' => $frameIds], ['id', 'name']);
                } else {
                    $item->load('members');
                }
            }
        });
        $count = $this->dao->count($where);
        return $this->listData($list, $count);
    }

    /**
     * 设置白名单.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function setWhitelist(array $list): bool
    {
        $res = $this->transaction(function () use ($list) {
            $this->setWhiteByType($list['members'], AttendanceGroupEnum::WHITELIST_MEMBER);
            $this->setWhiteByType($list['admins'], AttendanceGroupEnum::WHITELIST_ADMIN);

            app()->get(AttendanceArrangeService::class)->clearFutureArrangeByMembers($list['members'], true);
            app()->get(AttendanceStatisticsService::class)->clearWhitelist($list['members']);
            return true;
        });
        return $res && Cache::tags([CacheEnum::TAG_ATTENDANCE])->flush();
    }

    /**
     * 获取白名单.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getWhitelist(): array
    {
        return [
            'members' => $this->whitelistDao->select(['type' => AttendanceGroupEnum::WHITELIST_MEMBER], ['uid', 'uid as id'], ['card']),
            'admins'  => $this->whitelistDao->select(['type' => AttendanceGroupEnum::WHITELIST_ADMIN], ['uid', 'uid as id'], ['card']),
        ];
    }

    /**
     * @throws \ReflectionException
     * @throws BindingResolutionException
     */
    public function getShiftIds(int $id): array
    {
        return $this->shiftDao->column(['group_id' => $id], 'shift_id');
    }

    /**
     * 保存基本信息.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function saveGroup(array $data): mixed
    {
        $this->checkGroup((int) $data['type'], $data['name'], $data['members'], 0, $data['other_filters']);
        $userId = $this->uuId(false);
        return $this->transaction(function () use ($data, $userId) {
            $res = $this->dao->create(['name' => $data['name'], 'uid' => $userId, 'type' => $data['type']]);
            if (! $res) {
                throw $this->exception(__('common.insert.fail'));
            }

            if ($data['other_filters']) {
                $this->otherFilters($data, $res->id);
            }

            $this->handleMember(array_unique($data['members']), $data['type'] == 0 ? AttendanceGroupEnum::MEMBER : AttendanceGroupEnum::FRAME, $res->id);
            [$filterGroups, $userIds] = $this->filterGroupMember((int) $data['type'], $data['members'], $res->id);
            $this->handleMember(array_unique(array_merge($data['filters'], $userIds)), AttendanceGroupEnum::FILTER, $res->id);
            $this->handleMember(array_unique($data['admins']), AttendanceGroupEnum::ADMIN, $res->id);

            foreach (array_unique($data['shifts']) as $shift) {
                $this->shiftDao->create(['group_id' => $res->id, 'shift_id' => $shift]);
            }

            return $res;
        });
    }

    /**
     * 核对考勤组.
     * @param int $type
     * @param string $name
     * @param array $members
     * @param int $id
     * @param array $filterMember
     * @return void
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function checkGroup(int $type = 0, string $name = '', array $members = [], int $id = 0, array $filterMember = []): void
    {
        if ($id && ! $this->dao->exists($id)) {
            throw $this->exception('操作失败，记录不存在');
        }

        if ($name) {
            $where = ['name_like' => $name];
            if ($id) {
                $where['not_id'] = $id;
            }

            $this->dao->exists($where) && throw $this->exception('考勤组名称重复');
        }

        // 考勤重复检测
        if ($members) {
            $type ? $this->checkFrameRepeat($members, $id, $filterMember) : $this->checkMemberRepeat($members, $id, $filterMember);
        }
    }

    /**
     * 修改基本信息.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function updateStepOne(int $id, array $data): mixed
    {
        $this->checkGroup((int) $data['type'], $data['name'], $data['members'], $id, $data['other_filters']);
        $info = $this->dao->get(['id' => $id], ['*']);
        if (! $info) {
            throw $this->exception('操作失败，记录不存在');
        }

        return $this->transaction(function () use ($id, $data, $info) {
            $res = $this->dao->update($id, ['name' => $data['name'], 'type' => $data['type']]);
            if (! $res) {
                throw $this->exception(__('common.insert.fail'));
            }

            if ($info->type != $data['type']) {
                app()->get(AttendanceArrangeService::class)->clearFutureArrangeByGroupId($id, true);
                $this->memberDao->forceDelete(['group_id' => $id, 'type' => $info->type == 0 ? AttendanceGroupEnum::MEMBER : AttendanceGroupEnum::FRAME]);
            }
            $this->handleShift($id, array_unique($data['shifts']));
            if ($data['other_filters']) {
                $this->otherFilters($data, $id);
            }
            $this->handleMember(array_unique($data['members']), $data['type'] == 0 ? AttendanceGroupEnum::MEMBER : AttendanceGroupEnum::FRAME, $id);
            $this->handleMember(array_unique($data['filters']), AttendanceGroupEnum::FILTER, $id);
            $this->handleMember(array_unique($data['admins']), AttendanceGroupEnum::ADMIN, $id);

            return $res;
        });
    }

    /**
     * 修改考勤地点.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function updateStepTwo(int $id, array $data): int
    {
        $this->checkGroup(id: $id);
        return $this->dao->update($id, $data);
    }

    /**
     * 修改考勤规则.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function updateStepThree(int $id, array $data): int
    {
        $this->checkGroup(id: $id);
        foreach ($data['repair_type'] as $item) {
            if (! in_array($item, AttendanceGroupEnum::CARD_REPLACEMENT_TYPE)) {
                throw $this->exception('请选择正确的补卡类型');
            }
        }
        return $this->dao->update($id, $data);
    }

    /**
     * 修改考勤周期
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function updateStepFour(int $id, array $data): int
    {
        $this->checkGroup(id: $id);
        return $this->dao->update($id, $data);
    }

    /**
     * 详情.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getInfo(int $id): array
    {
        $info = $this->dao->get(['id' => $id], ['*'], ['shifts', 'admins', 'filters']);
        if (! $info) {
            throw $this->exception('操作失败，记录不存在');
        }

        // 考勤部门
        if ($info->type) {
            $frameIds        = $this->memberDao->column(['group_id' => $id, 'type' => AttendanceGroupEnum::FRAME], 'member');
            $info['members'] = app()->get(FrameService::class)->select(['ids' => $frameIds], ['id', 'name']);
        } else {
            $info->load(['members']);
        }

        return toArray($info);
    }

    /**
     * 获取已考勤人员.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function filterGroupMember(int $type, array $members, int $excludeGroupId = 0): array
    {
        $userIds = $currenMemberIds = [];

        // 根据考勤部门获取考勤人员
        if ($type) {
            $frameService  = app()->get(FrameService::class);
            $assistService = app()->get(FrameAssistService::class);
            $adminService  = app()->get(AdminService::class);

            $entId = 1;
            // 获取人员
            foreach ($members as $member) {
                $allFrameIds     = $frameService->getFrameTotalIds($member, $entId);
                $currenMemberIds = array_merge($currenMemberIds, array_unique($adminService->column([
                    'status' => 1,
                    'id'     => $assistService->column(['frame_ids' => array_merge($allFrameIds, [$member]), 'is_mastart' => 1], 'user_id'),
                ])));
            }
        } else {
            $currenMemberIds = $members;
        }

        [$filterGroups, $allMemberIds] = $this->getMemberIdsWithGroups($excludeGroupId);
        // 正常考勤人员
        $allMemberIds && $currenMemberIds && $userIds = array_intersect($currenMemberIds, $allMemberIds);
        return [$filterGroups, array_unique($userIds)];
    }

    /**
     * 考勤组重复人员检测.
     * @param int $type
     * @param array $members
     * @param int $filterId
     * @return array
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function memberRepeatCheck(int $type, array $members, int $filterId): array
    {
        [$filterGroups, $allMemberIds] = $this->getMemberIdsWithGroups($filterId);

        // 考勤部门
        if ($type > 0) {
            $frameService  = app()->get(FrameService::class);
            $assistService = app()->get(FrameAssistService::class);
            $adminService  = app()->get(AdminService::class);
            $tmpFrameIds   = [];
            foreach ($members as $frameId) {
                $tmpFrameIds = array_merge($tmpFrameIds, [$frameId], $frameService->getFrameTotalIds($frameId, 1));
            }

            $allMemberIds = array_diff($allMemberIds, array_unique($adminService->column([
                'status' => 1,
                'id'     => $assistService->column(['frame_ids' => $tmpFrameIds, 'is_mastart' => 1], 'user_id'),
            ], 'id')));
        }

        $intersect = array_intersect($members, $allMemberIds);
        if (empty($intersect)) {
            return [];
        }

        $with = [
            'job'    => fn($query) => $query->select(['id', 'name']),
            'frames' => fn($query) => $query->orderBy('frame_assist.is_admin', 'desc')->orderByDesc('frame_assist.is_mastart')->select(['frame.id', 'frame.name', 'frame_assist.is_mastart']),
        ];

        $groups = [];
        $list   = app()->get(AdminService::class)->select(['id' => $intersect, 'status' => 1], ['id', 'avatar', 'name', 'job', 'phone'], with: $with)->toArray();
        foreach ($list as &$item) {
            $groupId = $filterGroups[$item['id']];
            if (! isset($groups[$groupId])) {
                $item['group'] = $groups[$groupId] = toArray($this->get($groupId, ['id', 'name']));
            } else {
                $item['group'] = $groups[$groupId];
            }
        }

        return $list;
    }

    /**
     * 删除考勤组.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function deleteGroup(int $id): bool
    {
        $this->checkGroup(id: $id);
        return $this->transaction(function () use ($id) {
            $res = $this->dao->delete($id);
            if (! $res) {
                throw $this->exception(__('common.delete.fail'));
            }
            $this->memberDao->delete(['group_id' => $id]);
            $this->shiftDao->delete(['group_id' => $id]);
            app()->get(RosterCycleService::class)->deleteByGroupId($id);
            app()->get(AttendanceArrangeService::class)->clearFutureArrangeByGroupId($id);

            return true;
        });
    }

    /**
     * 检测班次是否使用.
     * @throws BindingResolutionException
     */
    public function checkShiftExist(int $shiftId): bool
    {
        return $this->shiftDao->exists(['shift_id' => $shiftId]);
    }

    /**
     * 获取未参与考核人员.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getUnAttendMember(): array
    {
        [$filterGroups, $allMemberIds] = $this->getMemberIdsWithGroups();
        $adminService                  = app()->get(AdminService::class);
        $diffIds                       = array_diff(
            $adminService->column(['status' => 1], 'id'),
            array_unique(array_merge($this->getWhiteListMemberIds(), $allMemberIds))
        );
        if (! count($diffIds)) {
            return [];
        }
        $with = [
            'job'    => fn ($query) => $query->select(['id', 'name']),
            'card'   => fn ($query) => $query->select(['work_time', 'id']),
            'frames' => fn ($query) => $query->orderBy('frame_assist.is_admin', 'desc')->orderByDesc('frame_assist.is_mastart')->select(['frame.id', 'frame.name', 'frame_assist.is_mastart']),
        ];
        return $adminService->getList(['id' => $diffIds], ['id', 'uid', 'avatar', 'name', 'job', 'phone'], with: $with);
    }

    /**
     * 考勤组人员.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getGroupMember(int $id, string $name = '', bool $withTrashed = false): array
    {
        if ($withTrashed) {
            $this->dao->setTrashed();
            $this->memberDao->setTrashed();
        }

        if (! $this->dao->exists(['id' => $id])) {
            throw $this->exception('操作失败，考勤组记录不存在');
        }
        return app()->get(AdminService::class)->select(['id' => $this->getMemberIdsById($id), 'name_like' => $name], ['id', 'name'])->toArray();
    }

    /**
     * 根据成员获取.
     * @return null|BaseModel|BuildsQueries|mixed|Model|object
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getGroupByUid(int $uid, array $field = ['*'], array $with = []): mixed
    {
        // 按人员获取
        $info = $this->dao->get(['member' => $uid], $field, $with);

        // 按部门获取
        if (! $info) {
            $frameIds = app()->get(FrameService::class)->getFrameIdsByUserId($uid);
            foreach (array_reverse($frameIds) as $frameId) {
                $info = $this->dao->get(['frame' => $frameId], $field, $with);
                if ($info) {
                    break;
                }
            }
        }
        return $info;
    }

    /**
     * 获取员工考勤组.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getMemberClockGroup(int $uid, int $id = 0, bool $admins = false, bool $filter = false): array
    {
        $with = [];
        if ($admins) {
            $with['admins'] = fn ($q) => $q->select(['admin.id', 'admin.name', 'admin.job', 'admin.avatar'])->with(['job' => fn ($q) => $q->select(['id', 'name'])]);
        }
        $field = ['id', 'name', 'address', 'lat', 'lng', 'effective_range', 'location_name', 'repair_allowed', 'repair_type',
            'is_limit_time', 'limit_time', 'is_limit_number', 'limit_number', 'is_photo', 'is_external', 'is_external_note', 'is_external_photo', ];

        if ($id) {
            $info = $this->dao->get($id, $field, $with);
        } else {
            $info = $this->getGroupByUid($uid, $field, $with);
        }

        if (! $info) {
            return [];
        }

        // 无需考勤
        if ($filter && ! in_array($uid, $this->getMemberIdsById((int) $info?->id))) {
            return [];
        }

        return toArray($info);
    }

    /**
     * 是否为白名单.
     * @throws BindingResolutionException
     */
    public function isWhitelist(int $uid, int $groupId = 0): bool
    {
        if ($groupId && $this->memberDao->exists(['group_id' => $groupId, 'type' => AttendanceGroupEnum::FILTER])) {
            return true;
        }

        return $this->whitelistDao->exists(['uid' => $uid, 'type' => AttendanceGroupEnum::WHITELIST_MEMBER]);
    }

    /**
     * 获取考勤组人员.
     * @param bool $filter 过滤无需考勤
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getMemberIdsById(int $id, bool $filter = true): array
    {
        $info = $this->dao->get(['id' => $id], ['id', 'type']);
        if (! $info) {
            return [];
        }

        // 考勤部门
        if ($info->type) {
            $allFrameIds  = [];
            $entId        = 1;
            $frameService = app()->get(FrameService::class);
            $frameIds     = $this->getMembersById($id, AttendanceGroupEnum::FRAME);
            foreach ($frameIds as $frameId) {
                $allFrameIds = array_merge($allFrameIds, [$frameId], $frameService->getFrameTotalIds($frameId, $entId));
            }

            $members = array_unique(app()->get(AdminService::class)->column([
                'status' => 1,
                'id'     => app()->get(FrameAssistService::class)->column(['frame_id' => $allFrameIds, 'is_mastart' => 1], 'user_id'),
            ], 'id'));
        } else {
            $members = $this->memberDao->column(['group_id' => $id, 'type' => AttendanceGroupEnum::MEMBER], 'member');
        }

        $filter && $members = $this->filterMember($id, $members);
        return $members;
    }

    /**
     * 过滤无需考勤人员.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function filterMember(int $id, array $members): array
    {
        if (! $id) {
            return $members;
        }

        $filters = array_merge(
            $this->getWhiteListMemberIds(),
            $this->memberDao->column(['group_id' => $id, 'type' => AttendanceGroupEnum::FILTER], 'member')
        );

        if ($filters) {
            return array_diff($members, $filters);
        }

        return $members;
    }

    /**
     * 根据管理员获取考勤成员.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getMemberByAdminUid(int $uid, bool $filter = false): array
    {
        $groupMemberIds = [];
        $rolesMemberIds = app()->get(RolesService::class)->getDataUids($uid);
        $ids            = $this->memberDao->column(['member' => $uid, 'type' => AttendanceGroupEnum::ADMIN], 'group_id');
        foreach ($ids as $id) {
            $groupMemberIds = array_merge($groupMemberIds, $this->getMemberIdsById((int) $id, $filter));
        }
        return array_unique(array_merge($rolesMemberIds, $groupMemberIds));
    }

    /**
     * 获取团队人员数据.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getTeamMember(int|string $uuid, int $entId = 1, bool $filter = true, bool $withMe = true): array
    {
        $uid = is_string($uuid) ? uuid_to_uid($uuid) : $uuid;

        // 超级管理员
        if ($this->isWhiteListAdmin($uid)) {
            $member = app()->get(AdminService::class)->column(['status' => 1], 'id');
        } else {
            $member = $this->getMemberByAdminUid($uid, $filter);
        }

        $withMe && $member[] = $uid;
        return array_unique($member);
    }

    /**
     * 下拉数据.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getSelectList(array $where = [], array $field = ['id', 'name']): array
    {
        return $this->dao->getList($where, $field, 0, 0, 'id');
    }

    /**
     * 用户是否为白名单管理员.
     * @throws BindingResolutionException
     */
    public function isWhiteListAdmin(int $uid): bool
    {
        return $this->whitelistDao->exists(['uid' => $uid, 'type' => AttendanceGroupEnum::WHITELIST_ADMIN]);
    }

    /**
     * 获取考勤组管理员.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getMembersById(int $id, int $type): array
    {
        return $this->memberDao->column(['group_id' => $id, 'type' => $type], 'member');
    }

    /**
     * 获取超级管理员ID.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getWhiteListAdminIds(): array
    {
        return Cache::tags([CacheEnum::TAG_ATTENDANCE])->remember('white_list_admin_ids', (int) sys_config('system_cache_ttl', 3600), function () {
            return $this->whitelistDao->column(['type' => AttendanceGroupEnum::WHITELIST_ADMIN], 'uid');
        });
    }

    /**
     * 获取白名单人员ID.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getWhiteListMemberIds(): array
    {
        return Cache::tags([CacheEnum::TAG_ATTENDANCE])->remember('white_list_member_ids', (int) sys_config('system_cache_ttl', 3600), function () {
            return $this->whitelistDao->column(['type' => AttendanceGroupEnum::WHITELIST_MEMBER], 'uid');
        });
    }

    /**
     * 获取考勤组人员数据.
     * @param bool $filter 过滤无需考勤
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getMemberUsersById(int $id, bool $filter = true, bool $trashed = false): array
    {
        if ($trashed) {
            $this->dao->setTrashed();
            $this->memberDao->setTrashed();
        }
        $info = $this->dao->get(['id' => $id], ['id', 'type']);
        if (! $info) {
            return [];
        }

        $adminService = app()->get(AdminService::class);
        // 考勤部门
        if ($info->type) {
            $allFrameIds  = [];
            $entId        = 1;
            $frameService = app()->get(FrameService::class);
            $frameIds     = $this->getMembersById($id, AttendanceGroupEnum::FRAME);
            foreach ($frameIds as $frameId) {
                $allFrameIds = array_merge($allFrameIds, [$frameId], $frameService->getFrameTotalIds($frameId, $entId));
            }

            $members = array_unique($adminService->column([
                'status' => 1,
                'id'     => app()->get(FrameAssistService::class)->column(['frame_id' => $allFrameIds, 'is_mastart' => 1], 'user_id'),
            ]));
        } else {
            $members = $this->getMembersById($id, AttendanceGroupEnum::MEMBER);
        }
        $filter && $members = $this->filterMember($id, $members);
        return $adminService->select(['id' => $members], ['id', 'name'])->toArray();
    }

    /**
     * 考勤部门重复检测.
     * @param array $members
     * @param int $id
     * @param array $filterMember
     * @return void
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function checkFrameRepeat(array $members, int $id = 0, array $filterMember = []): void
    {
        $allFrameIds  = [];
        $frameService = app()->get(FrameService::class);

        $entId = 1;
        foreach ($this->dao->select(['type' => 1], ['id', 'type']) as $item) {
            $frameIds = $this->getMembersById((int) $item->id, AttendanceGroupEnum::FRAME);
            foreach ($frameIds as $frameId) {
                $allFrameIds = array_merge($allFrameIds, [$frameId], $frameService->getFrameTotalIds($frameId, $entId));
            }
        }

        if ($id) {
            $groupFrameIds = [];
            if (! $this->dao->exists(['id' => $id])) {
                throw $this->exception('考勤组异常');
            }

            $entId        = 1;
            $frameService = app()->get(FrameService::class);
            $frameIds     = $this->getMembersById($id, AttendanceGroupEnum::FRAME);
            foreach ($frameIds as $frameId) {
                $groupFrameIds = array_merge($groupFrameIds, [$frameId], $frameService->getFrameTotalIds($frameId, $entId));
            }

            $allFrameIds = array_diff($allFrameIds, $groupFrameIds);
        }

        $memberFrameIds = [];
        foreach ($members as $member) {
            $memberFrameIds = array_merge($memberFrameIds, [$member], $frameService->getFrameTotalIds($member, $entId));
        }

        if (! empty(array_intersect(array_unique($memberFrameIds), array_unique($allFrameIds)))) {
            throw $this->exception('考勤部门重复');
        }
    }

    /**
     * 考勤人员重复检测.
     * @param array $members
     * @param int $id
     * @param array $filterMember
     * @return void
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function checkMemberRepeat(array $members, int $id = 0, array $filterMember = []): void
    {
        [$filterGroups, $allMemberIds] = $this->getMemberIdsWithGroups();
        $allMemberIds                  = array_unique($allMemberIds);

        if ($id) {
            $allMemberIds = array_diff($allMemberIds, $this->getMemberIdsById($id));
        }

        if ($filterMember) {
            $allMemberIds = array_diff($allMemberIds, $filterMember);
        }

        if (! empty(array_intersect($members, $allMemberIds))) {
            throw $this->exception('考勤人员重复');
        }
    }

    /**
     * 参加考勤的部门/人员.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getGroupMembersByType(int $type, int $filterId = 0): array
    {
        $filterIds = [];

        $frameService = app()->get(FrameService::class);

        // 所有考勤人员
        [$filterGroups, $allMemberIds] = $this->getMemberIdsWithGroups();
        $groupMemberIds                = array_unique($allMemberIds);

        if ($filterId) {
            if ($type == AttendanceGroupEnum::FRAME) {
                foreach ($this->getMembersById($filterId, AttendanceGroupEnum::FRAME) as $frameId) {
                    $filterIds = array_merge($filterIds, [$frameId], $frameService->getFrameTotalIds($frameId, 1));
                }
            } else {
                $filterIds = $this->getMemberIdsById($filterId);
            }

            $groupMemberIds = array_diff($groupMemberIds, array_unique($filterIds));
        }

        if (empty($groupMemberIds)) {
            return [];
        }

        $with = [
            'job'    => fn ($query) => $query->select(['id', 'name']),
            'frames' => fn ($query) => $query->orderBy('frame_assist.is_admin', 'desc')->orderByDesc('frame_assist.is_mastart')->select(['frame.id', 'frame.name', 'frame_assist.is_mastart']),
        ];
        $list = app()->get(AdminService::class)->select(['id' => $groupMemberIds, 'status' => 1], ['id', 'avatar', 'name', 'job', 'phone'], with: $with)->toArray();
        foreach ($list as $key => $item) {
            $list[$key]['group'] = $this->getGroupByUid($item['id'], ['id', 'name']);
        }
        return $list;
    }

    /**
     * 获取考勤组.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getGroupByUidAndGroupId(int $uid, int $groupId = 0, array $field = ['id', 'name']): mixed
    {
        if ($groupId) {
            $group = $this->dao->get($groupId, $field);
            if ($group) {
                return $group;
            }
        }
        return $this->getGroupByUid($uid, $field);
    }

    /**
     * 设置白名单人员.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    private function setWhiteByType(array $list, int $type): void
    {
        $whiteList    = array_column($this->whitelistDao->column(['type' => $type], ['uid']), 'uid');
        $delAids      = array_diff($whiteList, $list);
        $list         = array_diff($list, $whiteList);
        $adminService = app()->get(AdminService::class);

        $delAids && $this->whitelistDao->delete(['uid' => $delAids]);
        foreach ($list as $item) {
            if (! $adminService->exists(['id' => $item])) {
                continue;
            }
            $this->whitelistDao->create(['uid' => $item, 'type' => $type]);
        }
    }

    /**
     * 处理考勤成员.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    private function handleMember(array $members, int $type, int $groupId = 0): void
    {
        $data = [];
        if ($groupId) {
            foreach ($this->memberDao->column(['group_id' => $groupId, 'type' => $type], 'member', 'id') as $key => $item) {
                $data[$item] = $key;
            }
        }

        foreach ($members as $member) {
            if ($groupId && isset($data[$member])) {
                unset($data[$member]);
                continue;
            }
            $this->memberDao->create(['group_id' => $groupId, 'member' => $member, 'type' => $type]);
        }

        if ($type == AttendanceGroupEnum::FILTER) {
            app()->get(AttendanceArrangeService::class)->clearFutureArrangeByMembers($members);
        }

        if ($data) {
            // 清除剩余member排班数据
            $isAttendanceType = in_array($type, [AttendanceGroupEnum::MEMBER, AttendanceGroupEnum::FRAME]);
            if ($isAttendanceType) {
                $clearMembers = $type == AttendanceGroupEnum::MEMBER ? array_keys($data) : $this->getMemberIdsByFrameIds($groupId, array_keys($data));
                app()->get(AttendanceArrangeService::class)->clearFutureArrangeByMembers($clearMembers);
            }

            $this->memberDao->forceDelete(['group_id' => $groupId, 'id' => $data, 'type' => $isAttendanceType ? [AttendanceGroupEnum::MEMBER, AttendanceGroupEnum::FRAME] : $type]);
        }
    }

    /**
     * 处理考勤班次
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    private function handleShift(int $groupId, array $shifts): void
    {
        $data      = [];
        $shiftList = $this->shiftDao->column(['group_id' => $groupId], 'shift_id', 'id');
        foreach ($shiftList as $key => $item) {
            $data[$groupId . '_' . $item] = $key;
        }

        foreach ($shifts as $shift) {
            if (isset($data[$groupId . '_' . $shift])) {
                unset($data[$groupId . '_' . $shift]);
                continue;
            }

            $whereData = ['group_id' => $groupId, 'shift_id' => $shift];
            $this->shiftDao->firstOrCreate($whereData, $whereData);
        }
        $data && $this->shiftDao->forceDelete(['id' => array_values($data)]);
    }

    /**
     * 调整考勤组排除成员.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    private function otherFilters(array $data, int $excludeGroupId = 0): void
    {
        [$filterGroups, $userIds] = $this->filterGroupMember((int) $data['type'], $data['members'], $excludeGroupId);
        app()->get(AttendanceArrangeService::class)->clearFutureArrangeByMembers($data['other_filters']);
        foreach ($data['other_filters'] as $filter) {
            if (isset($filterGroups[$filter])) {
                $whereData = ['group_id' => $filterGroups[$filter], 'member' => $filter, 'type' => AttendanceGroupEnum::FILTER];
                $this->memberDao->updateOrCreate($whereData, $whereData);
            }
        }
    }

    /**
     * 是否为无需考勤人员.
     * @throws BindingResolutionException
     */
    private function isFilterMember(int $groupId, int $uid): bool
    {
        return $this->memberDao->exists(['group_id' => $groupId, 'type' => AttendanceGroupEnum::FILTER, 'member' => $uid]);
    }

    /**
     * 获取所有考勤组人员.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    private function getMemberIdsWithGroups(int $excludeGroupId = 0): array
    {
        $groups        = $memberIds = [];
        $frameService  = app()->get(FrameService::class);
        $assistService = app()->get(FrameAssistService::class);
        $adminService  = app()->get(AdminService::class);

        $list = $this->dao->select($excludeGroupId ? ['not_id' => $excludeGroupId] : [], ['id', 'type']);

        $entId = 1;
        foreach ($list as $item) {
            if ($item->type) {
                $currentFrameIds = [];
                $frameIds        = $this->getMembersById($item->id, AttendanceGroupEnum::FRAME);
                foreach ($frameIds as $frameId) {
                    $currentFrameIds = array_merge($currentFrameIds, [$frameId], $frameService->getFrameTotalIds($frameId, $entId));
                }

                $currentMemberIds = array_unique($this->filterMember($item->id, $adminService->column([
                    'status' => 1,
                    'id'     => $assistService->column(['frame_ids' => $currentFrameIds, 'is_mastart' => 1], 'user_id'),
                ], 'id')));
            } else {
                $currentMemberIds = $this->getMemberIdsById($item->id);
            }
            // 员工对应部门
            foreach ($currentMemberIds as $memberId) {
                $groups[$memberId] = $item->id;
            }
            $memberIds = array_merge($memberIds, $currentMemberIds);
        }

        $excludeGroupId && $memberIds = array_diff($memberIds, $this->getMemberIdsById($excludeGroupId));
        return [$groups, array_unique($excludeGroupId ? $this->filterMember($excludeGroupId, $memberIds) : $memberIds)];
    }

    /**
     * 获取指定部门考勤人员.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    private function getMemberIdsByFrameIds(int $id, array $frameIds): array
    {
        $allFrameIds  = [];
        $entId        = 1;
        $frameService = app()->get(FrameService::class);
        foreach ($frameIds as $frameId) {
            $allFrameIds = array_merge($allFrameIds, [$frameId], $frameService->getFrameTotalIds($frameId, $entId));
        }

        return $this->filterMember($id, array_unique(app()->get(AdminService::class)->column([
            'status' => 1,
            'id'     => app()->get(FrameAssistService::class)->column(['frame_id' => $allFrameIds, 'is_mastart' => 1], 'user_id'),
        ])));
    }
}
