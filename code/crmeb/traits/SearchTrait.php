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

namespace crmeb\traits;

use App\Http\Service\Admin\AdminService;
use App\Http\Service\Frame\FrameAssistService;
use App\Http\Service\Frame\FrameService;
use App\Http\Service\System\RolesService;

/**
 * 注册额外搜索条件.
 */
trait SearchTrait
{
    public array $uids = [];

    /**
     * 全部.
     */
    protected string $all = 'all';

    /**
     * 仅本人.
     */
    protected string $self = 'self';

    /**
     * 本部门(含无限下级).
     */
    protected string $department = 'dep';

    /**
     * 直属下级.
     */
    protected string $subordinate = 'sub';

    /**
     * 本人+直属下级.
     */
    protected string $team = 'team';

    public function withScopeFrame($key = 'uid', int $crudId = 0, int $crudRouleType = 1)
    {
        $frameIds  = app('request')->input('scope_frame', 'all');
        $normal    = (int) app('request')->input('scope_normal', '1');
        $searchUid = app('request')->input($key, []);
        $userId    = auth('admin')->id();
        $roleUids  = app()->get(RolesService::class)->getDataUids(userId: $userId, normal: $normal, crudId: $crudId, crudRouleType: $crudRouleType);
        switch ($frameIds) {
            case $this->self:
                $uid = [$userId];
                break;
            case $this->department:
                $frameAssist = app()->get(FrameAssistService::class);
                $info        = $frameAssist->get(['user_id' => $userId, 'is_mastart' => 1], ['frame_id', 'is_admin', 'entid']);
                if ($info['is_admin']) {
                    $uid = app()->get(FrameService::class)->scopeUser((int) $info['frame_id']);
                } else {
                    $uid = $frameAssist->column(['frame_id' => $info['frame_id'], 'is_mastart' => 1, 'is_admin' => 0], 'user_id');
                }
                $uid = array_intersect($uid, $roleUids);
                break;
            case $this->subordinate:
                $uid = app()->get(FrameAssistService::class)->getSubUid($userId);
                $uid = array_intersect($uid, $roleUids);
                break;
            case $this->team:
                $uid = array_merge(app()->get(FrameAssistService::class)->getSubUid($userId), [$userId]);
                $uid = array_intersect($uid, $roleUids);
                break;
            case $this->all:
                $uid = $roleUids;
                break;
            default:
                $frameId  = app()->get(FrameService::class)->scopeFrames((int) $frameIds);
                $frameUid = app()->get(FrameAssistService::class)->column(['frame_id' => $frameId, 'is_mastart' => 1], 'user_id');
                $uid      = array_intersect($frameUid, $roleUids);
        }

        if ($normal) {
            $uid = array_intersect($uid, app()->get(AdminService::class)->column(['status' => 1], 'id'));
        }

        if ($searchUid) {
            $searchUid = array_intersect(is_array($searchUid) ? $searchUid : [$searchUid], $uid);
            app('request')->merge([
                $key => $searchUid,
            ]);
        } else {
            app('request')->merge([
                $key => $uid,
            ]);
        }
    }
}
