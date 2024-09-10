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
use App\Http\Dao\Company\CompanyUserCardDao;
use App\Http\Dao\User\UserCardPerfectDao;
use App\Http\Service\BaseService;
use App\Http\Service\Frame\FrameAssistService;
use App\Http\Service\Frame\FrameScopeService;
use App\Http\Service\Frame\FrameService;
use App\Http\Service\User\UserApplyService;
use App\Http\Service\User\UserService;
use App\Task\frame\FrameCensusTask;
use App\Task\message\PerfectInfoRemind;
use Carbon\Carbon;
use Hhxsv5\LaravelS\Swoole\Task\Task;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Overtrue\Pinyin\Pinyin;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * 企业用户名片.
 * @deprecated
 */
class CompanyUserCardService extends BaseService
{
    public $dao;

    protected $otherData = [];

    protected array $frameData = [];

    protected array $normalType = [1, 2, 3];

    public function __construct(CompanyUserCardDao $dao)
    {
        $this->dao = $dao;
    }

    public function setOther($data)
    {
        $this->otherData = $data;

        return $this;
    }

    /**
     * 添加用户名片.
     *
     * @param int $verify
     *
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function addUserCard(string $uid, int $entid, int $frameId, $verify = 1)
    {
        $user = app()->get(UserService::class)->get($uid, ['uid', 'phone', 'avatar', 'real_name', 'sex', 'age', 'card_id', 'current_address', 'nation']);
        if (! $user) {
            throw $this->exception('未找到用户信息！');
        }
        if ($this->dao->exists(['uid' => $user['uid'], 'entid' => $entid])) {
            throw $this->exception('用户名片已存在，请勿重复操作！');
        }
        $card = [
            'entid'      => $entid,
            'uid'        => $user['uid'],
            'avatar'     => $user['avatar'],
            'phone'      => $user['phone'],
            'name'       => $user['real_name'],
            'card_id'    => $user['card_id'],
            'nation'     => $user['nation'],
            'address'    => $user['current_address'],
            'sex'        => $user['sex'],
            'age'        => $user['age'],
            'status'     => 1,
            'type'       => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'work_time'  => date('Y-m-d H:i:s'),
        ];
        // 创建用户名片
        $cardId         = $this->dao->getIncId($card);
        $userEntService = app()->get(CompanyUserService::class);
        $userentInfo    = $userEntService->create([
            'uid'     => $user['uid'],
            'entid'   => $entid,
            'verify'  => $verify,
            'status'  => 1,
            'card_id' => $cardId,
        ]);
        if (! $userentInfo) {
            throw $this->exception('用户和企业关联信息不存在');
        }
        $res = app()->get(FrameAssistService::class)->batchAdd($frameId, $userentInfo->id ? [$userentInfo->id] : [], (int) $frameId, $entid);
        if (! $user->entid) {
            $user->entid = $entid;
            $res         = $res && $user->save();
        }
        if (! ($cardId && $userentInfo && $res)) {
            throw $this->exception('同意加入企业失败');
        }
        Task::deliver(new FrameCensusTask());
        Cache::tags([CacheEnum::TAG_FRAME])->flush();
    }

    /**
     * 保存用户关联其他信息.
     * @param mixed $entid
     * @param mixed $userId
     * @param mixed $cardId
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function saveOther($entid, $userId, $cardId): bool
    {
        if ($this->otherData) {
            $frameAssistServices = app()->get(FrameAssistService::class);
            if (! empty($this->otherData['is_admin'])) {
                if ($frameAssistServices->count(['frame_id' => $this->otherData['main_id'], 'not_user_id' => $userId, 'is_admin' => 1])) {
                    throw $this->exception('主部门只能存在一个主管');
                }
            }
            if (isset($this->otherData['frame_id'])) {
                if (! $this->otherData['main_id']) {
                    $this->otherData['frame_id'] = $this->otherData['main_id'] = app()->get(FrameService::class)->dao->getDefaultFrame($entid);
                }

                $frameAssistServices->delete(['user_id' => $userId]);
                $frameAssistServices->batchAdd($this->otherData['frame_id'], [$userId], (int) $this->otherData['main_id'], $entid, (int) $this->otherData['is_admin'], $this->otherData['superior_uid']);
            }
            if (isset($this->otherData['frames'])) {
                app()->get(FrameScopeService::class)->saveUserScope($cardId, $entid, $this->otherData['frames']);
            }
            Task::deliver(new FrameCensusTask());
            $this->otherData = [];
            Cache::tags([CacheEnum::TAG_FRAME, 'rank_job_count_' . $entid])->flush();
        }

        return true;
    }

    /**
     * 修改员工档案信息.
     *
     * @param mixed $frame_id
     * @param mixed $id
     * @param mixed $type
     * @param mixed $data
     *
     * @throws BindingResolutionException|\ReflectionException
     */
    public function updateCardInfo(string $uuid, $id, $type, $data, $frame_id = 0): int
    {
        $info = $this->dao->get($id);
        if (! $info) {
            throw $this->exception('未找到相关员工信息');
        }
        $entId = 1;
        $save  = $this->getUpdateData($type, $data);
        if (isset($save['phone']) && $save['phone'] && $this->dao->exists(['phone' => $save['phone'], 'entid' => $entId, 'not_id' => $id])) {
            throw $this->exception('手机号码已存在');
        }
        if (isset($save['phone']) && $info['uid'] && $info['phone'] != $save['phone']) {
            throw $this->exception('手机号绑定的帐号已激活，无法修改手机号!');
        }

        $userEntService = app()->get(CompanyUserService::class);
        $userInfo       = $userEntService->get(['card_id' => $id], ['id', 'entid'], ['frames']);
        if (! $userInfo) {
            throw $this->exception('修改的企业用户信息不存在');
        }
        $userInfo = $userInfo->toArray();

        $this->setFrameData($userInfo['frames'], $this->otherData['frame_id'] ?? []);
        return $this->transaction(function () use ($type, $save, $id, $frame_id, $info, $userEntService, $entId, $uuid, $userInfo) {
            if ($type == 'basic') {
                $this->saveOther($entId, $userEntService->value(['card_id' => $id], 'id'), $id);
            } elseif ($frame_id) {
                $this->saveOther($info['entid'], $userEntService->value(['card_id' => $id], 'id'), $id);
            }
            $userEntData = $userData = [];
            if ($type == 'basic') {
                $userEntData['job']  = $save['position'];
                $userEntData['name'] = $save['name'];
            }
            if ($type == 'user') {
                $userData['real_name'] = $save['name'];
            }
            if (isset($save['email'])) {
                $userData['email'] = $save['email'];
            }
            if ($userData) {
                app()->get(UserService::class)->update($uuid, $userData);
            }
            if ($userEntData) {
                $userEntService->update($userInfo['id'], $userEntData);
            }

            // reload user role
            $masterId = $this->otherData['main_id'] ?? 0;
            $masterId && $userEntService->addDefaultRoleByMasterFrame($userInfo['id'], (int) $masterId, $entId);
            return $this->dao->update($id, $save);
        });
    }

    /**
     * 获取企业用户名片.
     *
     * @return array
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getUserCardInfo(int $id)
    {
        $cardInfo = $this->dao->get($id, ['*'], [
            'works' => function ($query) {
                $query->orderByDesc('id');
            },
            'educations' => function ($query) {
                $query->orderByDesc('id');
            },
            'userEnt' => function ($query) {
                $query->select(['id', 'card_id', 'verify', 'status', 'ident']);
            },
            'job' => function ($query) {
                $query->select(['id', 'name', 'describe']);
            },
            'scope' => function ($query) {
                $query->select(['frame.id', 'name', 'enterprise_user_scope.uid']);
            },
        ])->toArray();
        if (! $cardInfo) {
            throw $this->exception('查看的名片不存在');
        }
        $userEntServices = app()->get(CompanyUserService::class);
        $userInfo        = $userEntServices->get(['card_id' => $id], ['id', 'entid'], ['frames']);
        if (! $userInfo) {
            throw $this->exception('修改的企业用户信息不存在');
        }
        $cardInfo['frame']    = $userInfo['frames'];
        $cardInfo['is_admin'] = 0;
        $cardInfo['superior'] = [];
        if (! empty($userInfo['frames'])) {
            foreach ($userInfo['frames'] as $frames) {
                if ($frames['is_admin']) {
                    $cardInfo['is_admin'] = 1;
                    $cardInfo['superior'] = $userEntServices->get($frames['superior_uid'], ['id', 'name', 'avatar', 'uid']);
                }
            }
        }

        return $cardInfo;
    }

    /**
     * 修改企业用户状态
     *
     * @return mixed
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function updateUserStatus(int $id, int $status)
    {
        if (! ($userCardInfo = $this->dao->get($id, ['status', 'id', 'uid']))) {
            throw $this->exception('修改的用户名片不存在');
        }
        $userInfo = app()->get(CompanyUserService::class)->get(['uid' => $userCardInfo->uid, 'card_id' => $id], ['status', 'id']);
        if (! $userInfo) {
            throw $this->exception('用户关联企业信息不存在');
        }

        $userInfo->status     = $status;
        $userCardInfo->status = $status;

        return $this->transaction(function () use ($userInfo, $userCardInfo) {
            return $userInfo->save() && $userCardInfo->save();
        });
    }

    /**
     * 批量设置成员加入组织.
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function batchSetFrame(array $frameIds, array $userIds, int $mastartId, int $entid)
    {
        $frameServices = app()->get(FrameAssistService::class);
        $data          = [];
        $update        = [];
        foreach ($userIds as $item) {
            $ids = $frameServices->column(['user_id' => $item], 'frame_id');
            foreach ($frameIds as $frameId) {
                if (! in_array($frameId, $ids)) {
                    $data[] = [
                        'user_id'    => $item,
                        'frame_id'   => $frameId,
                        'entid'      => $entid,
                        'is_mastart' => $mastartId == $frameId ? 1 : 0,
                        'created_at' => now()->toDateTimeString(),
                    ];
                    if ($mastartId == $frameId) {
                        $update[] = ['user_id' => $item];
                    }
                }
            }
        }

        $res = $this->transaction(function () use ($update, $data, $frameServices) {
            if ($update) {
                foreach ($update as $item) {
                    $frameServices->update([
                        'user_id' => $item['user_id'],
                    ], ['is_mastart' => 0]);
                }
            }
            $frameServices->insert($data);
            return true;
        });
        return $res && Task::deliver(new FrameCensusTask()) && Cache::tags([CacheEnum::TAG_FRAME, CacheEnum::TAG_APPROVE])->flush();
    }

    /**
     * 从企业中批量删除用户.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function deleteBatchUserCard(array $ids, int $entId): bool
    {
        foreach ($ids as $id) {
            $this->deleteUserCard((int) $id, $entId);
        }

        return true;
    }

    /**
     * 从企业中删除用户.
     * @return bool
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function deleteUserCard(int $id, int $entid)
    {
        if (! ($userCardInfo = $this->dao->get($id))) {
            throw $this->exception('删除的用户名片不存在');
        }
        if ($userCardInfo->uid) {
            throw $this->exception('已激活的用户不可删除');
        }

        $userInfo = app()->get(CompanyUserService::class)->get(['uid' => $userCardInfo->uid, 'card_id' => $id], ['*'], ['frames']);
        if (! $userInfo) {
            throw $this->exception('用户关联企业信息不存在');
        }
        if ($userInfo->ident) {
            throw $this->exception('您不能删除企业的创始人');
        }

        if ($userCardInfo->uid && app()->get(CompanyService::class)->exists(['uid' => $userCardInfo->uid, 'id' => $entid])) {
            throw $this->exception('不能删除企业的创始人');
        }

        $frameIds = $userInfo->frames ? array_column($userInfo->frames->toArray(), 'id') : [];

        $res = $this->transaction(function () use ($frameIds, $entid, $userCardInfo, $userInfo) {
            Event::dispatch('enterprise.user.CardDeleteSuccess', [$userInfo->id, $userCardInfo->id, $userInfo->uid, $entid, $frameIds]);
            // 删除eb_enterprise_user_card企业用户信息
            $userCardInfo->delete();
            // 删除eb_user_enterprise用户和企业关联表信息
            $userInfo->delete();
            return true;
        });
        return $res && Cache::tags([CacheEnum::TAG_FRAME, CacheEnum::TAG_APPROVE])->flush();
    }

    /**
     * 获取修改表单内容.
     * @param mixed $type
     * @param mixed $data
     */
    public function getUpdateData($type, $data): array
    {
        $result = match ($type) {
            'basic' => [
                'name'     => $data['name'],
                'phone'    => $data['phone'],
                'position' => $data['position'],
                'letter'   => strtoupper(Pinyin::nameAbbr(trim($data['name']))[0]) ?: '',
            ],
            'staff' => [
                'is_part'            => $data['is_part'],
                'status'             => $data['status'],
                'work_time'          => $data['work_time'],
                'trial_time'         => $data['trial_time'],
                'formal_time'        => $data['formal_time'],
                'treaty_time'        => $data['treaty_time'],
                'interview_date'     => $data['interview_date'],
                'interview_position' => $data['interview_position'],
                'quit_time'          => $data['quit_time'],
                'type'               => $data['type'],
            ],
            'user' => [
                'card_id'    => $data['card_id'],
                'sex'        => $data['sex'],
                'birthday'   => $data['birthday'],
                'age'        => $data['age'],
                'nation'     => $data['nation'],
                'politic'    => $data['politic'],
                'work_years' => $data['work_years'],
                'native'     => $data['native'],
                'address'    => $data['address'],
                'marriage'   => $data['marriage'],
                'email'      => $data['email'],
                'name'       => $data['name'],
                'phone'      => $data['phone'],
            ],
            'education' => [
                'education'     => $data['education'],
                'acad'          => $data['acad'],
                'graduate_date' => $data['graduate_date'],
                'graduate_name' => $data['graduate_name'],
            ],
            'bank' => [
                'bank_num'  => $data['bank_num'],
                'bank_name' => $data['bank_name'],
            ],
            'social' => [
                'social_num' => $data['social_num'],
                'fund_num'   => $data['fund_num'],
            ],
            'spare' => [
                'spare_name' => $data['spare_name'],
                'spare_tel'  => $data['spare_tel'],
            ],
            'card' => [
                'card_front'      => $data['card_front'],
                'card_both'       => $data['card_both'],
                'education_image' => $data['education_image'],
                'acad_image'      => $data['acad_image'],
            ],
            'all' => [
                'letter'             => $data['name'] ? (strtoupper(Pinyin::nameAbbr(trim($data['name']))[0]) ?: '') : '',
                'work_time'          => $data['work_time'],
                'trial_time'         => $data['trial_time'],
                'formal_time'        => $data['formal_time'],
                'treaty_time'        => $data['treaty_time'],
                'interview_position' => $data['position'],
                'card_id'            => $data['card_id'],
                'sex'                => $data['sex'],
                'birthday'           => $data['birthday'],
                'age'                => $data['age'],
                'nation'             => $data['nation'],
                'politic'            => $data['politic'],
                'work_years'         => $data['work_years'],
                'native'             => $data['native'],
                'address'            => $data['address'],
                'marriage'           => $data['marriage'],
                'email'              => $data['email'],
                'education'          => $data['education'],
                'acad'               => $data['acad'],
                'graduate_date'      => $data['graduate_date'],
                'graduate_name'      => $data['graduate_name'],
                'bank_num'           => ! empty($data['bank_num']) ? $data['bank_num'] : '',
                'bank_name'          => ! empty($data['bank_name']) ? $data['bank_name'] : '',
                'social_num'         => ! empty($data['social_num']) ? $data['social_num'] : '',
                'fund_num'           => ! empty($data['fund_num']) ? $data['fund_num'] : '',
                'spare_name'         => ! empty($data['spare_name']) ? $data['spare_name'] : '',
                'spare_tel'          => ! empty($data['spare_tel']) ? $data['spare_tel'] : '',
                'card_front'         => ! empty($data['card_front']) ? $data['card_front'] : '',
                'card_both'          => ! empty($data['card_both']) ? $data['card_both'] : '',
                'education_image'    => ! empty($data['education_image']) ? $data['education_image'] : '',
                'acad_image'         => ! empty($data['acad_image']) ? $data['acad_image'] : '',
            ],
            'photo' => [
                'photo' => $data['photo'],
            ],
            default => ['sort' => $data['sort']],
        };
        $result['photo'] = $data['photo'];

        return $result;
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
        if (! $this->dao->exists(['phone' => $phone])) {
            throw $this->exception('无效的账号信息!');
        }
        $info = $this->dao->get(['phone' => $phone]);
        if ($info['status'] == 2) {
            throw $this->exception('该账号暂时无法登陆，请联系管理员激活');
        }
    }

    /**
     * 获取中间表用户ID.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getUserIdsByName(string $name): array
    {
        $userIds = [];
        if (! $name) {
            return $userIds;
        }
        $cardList = $this->dao->select(['name_like' => $name], ['id'], ['userEnt' => fn ($query) => $query->select(['id', 'card_id'])]);
        if ($cardList) {
            $userIds = array_column(array_column($cardList->toArray(), 'user_ent'), 'id');
        }
        return $userIds;
    }

    /**
     * 获取状态正常的员工.
     * @param mixed $field
     * @param mixed $entId
     * @param mixed $types
     * @param mixed $uid
     * @return array
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getNormalUid($entId, $types = true, $uid = true, $field = 'id')
    {
        $cardIds = $this->dao->column(['uid' => $uid, 'status' => 1, 'entid' => $entId, 'types' => [1, 2, 3]], 'id');
        if ($types) {
            return $cardIds;
        }
        if ($cardIds) {
            return array_unique(app()->get(CompanyUserService::class)->column(['card_id' => $cardIds], $field));
        }
        return [];
    }

    /**
     * 设置部门数据.
     */
    public function setFrameData(array $frameData, array $otherData): void
    {
        if (! $frameData && ! $otherData) {
            return;
        }
        $this->frameData = array_unique(array_filter(array_merge(array_column($frameData, 'id'), array_map('intval', $otherData))));
    }

    /**
     * 获取企业用户相关信息.
     *
     * @param mixed $cardId
     * @param mixed $field
     * @return array
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getEntUserInfo(int $entId, $cardId, $field = ['*'])
    {
        if (! $entId) {
            throw $this->exception('企业ID不能为空');
        }
        if (! $cardId) {
            throw $this->exception('用户ID不能为空');
        }
        $userInfo = toArray($this->dao->get($cardId, $field, [
            'job' => fn ($q) => $q->select(['id', 'name', 'rank_id', 'card_id']),
        ]));
        if (! $userInfo || $userInfo['entid'] != $entId) {
            throw $this->exception('用户信息不存在');
        }

        $userInfo['frames'] = app()->get(FrameAssistService::class)->getUserFrames($userInfo['uid'], $entId);
        return $userInfo;
    }

    /**
     * 判断用户状态
     * @param string $entId
     * @param mixed $userId
     * @return bool
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getUserStatus($userId, $entId = '')
    {
        $userEnterpriseService = app()->get(CompanyUserService::class);
        if (strlen($userId) == 32) {
            $id = $userEnterpriseService->value(['uid' => $userId, 'entid' => $entId], 'card_id');
        } else {
            $id = $userEnterpriseService->value($userId, 'card_id');
        }
        $card = toArray($this->dao->get($id, ['uid', 'status', 'type']));
        if (! $card || ! $card['status'] || ! $card['uid'] || ! in_array($card['type'], $this->normalType)) {
            return false;
        }
        return true;
    }

    /**
     * 业务员列表.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getSalesman(string $uuid, int $entId): array
    {
        return Cache::remember(md5('salesman_' . $uuid), 300, function () use ($uuid, $entId) {
            $uid = app()->get(FrameService::class)->subUserInfo($uuid, $entId, withAdmin: true, withSelf: true);
            if (! $uid) {
                return [];
            }
            return app()->get(CompanyUserService::class)->getCardWithClient($uid, $entId);
        });
    }

    /**
     * TODO 邀请完善信息.
     * @return array|string[]
     * @throws \ReflectionException
     * @throws BindingResolutionException
     */
    public function sendPerfect(string $uuId, int $entId, int $id = 0, bool $isInterview = false): array
    {
        $uniqued    = md5($entId . '_perfect_' . $id . time());
        $url        = sys_config('site_url') . config('app.ent.path') . '/user/resume?uni=' . $uniqued;
        $cardPerfec = app()->get(UserCardPerfectDao::class);
        $save       = [
            'entid'     => $entId,
            'uniqued'   => $uniqued,
            'total'     => -1,
            'types'     => 1,
            'fail_time' => now()->addDays(8)->toDateTimeString(),
        ];
        if (! $id) {
            if ($isInterview) {
                $save['types'] = 0;
            } else {
                $frameId = app()->get(FrameService::class)->getDefaultFrame();
                $url     = $this->addMember([
                    'frame_id'  => $frameId,
                    'type'      => 2,
                    'time'      => Carbon::now()->addDays(7)->toDateTimeString(),
                    'is_verify' => 0,
                ], $entId, $uuId, $uniqued);
            }
            $save['fail_time'] = now()->addDays(7)->toDateTimeString();
            $data              = ['url' => $url, 'message' => ''];
        } else {
            $cardInfo = toArray($this->dao->get($id));
            if (! $cardInfo) {
                throw $this->exception('未找到相关员工档案信息');
            }
            $save['card_id'] = $id;
            $save['total']   = 1;
            if ($cardInfo['uid']) {
                if ($cardPerfec->exists(['time' => 'today', 'uid' => $cardInfo['uid']])) {
                    throw $this->exception('每日仅可发送一次邀请！');
                }
                $save['uid'] = $cardInfo['uid'];
                $entInfo     = app()->get(CompanyService::class)->get($entId);
                // 发送邀请
                Task::deliver(new PerfectInfoRemind($cardInfo, $entInfo));
                $data = ['url' => '', 'message' => '邀请完善信息已发出，等待用户完善'];
            } else {
                $userId  = card_to_uid($id);
                $frameId = app()->get(FrameAssistService::class)->value(['is_mastart' => 1, 'user_id' => $userId], 'frame_id') ?: 0;
                $url     = $this->addMember([
                    'frame_id'  => $frameId,
                    'type'      => 2,
                    'time'      => Carbon::now()->addDays(7)->toDateTimeString(),
                    'is_verify' => 0,
                ], $entId, $uuId, $uniqued);
                $data = ['url' => $url, 'message' => ''];
            }
        }
        $cardPerfec->create($save);
        return $data;
    }

    /**
     * 企业中添加用户.
     *
     * @return mixed
     * @throws BindingResolutionException
     */
    public function addMember(array $data, int $entid, string $uid, string $linkKey = '')
    {
        $userService = app()->get(UserService::class);
        switch ($data['type']) {
            case 2:
                if (! $data['time']) {
                    throw $this->exception('请选择失效时间');
                }
                $data = [
                    'entid'       => $entid,
                    'send_uid'    => $this->uuId(false),
                    'frame_id'    => $data['frame_id'],
                    'is_verify'   => $data['is_verify'],
                    'perfect_key' => $linkKey,
                    'fail_time'   => Carbon::parse($data['time'])->toDateTimeString(),
                ];
                $data['uniqued'] = md5(json_encode($data) . time());
                $inviteService   = app()->get(CompanyInviteService::class);
                $res             = $this->transaction(function () use ($inviteService, $data, $entid) {
                    if ($inviteService->create($data)) {
                        return sys_config('site_url') . config('app.ent.path') . '?enterprise=' . $entid . '&invitation=' . $data['uniqued'];
                    }
                    throw $this->exception('生成邀请链接失败！');
                });
                break;
            default:
                if ($data['uid']) {
                    $user = $userService->get($data['uid']);
                } else {
                    $user = $userService->get(['real_name' => $data['name'], 'phone' => $data['phone']]);
                }
                if (! $user) {
                    throw $this->exception('未找到用户信息！');
                }
                if ($user->uid == $uid) {
                    throw $this->exception('不能邀请自己加入');
                }
                if ($this->dao->setDefaultWhere(['entid' => $entid])->exists(['uid' => $user['uid']])) {
                    throw $this->exception('用户已在企业中，请勿重复操作！');
                }
                $save = [
                    'uid'    => $user['uid'],
                    'entid'  => $entid,
                    'status' => 0,
                ];
                $frameId      = $data['frame_id'];
                $applyService = app()->get(UserApplyService::class);
                if ($applyService->exists(['uid' => $user['uid'], 'entid' => $entid, 'status' => [1, -1], 'verify' => [1, 0]])) {
                    throw $this->exception('您的邀请已发出,等待用户确认!');
                }
                $res = $this->transaction(function () use ($save, $applyService, $frameId) {
                    $res = $applyService->create([
                        'uid'        => $save['uid'],
                        'send_uid'   => $this->uuId(false),
                        'entid'      => $save['entid'],
                        'frame_id'   => $frameId,
                        'created_at' => now()->toDateTimeLocalString(),
                        'status'     => -1,
                    ]);
                    if (! $res) {
                        throw $this->exception('邀请失败');
                    }

                    return $res;
                });
                // 发送消息
                event('enterprise.invitation.remind', [$entid, $user['uid'], $res->id]);
                break;
        }

        return $res;
    }

    /**
     * 获取入职月份数量.
     * @return mixed
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getMonthNumberByUserWorkTime(int $id, bool $absolute = false): int
    {
        if (! ($userCardInfo = $this->dao->get($id, ['work_time']))) {
            throw $this->exception('用户名片不存在');
        }
        $tz = config('app.timezone');
        return Carbon::parse($userCardInfo->work_time, $tz)->diffInMonths(Carbon::now($tz), $absolute);
    }

    /**
     * 获取离职人员ID.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function getLeaveUid(): array
    {
        $cardIds = $this->dao->column(['status' => 1, 'entid' => 1, 'type' => 0], 'id');
        if ($cardIds) {
            return app()->get(CompanyUserService::class)->column(['card_id' => $cardIds], 'id');
        }
        return [];
    }
}
