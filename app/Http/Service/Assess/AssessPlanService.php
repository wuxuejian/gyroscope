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

namespace App\Http\Service\Assess;

use App\Constants\CacheEnum;
use App\Http\Dao\Access\AssessPlanDao;
use App\Http\Dao\BaseDao;
use App\Http\Service\Admin\AdminInfoService;
use App\Http\Service\Admin\AdminService;
use App\Http\Service\BaseService;
use App\Http\Service\Frame\FrameAssistService;
use App\Http\Service\Frame\FrameService;
use App\Http\Service\Message\MessageService;
use App\Http\Service\Other\TaskService;
use App\Http\Service\User\UserRemindLogService;
use App\Task\message\MessageSendTask;
use crmeb\options\TaskOptions;
use crmeb\utils\MessageType;
use crmeb\utils\Regex;
use Hhxsv5\LaravelS\Swoole\Task\Task;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * 考核计划
 * Class AssessPlanService.
 * @method BaseDao setDefaultWhere(array $where)
 */
class AssessPlanService extends BaseService
{
    public function __construct(AssessPlanDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 考核计划列表.
     * @param array|string[] $field
     * @param null $sort
     * @throws BindingResolutionException
     */
    public function getList(array $where, array $field = ['*'], $sort = null, array $with = []): array
    {
        $planUserService = app()->get(AssessPlanUserService::class);
        if (count($where['check_uid'])) {
            $where['check_uid'] = $planUserService->column(['check_uid' => $where['check_uid'], 'entid' => 1], 'planid');
        } else {
            unset($where['check_uid']);
        }
        if (count($where['test_uid'])) {
            $where['test_uid'] = $planUserService->column(['test_uid' => $where['test_uid'], 'entid' => 1], 'planid');
        } else {
            unset($where['test_uid']);
        }
        return parent::getList($where, $field, $sort, $with + [
            'test' => function ($query) {
                $query->select(['test_uid', 'planid', 'card_id'])->with([
                    'card' => function ($query) {
                        $query->select(['name', 'id']);
                    },
                ]);
            },
        ]);
    }

    /**
     * 获取已启用的考核计划.
     * @return array|Collection
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getEnablePeriod(int $uid)
    {
        return $this->dao->select(['entid' => 1, 'status' => 1], ['period', 'id'])->each(function ($item) use ($uid) {
            switch ($item['period']) {
                case 1:
                    $item['name'] = '周考核';
                    break;
                case 2:
                    $item['name'] = '月考核';
                    break;
                case 3:
                    $item['name'] = '年考核';
                    break;
                case 4:
                    $item['name'] = '半年考核';
                    break;
                case 5:
                    $item['name'] = '季度考核';
                    break;
            }
            $item['test'] = $this->dao->getPlanUser($item['id'], $uid);
        })?->toArray() ?: [];
    }

    /**
     * 考核计划信息.
     * @param int $id 考核周期
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function resourceEdit(int $id, array $other = []): array
    {
        $entid    = 1;
        $planInfo = $this->dao->get(['period' => $id, 'entid' => $entid], ['*'], [
            'test', 'testFrame',
        ]);
        if (! $planInfo) {
            $planInfo = $this->dao->create([
                'period'       => $id,
                'entid'        => $entid,
                'make_type'    => 'before',
                'make_day'     => 3,
                'eval_type'    => 'before',
                'eval_day'     => 2,
                'verify_type'  => 'after',
                'verify_day'   => 7,
                'status'       => 1,
                'assess_type'  => 0,
                'create_time'  => 1,
                'create_month' => 0,
            ]);
            return $planInfo->load([
                'test' => fn ($q) => $q->where('assess_plan_user.entid', $entid), 'testFrame',
            ])->toArray();
        }
        return $planInfo->toArray();
    }

    /**
     * 修改考核计划.
     * @param int $id 考核周期
     * @return mixed
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function resourceUpdate($id, array $data)
    {
        $testUser   = $data['test'];
        $testFrame  = $data['test_frame'];
        $entId      = 1;
        $assessType = $data['assess_type']; // 类型：1、部门；0、人员
        unset($data['test'], $data['test_frame']);
        $plan = $this->dao->get(['id' => $id, 'entid' => $entId]);
        if (! $plan) {
            $plan = $this->dao->create([
                'period' => $data['period'],
                'entid'  => 1,
                'status' => $data['status'],
            ]);
        }
        if ($assessType) {
            if (! $testFrame) {
                $testFrame = app()->get(FrameService::class)->column(['entid' => $entId, 'pid' => 0], 'id');
            }
            $res = $this->transaction(function () use ($testFrame, $plan, $entId, $data) {
                $res1        = $this->dao->update($plan->id, $data);
                $assessFrame = app()->get(AssessFrameService::class);
                $assessFrame->delete(['entid' => $entId, 'planid' => $plan->id]);
                $frames = [];
                foreach ($testFrame as $item) {
                    $frames[] = [
                        'planid'        => $plan->id,
                        'test_frame_id' => $item,
                        'entid'         => $entId,
                        'created_at'    => now()->toDateTime(),
                    ];
                }
                $res2 = $assessFrame->insert($frames);
                return $res1 && $res2;
            });
        } else {
            if (! $testUser) {
                $testUser = app()->get(AdminService::class)->column(['status' => 1], 'id');
            }
            $res = $this->transaction(function () use ($testUser, $plan, $entId, $data) {
                $res1       = $this->dao->update($plan->id, $data);
                $assessUser = app()->get(AssessPlanUserService::class);
                $assessUser->delete(['entid' => $entId, 'planid' => $plan->id]);
                $user = [];
                foreach ($testUser as $item) {
                    $user[] = [
                        'test_uid'   => $item,
                        'planid'     => $plan->id,
                        'entid'      => $entId,
                        'created_at' => now()->toDateTime(),
                    ];
                }
                $res2 = $assessUser->insert($user);
                return $res1 && $res2;
            });
        }
        $res && Cache::tags([CacheEnum::TAG_ASSESS])->flush();
        return true;
    }

    /**
     * 修改状态
     * @param mixed $id
     * @param mixed $status
     * @return int
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function resourceShowUpdate($id, $status)
    {
        $data = $this->dao->get($id);
        if (! $data) {
            throw $this->exception('操作失败，记录不存在');
        }
        if (! $status['status']) {
            $res = $this->dao->update(['id' => $id], ['status' => 0]);
        } else {
            $res = $this->dao->update(['id' => $id], ['status' => 1]);
        }
        Cache::tags([CacheEnum::TAG_ASSESS])->flush();
        return $res;
    }

    /**
     * 获取绩效用户列表.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function getPlanUserList(int $id, array $where)
    {
        $normalUid = app()->get(AdminInfoService::class)->column(['type' => [1, 2, 3]], 'id');
        $planUid   = $this->getPlanUid($id, $normalUid);
        if ($where['uni']) {
            if ($where['frame_id']) {
                $frameUid    = app()->get(FrameAssistService::class)->column(['frame_id' => $where['frame_id']], 'user_id');
                $where['id'] = array_intersect($planUid, $frameUid);
            } else {
                $where['id'] = $planUid;
            }
        } else {
            $where['id'] = array_diff($normalUid, $planUid);
            if ($where['frame_id']) {
                $frameUid    = app()->get(FrameAssistService::class)->column(['frame_id' => $where['frame_id']], 'user_id');
                $where['id'] = array_intersect($frameUid, $where['id']);
            }
        }
        unset($where['frame_id'],$where['uni']);
        $listData = app()->get(AdminService::class)->getList(
            $where,
            ['id', 'name', 'avatar', 'job'],
            with: [
                'job' => fn ($q) => $q->select(['id', 'name']),
                'frame',
                'super',
            ]
        );
        $frameService = app()->get(FrameService::class);
        foreach ($listData['list'] as &$value) {
            $value['super'] = $value['super'] ?: $frameService->getLevelSuperUser((int) $value['id']);
        }
        return $listData;
    }

    /**
     * 获取参与绩效用户ID.
     * @param mixed $id
     * @param mixed $normalUid
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function getPlanUid($id, $normalUid = [])
    {
        $plan = $this->dao->get($id)?->toArray();
        if (! $plan || ! $plan['status']) {
            return [];
        }

        if ($plan['assess_type']) {
            $planFrameIds = app()->get(AssessFrameService::class)->column(['planid' => $id], 'test_frame_id');
            $planUid      = app()->get(FrameService::class)->scopeUser($planFrameIds);
        } else {
            $planUid = app()->get(AssessPlanUserService::class)->column(['planid' => $id], 'test_uid');
            if (! $normalUid) {
                $normalUid = app()->get(AdminService::class)->column(['status' => 1], 'id');
            }
            $planUid = array_intersect($planUid, $normalUid);
        }
        return $planUid;
    }

    /**
     * 关闭任务
     * @param mixed $data
     * @return bool
     * @throws BindingResolutionException
     */
    public function closeTask($data)
    {
        $taskService = app()->get(TaskService::class);
        if ($data['uniqued']) {
            $taskService->update(['uniqued' => $data['uniqued']], ['delete' => 1]);
        }
        return true;
    }

    /**
     * 开启考核计划.
     * @param mixed $id
     * @param mixed $data
     * @return mixed
     * @throws BindingResolutionException
     */
    public function openTask($id, $data)
    {
        // 设置任务参数
        $optione            = new TaskOptions($data['name']);
        $optione->persist   = 1;
        $optione->className = 'user.assess';
        $optione->entid     = $data['entid'];
        $optione->uniqued   = md5($data['entid'] . time());
        // 设置生成时间
        [$createMonth, $createDay, $time, $week] = $this->getTimeData($data['make_type'], $data['period'], $data['make_day']);
        [$hour, $minute, $secound]               = explode(':', $time);
        // 设置执行周期
        switch ($data['period']) {
            case 1:// 周考核
                $optione->period         = 'week';
                $optione->intervalWeek   = $week;
                $optione->intervalHour   = (int) mt_rand(0, 6);
                $optione->intervalMinute = (int) $minute;
                $optione->intervalSecond = (int) $secound;
                break;
            case 2:// 月考核
                $optione->period         = 'month';
                $optione->intervalDay    = (int) $createDay;
                $optione->intervalHour   = (int) $hour;
                $optione->intervalMinute = (int) $minute;
                $optione->intervalSecond = (int) $secound;
                break;
            case 3:// 年考核
                $optione->period         = 'year';
                $optione->intervalMonth  = (int) $createMonth;
                $optione->intervalDay    = (int) $createDay;
                $optione->intervalHour   = (int) $hour;
                $optione->intervalMinute = (int) $minute;
                $optione->intervalSecond = (int) $secound;
                break;
            case 4:// 季度考核
                $optione->period         = 'year';
                $optione->intervalMonth  = (int) $createMonth;
                $optione->intervalDay    = (int) $createDay;
                $optione->intervalHour   = (int) $hour;
                $optione->intervalMinute = (int) $minute;
                $optione->intervalSecond = (int) $secound;
                break;
            case 5:// 半年考核
                $optione->period         = 'year';
                $optione->intervalMonth  = (int) $createMonth;
                $optione->intervalDay    = (int) $createDay;
                $optione->intervalHour   = (int) $hour;
                $optione->intervalMinute = (int) $minute;
                $optione->intervalSecond = (int) $secound;
                break;
        }
        $taskService = app()->get(TaskService::class);
        return $this->transaction(function () use ($data, $taskService, $optione, $id) {
            if ($data['uniqued']) {
                $taskService->update(['uniqued' => $data['uniqued']], ['delete' => now()->toDateTimeString()]);
            }
            $this->dao->update(['id' => $id], ['uniqued' => $optione->uniqued]);
            $optione->setParameter($id);
            return $taskService->addTask($optione);
        });
    }

    /**
     * 绩效提醒定时任务定时任务
     * @param mixed $now
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function timer(int $entid, $now)
    {
        $list = $this->dao->select(['status' => 1, 'entid' => $entid], ['id', 'status', 'create_time', 'create_month', 'entid', 'period'])->toArray();
        foreach ($list as $item) {
            $entid = (int) $item['entid'];
            switch ((int) $item['period']) {
                case 1:// 周
                    if ($now->dayOfWeek === (int) $item['create_time']) {
                        $this->entAssessRemind(
                            $item['id'],
                            MessageType::ASSESS_TARGET_TYPE,
                            $entid,
                            $now->year,
                            0,
                            $now->week,
                            0,
                            0,
                            (int) $item['period']
                        );
                    }
                    break;
                case 2:// 月
                    $createTime = (int) $item['create_time'];
                    // 设置的日志大于当月最大日期等于当个月最大日期
                    if ($createTime > $now->daysInMonth) {
                        $createTime = $now->daysInMonth;
                    }
                    if ($now->day === $createTime) {
                        $this->entAssessRemind(
                            $item['id'],
                            MessageType::ASSESS_TARGET_TYPE,
                            $entid,
                            $now->year,
                            $now->month,
                            0,
                            0,
                            0,
                            (int) $item['period']
                        );
                    }
                    break;
                case 3:// 年
                    $createMonth = (int) $item['create_month'];
                    $createTime  = (int) $item['create_time'];
                    // 设置的日志大于当月最大日期等于当个月最大日期
                    if ($createTime > $now->daysInMonth) {
                        $createTime = $now->daysInMonth;
                    }
                    if ($now->month == $createMonth && $now->day === $createTime) {
                        $this->entAssessRemind(
                            $item['id'],
                            MessageType::ASSESS_TARGET_TYPE,
                            $entid,
                            $now->year,
                            $now->month,
                            0,
                            0,
                            0,
                            (int) $item['period']
                        );
                    }
                    break;
                case 4:// 半年
                    $createMonth = (int) $item['create_month'];
                    $createTime  = (int) $item['create_time'];
                    // 设置的日志大于当月最大日期等于当个月最大日期
                    if ($createTime > $now->daysInMonth) {
                        $createTime = $now->daysInMonth;
                    }
                    if ($now->month == $createMonth && $now->day === $createTime) {
                        $time = get_start_and_end_time(4);
                        $this->entAssessRemind(
                            $item['id'],
                            MessageType::ASSESS_TARGET_TYPE,
                            $entid,
                            $now->year,
                            0,
                            0,
                            0,
                            $now->quarter,
                            (int) $item['period'],
                            $time
                        );
                    }
                    break;
                case 5:// 季度
                    $createMonth = (int) $item['create_month'];
                    $createTime  = (int) $item['create_time'];
                    // 设置的日志大于当月最大日期等于当个月最大日期
                    if ($createTime > $now->daysInMonth) {
                        $createTime = $now->daysInMonth;
                    }
                    if ($now->month == $createMonth && $now->day === $createTime) {
                        $this->entAssessRemind(
                            $item['id'],
                            MessageType::ASSESS_TARGET_TYPE,
                            $entid,
                            $now->year,
                            0,
                            0,
                            0,
                            $now->quarter,
                            (int) $item['period']
                        );
                    }
                    break;
            }
        }
    }

    /**
     * @param null|mixed $time
     * @return bool|void
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function entAssessRemind(int $id, string $type, int $entid, int $year, int $month = 0, int $week = 0, int $day = 0, int $quarter = 0, int $period = 1, $time = null)
    {
        // 获取当前被考核的成员
        $testUid = app()->get(AssessPlanUserService::class)->setEntValue($entid)->column(['planid' => $id], 'test_uid');
        if (! $testUid) {
            return;
        }
        // 查找已经创建绩效的成员
        $userAssessUids = app()->get(UserAssessService::class)->column([
            'period'   => $period,
            'entid'    => $entid,
            'planid'   => $id,
            'test_uid' => $testUid,
            'time'     => implode('-', get_start_and_end_time($period)),
        ], 'test_uid');
        if (empty($userAssessUids)) {
            return;
        }
        // 剔除已创建的成员
        $testUid = array_diff($testUid, $userAssessUids);
        // 上级主管的uid
        $frameService = app()->get(FrameService::class);
        $uids         = [];
        foreach ($testUid as $item) {
            $uids[] = $frameService->getLevelSuper($item);
        }
        $uids = array_unique($uids);
        // 获取没有提醒的上级主管uid
        $remindService = app()->get(UserRemindLogService::class);
        $remindUids    = $remindService->column([
            'year'        => $year,
            'relation_id' => $id,
            'month'       => $month,
            'week'        => $week,
            'quarter'     => $quarter,
            'day'         => $day,
            'entid'       => $entid,
            'uids'        => $uids,
            'remind_type' => $type,
            'time'        => $time,
        ], 'user_id');
        // 没有发送过消息的用户
        $unRemindUids = array_diff($uids, $remindUids);
        if (! $unRemindUids) {
            return;
        }
        // 获取用户手机号
        $userList = app()->get(AdminService::class)->select([
            'ids'    => $unRemindUids,
            'status' => 1,
        ], with: ['frame']);

        $toUsers = [];
        foreach ($userList as $item) {
            $toUsers[] = [
                'to_uid' => [
                    'to_uid' => $item['id'],
                    'phone'  => $item['phone'],
                ],
                'frame_name' => $item['frame']['name'],
            ];
        }
        $timeStr = match ($period) {
            1       => '周',
            2       => '月',
            3       => '年',
            4       => '季度',
            5       => '半年',
            default => ''
        };
        $remind = [];

        foreach ($toUsers as $item) {
            $task = new MessageSendTask(
                entid: $entid,
                i: $entid,
                type: $type,
                toUid: $item['to_uid'],
                params: [
                    '负责部门' => $item['frame_name'],
                    '考核类型' => $timeStr . '考核',
                    '时间1'  => $timeStr,
                    '时间2'  => $timeStr,
                    '时间3'  => $timeStr,
                ]
            );
            Task::deliver($task);

            $remind[] = [
                'remind_type' => $type,
                'user_id'     => $item['to_uid']['to_uid'],
                'entid'       => $entid,
                'relation_id' => $id,
                'year'        => now()->year,
                'day'         => now()->day,
                'month'       => now()->month,
                'week'        => now()->week,
                'quarter'     => now()->quarter,
                'created_at'  => date('Y-m-d H:i:s'),
            ];
        }
        if ($remind) {
            $remindService->insert($remind);
        }

        return true;
    }

    /**
     * 获取开启考核计划的企业id.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getEntPlanList(): array
    {
        return Cache::tags([CacheEnum::TAG_ASSESS])->remember(
            'ent_plan_list',
            (int) sys_config('system_cache_ttl', 3600),
            fn () => array_unique(array_column($this->dao->select(['status' => 1], ['entid'])->toArray(), 'entid'))
        );
    }

    /**
     * @throws BindingResolutionException
     * @throws InvalidArgumentException
     * @throws \ReflectionException
     */
    public function abnormalTimer(int $entId, array $userIds, int $period)
    {
        // 没有开启不需要提醒
        $planid = $this->dao->value(['entid' => $entId, 'period' => $period, 'status' => 1], 'id');
        if (! $planid) {
            return;
        }
        $userAssessList = app()->get(UserAssessService::class)->select([
            'test_uid' => $userIds,
            'entid'    => $entId,
            'period'   => $period,
            'time'     => 'year',
        ])?->toArray();

        /** @var UserRemindLogService $service */
        $service = app()->get(UserRemindLogService::class);
        /** @var FrameService $frameService */
        $frameService = app()->get(FrameService::class);
        $userService  = app()->get(AdminService::class);
        /** @var MessageService $messageService */
        $messageService = app()->get(MessageService::class);
        $message        = $messageService->getMessageContent($entId, MessageType::ASSESS_ABNORMAK_TYPE);

        $remind = false;
        // 考核结束前，在设置的时间内提醒
        switch ($period) {
            case 1:
                $remind = now()->endOfWeek()->endOfDay()->day == now()->day && date('H:i') == $message['remind_time'] && $message['remind_time'];
                break;
            case 2:
                $remind = now()->endOfMonth()->endOfDay()->day == now()->day && date('H:i') == $message['remind_time'] && $message['remind_time'];
                break;
            case 3:
                $remind = now()->endOfYear()->endOfDay()->day == now()->day && date('H:i') == $message['remind_time'] && $message['remind_time'];
                break;
            case 5:
                $remind = now()->endOfQuarter()->endOfDay()->day == now()->day && date('H:i') == $message['remind_time'] && $message['remind_time'];
                break;
            case 4:
                if (date('m') <= 7) {
                    // 下半年
                    $remind = now()->endOfYear()->endOfDay()->day == now()->day && date('H:i') == $message['remind_time'] && $message['remind_time'];
                } else {
                    // 上半年
                    $remind = now()->setMonths(6)->endOfMonth()->endOfDay()->day == now()->day && date('H:i') == $message['remind_time'] && $message['remind_time'];
                }
                break;
        }

        $timeStr = match ($period) {
            1       => '周',
            2       => '月',
            3       => '年',
            4       => '半年',
            5       => '季度',
            default => ''
        };

        $messageUser = [];
        if ($userAssessList) {
            foreach ($userAssessList as $item) {
                $boole = false; // true需要提醒
                // 上级用户user_id
                $userId = $frameService->getLevelSuper($item['test_uid']);
                if (! $userId) {
                    continue;
                }
                switch ($item['period']) {
                    case 1:// 周
                        $startTime = now()->startOfWeek()->toDateTimeString();
                        $endTime   = now()->endOfWeek()->toDateTimeString();
                        // 没有在当前考核时间内创建绩效
                        $res = $item['created_at'] >= $startTime && $endTime >= $item['created_at'];
                        // 没有发送消息
                        $boole = ! $res && ! $service->exists(['relation_id' => $item['planid'], 'entid' => $entId, 'user_id' => $item['test_uid'], 'year' => now()->year, 'week' => now()->week, 'remind_type' => MessageType::ASSESS_ABNORMAK_TYPE]);
                        break;
                    case 2:// 月
                        $startTime = now()->startOfMonth()->toDateTimeString();
                        $endTime   = now()->endOfMonth()->toDateTimeString();
                        // 没有在当前考核时间内创建绩效
                        $res = $item['created_at'] >= $startTime && $endTime >= $item['created_at'];
                        // 没有发送消息
                        $boole = ! $res && ! $service->exists(['relation_id' => $item['planid'], 'entid' => $entId, 'user_id' => $item['test_uid'], 'year' => now()->year, 'month' => now()->month, 'remind_type' => MessageType::ASSESS_ABNORMAK_TYPE]);
                        break;
                    case 3:// 年
                        $startTime = now()->startOfYear()->toDateTimeString();
                        $endTime   = now()->endOfYear()->toDateTimeString();
                        // 没有在当前考核时间内创建绩效
                        $res = $item['created_at'] >= $startTime && $endTime >= $item['created_at'];
                        // 没有发送消息
                        $boole = ! $res && ! $service->exists(['relation_id' => $item['planid'], 'entid' => $entId, 'user_id' => $item['test_uid'], 'year' => now()->year, 'remind_type' => MessageType::ASSESS_ABNORMAK_TYPE]);
                        break;
                    case 5:// 季度
                        $startTime = now()->startOfQuarter()->toDateTimeString();
                        $endTime   = now()->endOfQuarter()->toDateTimeString();
                        // 没有在当前考核时间内创建绩效
                        $res = $item['created_at'] >= $startTime && $endTime >= $item['created_at'];
                        // 没有发送消息
                        $boole = ! $res && ! $service->exists(['relation_id' => $item['planid'], 'entid' => $entId, 'user_id' => $item['test_uid'], 'quarter' => now()->quarter, 'year' => now()->year, 'remind_type' => MessageType::ASSESS_ABNORMAK_TYPE]);
                        break;
                    case 4:// 半年
                        [$startTime, $endTime] = get_start_and_end_time(4);
                        // 没有在当前考核时间内创建绩效
                        $res = $item['created_at'] >= $startTime && $endTime >= $item['created_at'];
                        // 没有发送消息
                        $boole = ! $res && ! $service->exists(['relation_id' => $item['planid'], 'entid' => $entId, 'user_id' => $item['test_uid'], 'time' => $startTime . '-' . $endTime, 'year' => now()->year, 'remind_type' => MessageType::ASSESS_ABNORMAK_TYPE]);
                        break;
                }

                // 提醒消息
                if ($remind && $boole) {
                    [$start, $end] = get_start_and_end_time($item['period']);
                    $prames        = [
                        '被考核人部门' => $item['frame']['name'] ?? '',
                        '被考核人'   => $item['user_ent']['name'] ?? '',
                        '考核类型'   => $timeStr . '考核',
                        '考核周期'   => $timeStr,
                        '时间1'    => $timeStr,
                        '开始时间'   => $start,
                        '结束时间'   => $end,
                    ];

                    $phone = $userService->value($userId, 'phone');

                    $task = new MessageSendTask(
                        entid: $entId,
                        i: $entId,
                        type: MessageType::ASSESS_ABNORMAK_TYPE,
                        toUid: ['to_uid' => $userId, 'phone' => $phone],
                        params: $prames
                    );
                    Task::deliver($task);

                    $service->create([
                        'relation_id' => $item['planid'],
                        'remind_type' => MessageType::ASSESS_ABNORMAK_TYPE,
                        'user_id'     => $item['test_uid'],
                        'entid'       => $entId,
                        'year'        => now()->year,
                        'day'         => now()->day,
                        'month'       => now()->month,
                        'week'        => now()->week,
                        'quarter'     => now()->quarter,
                    ]);
                    unset($userId);
                }
            }

            // 没有在考核人中
            $newUserIds  = array_column($userAssessList, 'test_uid');
            $userIdsDiff = array_unique(array_diff($userIds, $newUserIds));

            // 没有创建绩效的人员
            if ($userIdsDiff && $remind) {
                foreach ($userIdsDiff as $value) {
                    $key = 'remind_log_' . $planid . '_' . $entId;
                    if (Cache::tags([CacheEnum::TAG_OTHER])->has($key)) {
                        continue;
                    }
                    // 发送过就不需要发送
                    $res = true;
                    switch ($period) {
                        case 1:
                            $res = $service->exists(['relation_id' => $planid, 'entid' => $entId, 'user_id' => $value, 'year' => now()->year, 'week' => now()->week, 'remind_type' => MessageType::ASSESS_ABNORMAK_TYPE]);
                            break;
                        case 2:
                            $res = $service->exists(['relation_id' => $planid, 'entid' => $entId, 'user_id' => $value, 'year' => now()->year, 'day' => now()->day, 'remind_type' => MessageType::ASSESS_ABNORMAK_TYPE]);
                            break;
                        case 3:
                            $res = $service->exists(['relation_id' => $planid, 'entid' => $entId, 'user_id' => $value, 'year' => now()->year, 'remind_type' => MessageType::ASSESS_ABNORMAK_TYPE]);
                            break;
                        case 5:
                            $res = $service->exists(['relation_id' => $planid, 'entid' => $entId, 'user_id' => $value, 'quarter' => now()->quarter, 'year' => now()->year, 'remind_type' => MessageType::ASSESS_ABNORMAK_TYPE]);
                            break;
                        case 4:
                            [$startTime, $endTime] = get_start_and_end_time(4);
                            $res                   = $service->exists(['relation_id' => $planid, 'entid' => $entId, 'user_id' => $value, 'time' => $startTime . '-' . $endTime, 'year' => now()->year, 'remind_type' => MessageType::ASSESS_ABNORMAK_TYPE]);
                            break;
                    }
                    if ($res) {
                        Cache::tags([CacheEnum::TAG_ASSESS])->set($key, true);
                        continue;
                    }

                    $uuid = uid_to_uuid($value);
                    // 上级用户user_id
                    $userId = $frameService->getUserFrameAdminList($uuid, false);
                    if (! $userId) {
                        continue;
                    }
                    $phone = $userService->value($userId, 'phone');

                    // 被考核人信息
                    $testUserInfo = $userService->getUserInfo($value);

                    $messageUser[] = [
                        'to_uid'    => $userId,
                        'test_uid'  => $value,
                        'phone'     => $phone,
                        'frameName' => $testUserInfo['frame']['name'] ?? '',
                        'name'      => $testUserInfo['card']['name'] ?? '',
                    ];
                }
            }
        } else {
            // 证明，本年度没有创建过绩效，所有的用户都需要提醒
            foreach ($userIds as $value) {
                $key = 'remind_log_' . $planid . '_' . $entId;
                if (Cache::tags([CacheEnum::TAG_OTHER])->has($key)) {
                    continue;
                }
                $res = true;
                switch ($period) {
                    case 1:
                        $res = $service->exists(['relation_id' => $planid, 'entid' => $entId, 'user_id' => $value, 'year' => now()->year, 'week' => now()->week, 'remind_type' => MessageType::ASSESS_ABNORMAK_TYPE]);
                        break;
                    case 2:
                        $res = $service->exists(['relation_id' => $planid, 'entid' => $entId, 'user_id' => $value, 'year' => now()->year, 'day' => now()->day, 'remind_type' => MessageType::ASSESS_ABNORMAK_TYPE]);
                        break;
                    case 3:
                        $res = $service->exists(['relation_id' => $planid, 'entid' => $entId, 'user_id' => $value, 'year' => now()->year, 'remind_type' => MessageType::ASSESS_ABNORMAK_TYPE]);
                        break;
                    case 5:
                        $res = $service->exists(['relation_id' => $planid, 'entid' => $entId, 'user_id' => $value, 'quarter' => now()->quarter, 'year' => now()->year, 'remind_type' => MessageType::ASSESS_ABNORMAK_TYPE]);
                        break;
                    case 4:
                        [$startTime, $endTime] = get_start_and_end_time(4);
                        $res                   = $service->exists(['relation_id' => $planid, 'entid' => $entId, 'user_id' => $value, 'time' => $startTime . '-' . $endTime, 'year' => now()->year, 'remind_type' => MessageType::ASSESS_ABNORMAK_TYPE]);
                        break;
                }
                // 发送过就不需要发送
                if ($res) {
                    Cache::tags([CacheEnum::TAG_ASSESS])->set($key, true);
                    continue;
                }
                // 被考核人信息
                $testUserInfo = $userService->get($value, with: ['frame'])?->toArray();
                // 上级用户user_id
                $userId = $frameService->getUserFrameAdminList($testUserInfo['uid'], false);
                if (! $userId) {
                    continue;
                }
                $phone = $userService->value($userId, 'phone');

                if ($remind) {
                    $messageUser[] = [
                        'to_uid'    => $userId,
                        'test_uid'  => $value,
                        'phone'     => $phone,
                        'frameName' => $testUserInfo['frame']['name'] ?? '',
                        'name'      => $testUserInfo['name'] ?? '',
                    ];
                }
            }
        }

        // 没有发送的用户
        if ($messageUser) {
            [$start, $end] = get_start_and_end_time($period);
            $remindLog     = [];
            foreach ($messageUser as $item) {
                $task = new MessageSendTask(
                    entid: $entId,
                    i: $entId,
                    type: MessageType::ASSESS_ABNORMAK_TYPE,
                    toUid: ['to_uid' => $item['to_uid'], 'phone' => $item['phone']],
                    params: [
                        '被考核人部门' => $item['frameName'] ?? '',
                        '被考核人'   => $item['name'] ?? '',
                        '考核类型'   => $timeStr . '考核',
                        '考核周期'   => $timeStr,
                        '时间1'    => $timeStr,
                        '开始时间'   => $start,
                        '结束时间'   => $end,
                    ]
                );
                Task::deliver($task);
                $remindLog[] = [
                    'remind_type' => MessageType::ASSESS_ABNORMAK_TYPE,
                    'user_id'     => $item['test_uid'],
                    'entid'       => $entId,
                    'relation_id' => $planid,
                    'year'        => now()->year,
                    'day'         => now()->day,
                    'month'       => now()->month,
                    'week'        => now()->week,
                    'quarter'     => now()->quarter,
                    'created_at'  => date('Y-m-d H:i:s'),
                ];
            }

            if ($remindLog) {
                $service->insert($remindLog);
            }
        }
    }

    /**
     * 解析时间.
     * @param string $type 类型：前后
     * @param int $period 周期：周、月、年
     * @param int $day 天数
     * @return array
     */
    protected function getTimeData(string $type, int $period, int $day)
    {
        switch ($period) {
            case 5:// 季度
                if ($type == 'before') {
                    $date = now()->startOfMonth()->subDays($day);
                } else {
                    $date = now()->startOfMonth();
                }
                if (! preg_match(Regex::DAT_TIME_RULE, $date->daysInMonth . ' ' . $date->toTimeString())) {
                    throw $this->exception('时间段格式错误,格式为:dd hh:ii:ss');
                }
                [$timeDay, $time] = explode(' ', $date->daysInMonth . ' ' . $date->toTimeString());
                $timeMonth        = 0;
                $timeWeek         = 1;
                break;
            case 4:// 半年
                if ($type == 'before') {
                    $date = now()->startOfMonth()->subDays($day);
                } else {
                    $date = now()->startOfMonth();
                }
                if (! preg_match(Regex::DAT_TIME_RULE, $date->daysInMonth . ' ' . $date->toTimeString())) {
                    throw $this->exception('时间段格式错误,格式为:dd hh:ii:ss');
                }
                [$timeDay, $time] = explode(' ', $date->daysInMonth . ' ' . $date->toTimeString());
                $timeMonth        = 0;
                $timeWeek         = 2;
                break;
            case 3:// 年
                if ($type == 'before') {
                    $date = now()->startOfYear()->subDays($day);
                } else {
                    $date = now()->startOfYear();
                }
                if (! preg_match(Regex::TIME_RULE, $date->month . '-' . $date->daysInMonth . ' ' . $date->toTimeString())) {
                    throw $this->exception('时间格式错误,格式为:mm-dd hh:ii:ss');
                }
                [$month, $time] = explode(' ', $date->month . '-' . $date->daysInMonth . ' ' . $date->toTimeString());
                if (strstr($month, '-') === false) {
                    throw $this->exception('请输入正确的时间');
                }
                [$timeMonth, $timeDay] = explode('-', $month);
                $timeWeek              = 0;
                break;
            case 2:// 月
                if ($type == 'before') {
                    $date = now()->startOfMonth()->subDays($day);
                } else {
                    $date = now()->startOfMonth();
                }
                if (! preg_match(Regex::DAT_TIME_RULE, $date->daysInMonth . ' ' . $date->toTimeString())) {
                    throw $this->exception('时间段格式错误,格式为:dd hh:ii:ss');
                }
                [$timeDay, $time] = explode(' ', $date->daysInMonth . ' ' . $date->toTimeString());
                $timeMonth        = 0;
                $timeWeek         = 0;
                break;
            default:// 周
                if ($type == 'before') {
                    $date = now()->startOfWeek()->subDays($day);
                } else {
                    $date = now()->startOfWeek();
                }
                if (! preg_match(Regex::HOUR_TIME_RULE, $date->toTimeString())) {
                    throw $this->exception('时间格式错误,格式为:hh:ii:ss');
                }
                $timeMonth = 0;
                $timeDay   = 0;
                $timeWeek  = $date->dayOfWeek + 1;
                $time      = $date->toTimeString();
                break;
        }
        return [$timeMonth, $timeDay, $time, $timeWeek];
    }
}
