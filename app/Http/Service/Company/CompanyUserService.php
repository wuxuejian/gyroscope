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

namespace App\Http\Service\Company;

use App\Constants\CacheEnum;
use App\Constants\RuleEnum;
use App\Http\Contract\Company\CompanyUserInterface;
use App\Http\Dao\Company\CompanyUserDao;
use App\Http\Dao\Frame\FrameAssistDao;
use App\Http\Dao\Frame\FrameDao;
use App\Http\Service\Admin\AdminService;
use App\Http\Service\BaseService;
use App\Http\Service\Frame\FrameAssistService;
use App\Http\Service\Frame\FrameScopeService;
use App\Http\Service\Frame\FrameService;
use App\Http\Service\System\MenusService;
use App\Http\Service\System\RolesService;
use App\Http\Service\System\RoleUserService;
use App\Http\Service\User\UserService;
use App\Task\frame\UserJoinEnterpriseJob;
use crmeb\services\phpoffice\SheetService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Cache;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * @deprecated
 * 企业用户
 * @method CompanyUserDao getAssessPlanModel(array $where, int $entid)
 */
class CompanyUserService extends BaseService implements CompanyUserInterface
{
    private FrameDao $frameDao;

    private FrameAssistDao $assistDao;

    public function __construct(CompanyUserDao $dao, FrameDao $frameDao, FrameAssistDao $assistDao)
    {
        $this->dao       = $dao;
        $this->frameDao  = $frameDao;
        $this->assistDao = $assistDao;
    }

    /**
     * 列表.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getPageFrameUsers(array $where, array $field = ['*'], null|array|string $sort = null, array $with = []): array
    {
        $where          = $this->getListWhere($where);
        [$page, $limit] = $this->getPageValue();
        $list           = $this->dao->getList($where, $field, $page, $limit, $sort, $with);
        $count          = $this->dao->count($where);
        return $this->listData($list, $count);
    }

    /**
     * 获取企业用户信息.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getFrameUserInfo(int $id, int $entId): array
    {
        return Cache::tags([CacheEnum::TAG_FRAME . '_user'])->remember(md5($id . $entId), (int) sys_config('system_cache_ttl', 3600), function () use ($id) {
            $where = compact('id');
            $field = ['id', 'name', 'phone', 'job', 'created_at'];
            $with  = [
                'frames' => fn ($query) => $query->select(['frame_assist.id', 'name', 'is_mastart', 'is_admin']),
                'job'    => fn ($query) => $query->select(['id', 'name']),
                'senior' => fn ($query) => $query->select(['user_enterprise.id', 'name', 'phone', 'job', 'superior_uid']),
                'scope'  => fn ($query) => $query->select(['frame.id', 'name']),
            ];
            return toArray($this->dao->get($where, $field, $with));
        });
    }

    /**
     * 修改企业用户信息.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function updateFrameUserInfo(int $id, int $entId, array $data): bool
    {
        if (! $info = $this->dao->get(['id' => $id, 'entid' => $entId])) {
            throw $this->exception('未找到修改的用户信息');
        }
        if ($data['master_frame'] && ! $data['senior']) {
            throw $this->exception('请选择直属上级');
        }
        $res = $this->transaction(function () use ($data, $info, $id, $entId) {
            $info->name  = $data['name'];
            $info->phone = $data['phone'];
            $info->job   = $data['job'];
            $res1        = $info->save();
            $res2        = app()->get(FrameAssistService::class)->batchAdd($data['frame'], [$id], $data['first_frame'], $entId, $data['master_frame'], $data['senior']);
            app()->get(FrameScopeService::class)->saveUserScope($id, $entId, $data['scope']);
            return $res1 && $res2;
        });
        if ($res) {
            Cache::tags([CacheEnum::TAG_FRAME . '_user'])->flush();
        }
        return $res;
    }

    public function userInfo(int $id, array|string $field = ['*']): array|string
    {
        return is_string($field) ? (string) $this->dao->value($id, $field) : toArray($this->dao->get($id, $field));
    }

    /**
     * 获取修修改组织架构成员信息(不含权限).
     * @return array
     * @throws \ReflectionException
     * @throws BindingResolutionException
     */
    public function getUserInfo(int $id, int $entid = 1)
    {
        $userInfo = toArray($this->dao->get($id, ['*'], ['frames', 'card' => fn ($q) => $q->select(['enterprise_user_card.id', 'enterprise_user_card.phone'])]));
        if (! $userInfo) {
            throw $this->exception('修改的用户不存在');
        }
        // 当前用户所在部门是否为主管
        $frame = app()->get(FrameAssistService::class)->get(['user_id' => $id, 'is_mastart' => 1, 'entid' => $entid], ['is_admin', 'superior_uid']);
        if (! $frame) {
            $userInfo['is_admin']     = 0;
            $userInfo['superior_uid'] = 0;
        } else {
            $userInfo['is_admin']     = $frame['is_admin'];
            $userInfo['superior_uid'] = $frame['superior_uid'];
        }
        $superiorUser                = toArray($this->dao->get($userInfo['superior_uid'], ['name', 'avatar', 'phone']));
        $userInfo['superior_name']   = $superiorUser['name'] ?? '';
        $userInfo['superior_avatar'] = $superiorUser['avatar'] ?? '';
        $userInfo['scope']           = app()->get(FrameScopeService::class)->getUserScope($userInfo['card_id']);
        return $userInfo;
    }

    /**
     * 验证手机号是否存在企业名片.
     * @throws BindingResolutionException
     */
    public function checkPhoneExists(string $uid, int $entid): bool
    {
        $phone = app()->get(UserService::class)->value($uid, 'phone');
        return app()->get(AdminService::class)->exists(['phone' => $phone]);
    }

    /**
     * 企业批量导入用户.
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function addMoreMember(int $entID, string $filePath, string $uid, int $frameId, int $limit = 1000): void
    {
        $data = SheetService::instance()->getExcelData($filePath, ['name' => 'A', 'phone' => 'B', 'uid' => 'C'], function (Worksheet $worksheet) {
            $data = ['A1' => '成员姓名', 'B1' => '手机号码', 'C1' => '成员ID(非必填)'];
            foreach ($data as $key => $value) {
                if ($worksheet->getCell($key)->getValue() != $value) {
                    throw $this->exception('模板格式不正确');
                }
            }
        });
        $count = count($data);
        $page  = $count < $limit ? 1 : ceil($count / $limit);
        for ($i = 0; $i < $page; ++$i) {
            UserJoinEnterpriseJob::dispatch(
                $entID,
                $pageData = collect($data)->forPage($i, $limit)->toArray(),
                $uid,
                $frameId
            );
        }
    }

    /**
     * 修改用户管理员.
     *
     * @throws BindingResolutionException
     */
    public function updateRole(array $userId, int $roleId)
    {
        $this->dao->userEnt($userId, function ($item) use ($roleId) {
            $roles       = is_string($item->roles) ? json_decode($item->roles, true) : $item->roles;
            $roles[]     = $roleId;
            $item->roles = array_merge(array_unique($roles));
            $item->save();
        });
    }

    /**
     * 获取拥有客户的业务员.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getCardWithClient(array $uid, int $entId = 1): array
    {
        return toArray($this->dao->search(['entid' => $entId])->select(['id', 'card_id'])
            ->whereIn('id', function ($query) use ($uid, $entId) {
                $query->from('client_list')->whereIn('uid', $uid)->where('entid', $entId)->select(['uid'])->groupBy('uid');
            })->with(['card' => fn ($q) => $q->select(['id', 'name'])])->get()->each(function ($item) {
                $item['name'] = $item->card->name ?? '';
                unset($item->card_id, $item->card);
            }));
    }

    /**
     * 判断用户是否有某菜单权限.
     *
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function haveMenuAuth(string $uuid, int $entid, string $type): bool
    {
        return true;
        $menusService = app()->get(MenusService::class);
        $menuIds      = match ($type) {
            RuleEnum::FINANCE_TYPE        => $menusService->column(['entid' => 1, 'type' => 0, 'menu_path' => '/fd/'], 'id'),
            RuleEnum::PERSONNEL_TYPE      => $menusService->column(['entid' => 1, 'type' => 0, 'menu_path' => '/hr/'], 'id'),
            RuleEnum::ADMINISTRATION_TYPE => $menusService->column(['entid' => 1, 'type' => 0, 'menu_path' => '/administration/'], 'id'),
            default                       => $menusService->column(['entid' => 1, 'type' => 0], 'id'),
        };
        $userRoles = $this->dao->value(['uid' => $uuid, 'entid' => $entid], 'roles');
        return (bool) array_intersect(app()->get(RolesService::class)->getAdminRole($userRoles), $menuIds);
    }

    /**
     * 获取用户数量.
     * @throws BindingResolutionException
     */
    public function getUserCountCache(): int
    {
        return (int) Cache::tags('enterprise')->remember(
            'enterprise_user_cache',
            (int) sys_config('system_cache_ttl', 3600),
            fn () => $this->dao->count(['verify' => 1])
        );
    }

    /**
     * uuids获取user_id.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function batchUuidByUserId(array $uuids): array
    {
        return $this->dao->column(['uid' => $uuids, 'verify' => 1], 'id');
    }

    /**
     * 根据id获取人员数据.
     * @throws \ReflectionException
     * @throws BindingResolutionException
     */
    public function getMemberByIds(array $ids, array $field = ['id', 'uid', 'card_id', 'name', 'job', 'phone', 'join_time']): array
    {
        $with = [
            'job'    => fn ($query) => $query->select(['id', 'name']),
            'card'   => fn ($query) => $query->select(['work_time', 'id']),
            'frames' => fn ($query) => $query->orderBy('frame_assist.is_admin', 'desc')
                ->orderByDesc('frame_assist.is_mastart')->select(['frame.id', 'frame.name', 'frame_assist.is_mastart']),
        ];

        [$page, $limit] = $this->getPageValue();
        $where          = ['status' => 1, 'uid' => true, 'id' => $ids];
        $list           = $this->dao->getList($where, $field, $page, $limit, 'id', $with);
        $count          = $this->dao->count($where);
        return $this->listData($list, $count);
    }

    /**
     * 获取部门员工数据.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function getIdsByFrameIds(int $uid, array|int $frameIds = []): array
    {
        $frameService = app()->get(FrameService::class);
        if (empty($frameIds)) {
            $frameIds[] = $frameService->getFrameIdByUserId($uid);
        } else {
            $frameIds = array_intersect(
                array_unique(array_merge($frameIds, $frameService->scopeFrames($frameIds))),
                app()->get(RolesService::class)->getDataFrames(uid_to_uuid($uid), 1)
            );
        }

        return array_unique(array_intersect(
            $this->assistDao->column(['frame_id' => $frameIds], 'user_id'),
            app()->get(AdminService::class)->column(['status' => 1], 'id')
        ));
    }

    /**
     * 默认条件.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getListWhere(array $where): array
    {
        if (! $where['frame_id']) {
            $frameIds = $this->frameDao->column(['entid' => $where['entid']], 'id');
        } else {
            $frameIds = $this->frameDao->column(['path' => $where['frame_id'], 'entid' => $where['entid']], 'id');
        }

        unset($where['frame_id']);

        $where['ids'] = array_values(array_unique($this->assistDao->column(['entid' => $where['entid'], 'frame_ids' => $frameIds], 'user_id')));
        if (isset($where['types']) && $where['types']) {
            $normalUid    = app()->get(AdminService::class)->column(['status' => 1], 'id');
            $where['ids'] = array_values(array_unique(array_intersect($where['ids'], $normalUid)));
            unset($where['types']);
        }

        return $where;
    }

    /**
     * 组织架构列表(部门主管放第一位).
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getOrganizationalStructureList(array $where, array $field = ['*'], null|array|string $sort = null, array $with = []): array
    {
        $list           = [];
        $count          = 0;
        [$page, $limit] = $this->getPageValue();

        // frame admin
        $userId = app()->get(FrameAssistService::class)->getFrameAdminUserId((int) $where['frame_id'] ?: 1);

        $where = $this->getListWhere(array_merge($where, ['types' => [1, 2, 3]]));
        if ($userId && $page < 2) {
            $list  = $this->dao->getList(array_merge($where, ['id' => $userId]), $field, 1, 1, $sort, $with);
            $count = count($list);
            $count && $limit--;
            $where['not_id'] = $userId;
        }

        $list  = array_merge($list, $this->dao->getList($where, $field, $page, $limit, $sort, $with));
        $count = $count + $this->dao->count($where);
        return $this->listData($list, $count);
    }

    /**
     * 验证账号.
     *
     * @param mixed $phone
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function checkUserAuth($phone)
    {
        $info = app()->get(AdminService::class)->get(['phone' => $phone]);
        if ($info) {
            if (! $info->status) {
                throw $this->exception('该账号暂时无法登陆，请联系管理员激活');
            }
        } elseif (! sys_config('registration_open', 0)) {
            throw $this->exception('无效的账号信息!');
        }
    }

    /**
     * 添加主部门默认权限.
     * @throws BindingResolutionException
     */
    public function addDefaultRoleByMasterFrame(int $id, int $masterId, int $entId): void
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
}
