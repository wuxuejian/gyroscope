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

namespace App\Http\Controller\AdminApi\Company;

use App\Http\Controller\AdminApi\AuthController;
use App\Http\Requests\enterprise\user\EnterpriseUserCardRequest;
use App\Http\Service\Admin\AdminService;
use App\Http\Service\Assess\AssessScoreService;
use App\Http\Service\Company\CompanyApplyService;
use App\Http\Service\Company\CompanyUserService;
use App\Http\Service\Frame\FrameService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Reader\Exception as ReaderException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Prefix;
use Spatie\RouteAttributes\Attributes\Put;

/**
 * 企业用户.
 */
#[Prefix('ent/user')]
#[Middleware(['auth.admin', 'ent.auth', 'ent.log'])]
class CompanyUserController extends AuthController
{
    public function __construct(CompanyUserService $services)
    {
        parent::__construct();
        $this->service = $services;
    }

    /**
     * 获取组织架构人员列表.
     */
    public function list(): mixed
    {
        $where = $this->request->getMore([
            ['name', '', 'name_like'],
            ['frame_id', 0],
            ['entid', 1],
        ]);
        $field = ['id', 'name', 'avatar', 'phone', 'job', 'created_at'];
        $with  = [
            'frames' => fn ($query) => $query->select(['frame_assist.id', 'name', 'is_mastart']),
            'job'    => fn ($query) => $query->select(['id', 'name']),
        ];
        $data = $this->service->getPageFrameUsers($where, $field, 'id', $with);
        return $this->success($data);
    }

    /**
     * 组织架构人员详情.
     * @return mixed
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function info($id)
    {
        $info = $this->service->getFrameUserInfo((int) $id, $this->entId);
        return $this->success($info);
    }

    /**
     * 修改组织架构人员.
     * @return mixed
     */
    public function update($id)
    {
        $data = $this->request->postMore([
            ['name', ''],
            ['phone', ''],
            ['gender', ''],
            ['frame', []],
            ['job', 0],
            ['first_frame', 0],
            ['master_frame', 0],
            ['senior', 0],
            ['scope', []],
        ]);
        $res = $this->service->updateFrameUserInfo((int) $id, $this->entId, $data);
        return $res ? $this->success('修改成功') : $this->success('修改失败');
    }

    /**
     * 批量导入邀请用户加入企业.
     *
     * @throws ContainerExceptionInterface
     * @throws Exception
     * @throws NotFoundExceptionInterface
     * @throws ReaderException
     */
    public function addMoreMember(): mixed
    {
        $filePath = $this->request->get('file');
        $frameId  = $this->request->get('frame_id');
        if (! $filePath) {
            return $this->fail('批量导入文件必须存在');
        }
        $filePath = str_replace(sys_config('site_url'), '', $filePath);
        $filePath = public_path($filePath);
        if (! file_exists($filePath)) {
            return $this->fail('批量导入文件不存在');
        }
        if (! $frameId) {
            return $this->fail('缺少组织架构id');
        }
        $this->service->addMoreMember($this->entId, $filePath, $this->uuid, (int) $frameId);
        return $this->success('操作成功，请等待用户确认！');
    }

    /**
     * 获取企业用户列表.
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Get('list', '组织架构人员列表')]
    public function index(AdminService $service)
    {
        $where = $this->request->getMore([
            ['pid', '', 'frame_id'],
            ['name', ''],
            ['status', 1],
            ['types', [1, 2, 3]],
        ]);
        $with = [
            'job'    => fn ($query) => $query->select(['id', 'name']),
            'frames' => fn ($query) => $query->orderBy('frame_assist.is_admin', 'desc')
                ->orderByDesc('frame_assist.is_mastart')
                ->select(['frame.id', 'frame.name', 'frame_assist.is_mastart', 'frame_assist.is_admin']),
        ];
        return $this->success($service->getListOrderAdmin($where, ['is_admin', 'frame_assist.is_admin', 'id' => 'asc'], $with));
    }

    /**
     * 获取修改组织架构成员信息.
     * @return mixed
     */
    public function edit($id)
    {
        if (! $id) {
            return $this->fail('common.empty.attrs');
        }
        return $this->success($this->service->getEditUserInfo($id, $this->entId));
    }

    /**
     * 组织架构成员信息.
     * @return mixed
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    #[Get('card/{id}', '组织架构成员信息')]
    public function editUser(AdminService $service, $id)
    {
        if (! $id) {
            return $this->fail('common.empty.attrs');
        }
        return $this->success($service->editAdminFrame((int) $id));
    }

    /**
     * 修改用户角色权限.
     * @return mixed
     */
    public function saveUserRole($id)
    {
        [$roleId] = $this->request->postMore([
            ['role_id', []],
        ], true);
        $this->service->saveUserRole($id, $this->entId, $roleId);

        return $this->success('common.update.succ');
    }

    /**
     * 修改组织架构成员.
     * @return mixed
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ValidationException
     * @throws \ReflectionException
     */
    #[Put('card/{id}', '修改组织架构成员')]
    public function updateUser(EnterpriseUserCardRequest $request, AdminService $service, $id)
    {
        $request->scene('edit')->check();
        if (! $id) {
            return $this->fail('common.empty.attrs');
        }
        $data = $request->postMore([
            ['frame_id', []],
            ['mastart_id', 0],
            ['name', ''],
            ['position', ''],
            ['phone', ''],
            ['is_admin', 0],
            ['superior_uid', 0],
            ['frames', []],
            ['manage_frame', [], 'manage_frames'],
            ['cards', []],
        ]);
        $service->saveAdminFrame((int) $id, $data);
        return $this->success('common.update.succ');
    }

    /**
     * 获取企业邀请的用户列表.
     * @return mixed
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function getApply(CompanyApplyService $services)
    {
        return $this->success($services->getList(['entid' => $this->entId, 'status_apply' => $this->request->get('status', '')], ['*'], 'id'));
    }

    /**
     * 删除企业邀请用户.
     * @return mixed
     */
    public function destroyApply($applyId)
    {
        if (! $applyId) {
            return $this->fail('common.empty.attrs');
        }
        if ($this->service->destroyApply((int) $applyId)) {
            return $this->success('删除成功');
        }
        return $this->fail('删除失败');
    }

    /**
     * 获取用户关联企业详情.
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function userInfo()
    {
        $userInfo = app()->get(AdminService::class)->get(auth('admin')->id())?->toArray();
        if (! $userInfo) {
            return $this->fail('未找到用户信息');
        }
        // 用户所在所有企业
        $userInfo['entIds']       = [1];
        $userInfo['job_id']       = $userInfo['job'];
        $userInfo['maxScore']     = app()->get(AssessScoreService::class)->max(['entid' => $this->entId], 'max') ?: 0;
        $userInfo['compute_mode'] = sys_config('assess_compute_mode', 1) ? 1 : 0;

        return $this->success($userInfo);
    }

    /**
     * 获取用户部门信息.
     * @return mixed
     */
    public function userFrame()
    {
        $where    = ['uid' => $this->uuid, 'status' => 1, 'entid' => $this->entId];
        $userInfo = $this->service->get($where, ['id'], ['frame']);
        if (! $userInfo) {
            return $this->fail('未找到用户信息');
        }
        if (! $userInfo['frame']) {
            return $this->fail('未找到用户部门信息');
        }
        return $this->success($userInfo['frame']);
    }

    /**
     * 获取通讯录组织架构.
     * @return mixed
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    #[Get('add_book/tree', '通讯录tree型数据')]
    public function getFrameTree(FrameService $services)
    {
        $where = $this->request->getMore([
            ['name', '', 'name_like'],
        ]);
        return $this->success($services->tree($where));
    }

    /**
     * 通讯录用户列表.
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Get('add_book/list', '通讯录用户列表')]
    public function addressBook(AdminService $service)
    {
        $where = $this->request->getMore([
            ['frame_id', 0],
            ['entid', 1],
            ['time', ''],
            ['sex', ''],
            ['types', [1, 2, 3]],
            ['field', ''],
            ['status', ''],
            ['search', '', 'name'],
        ]);
        $with = [
            'frames' => fn ($query) => $query->select(['frame.id', 'frame.name', 'frame_assist.is_mastart', 'frame_assist.is_admin']),
            'job'    => fn ($query) => $query->select(['id', 'name']),
            'info'   => fn ($query) => $query->select(['uid', 'email']),
        ];
        $data = $service->getListOrderAdmin($where, ['is_admin', 'frame_assist.is_admin', 'id' => 'asc'], $with);
        return $this->success($data);
    }

}
