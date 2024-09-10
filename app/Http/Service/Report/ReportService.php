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

namespace App\Http\Service\Report;

use App\Constants\DailyEnum;
use App\Constants\RuleEnum;
use App\Http\Contract\Schedule\ScheduleInterface;
use App\Http\Dao\Report\MemberDao;
use App\Http\Dao\Report\UserDailyDao;
use App\Http\Model\BaseModel;
use App\Http\Service\Admin\AdminInfoService;
use App\Http\Service\Admin\AdminService;
use App\Http\Service\Attach\AttachService;
use App\Http\Service\Attendance\AttendanceArrangeService;
use App\Http\Service\BaseService;
use App\Http\Service\Frame\FrameService;
use App\Http\Service\System\RolesService;
use App\Http\Service\User\UserRemindLogService;
use App\Task\message\DailyReportRemind;
use App\Task\message\MessageSendTask;
use App\Task\message\StatusChangeTask;
use App\Task\report\DailyCreateTask;
use Carbon\CarbonPeriod;
use crmeb\interfaces\ResourceServicesInterface;
use crmeb\services\FormService;
use crmeb\traits\service\ResourceServiceTrait;
use crmeb\utils\MessageType;
use Hhxsv5\LaravelS\Swoole\Task\Task;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * 汇报.
 */
class ReportService extends BaseService implements ResourceServicesInterface
{
    use ResourceServiceTrait;

    protected FormService $build;

    /**
     * @var Application|mixed|Repository
     */
    private mixed $timeZone;

    private MemberDao $memberDao;

    /**
     * ReportService constructor.
     */
    public function __construct(UserDailyDao $dao, FormService $build, MemberDao $memberDao)
    {
        $this->dao       = $dao;
        $this->build     = $build;
        $this->memberDao = $memberDao;
        $this->timeZone  = config('app.timezone');
    }

    /**
     * 查询.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getList(array $where, array $field = ['*'], $sort = 'created_at', array $with = []): array
    {
        if ($where['viewer'] == 'hr') {
            unset($where['user_id']);
        }
        if ($where['finish_like']) {
            if (! $where['user_ids'] = app()->get(AdminService::class)->column(['name_like' => $where['finish_like']], 'id')) {
                return $this->listData([]);
            }
        }
        return $this->defaultList($where, $field, $sort, $with);
    }

    /**
     * 按天分组查询.
     *
     * @param array|string[] $field
     */
    public function getGroupList(array $where, array $field = ['*']): array
    {
        $entid        = 1;
        $uuid         = $this->uuId(false);
        $frameService = app()->get(FrameService::class);
        if ($where['viewer'] != 'hr') {
            if ($where['dep_report']) {
                if (! $where['user_id']) {
                    $where['user_ids'] = app()->get(RolesService::class)->getDataUids(uuid_to_uid($uuid, $entid));
                }
            } else {
                $where['user_id'] = $frameService->uuidToUid($uuid, $entid);
            }
        }
        unset($where['viewer'], $where['dep_report']);
        [$page, $limit] = $this->getPageValue();
        $list           = $this->dao->getGroupList($where, $field, $page, $limit, 'daily_id', [
            'user' => fn ($q) => $q->select(['id', 'uid', 'name', 'avatar']),
        ]);
        $count = $this->dao->getGroupCount($where);
        return $this->listData($list, $count);
    }

    /**
     * 获取创建汇报表单.
     */
    public function resourceCreate(array $other = []): array
    {
        return [];
    }

    /**
     * 编辑汇报获取表单.
     *
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function resourceEdit(int $id, array $other = []): array
    {
        $info = $this->dao->get($id, ['*'], [
            'replys' => function ($query) {
                $query->with([
                    'card' => fn ($query) => $query->select(['name', 'id', 'uid', 'avatar']),
                ]);
            },
            'attachs' => function ($query) {
                $query->select(['id', 'att_dir as src', 'relation_id', 'name', 'att_size as size']);
            },
            'card',
            'members',
        ])?->toArray();
        if (! $info) {
            throw $this->exception('汇报信息不存在');
        }
        $create = Carbon::make($info['created_at']);
        switch ($info['types']) {
            case 2:
                $info['start_time'] = $create->startOfMonth()->toDateTimeString();
                $info['end_time']   = $create->endOfMonth()->toDateTimeString();
                break;
            case 1:
                $info['start_time'] = $create->startOfWeek()->toDateTimeString();
                $info['end_time']   = $create->endOfWeek()->toDateTimeString();
                break;
            default:
                $info['start_time'] = $create->startOfDay()->toDateTimeString();
                $info['end_time']   = $create->endOfDay()->toDateTimeString();
        }
        $info['replys'] = $info['replys'] ?? [];
        $pid            = array_filter(array_unique(array_column($info['replys'], 'pid')));
        $replyList      = app()->get(ReportReplyService::class)->getList(['ids' => $pid], ['pid', 'uid', 'id'], 'id', ['card']);
        $dataList       = [];
        foreach ($replyList['list'] as $item) {
            $dataList[$item['id']] = $item;
        }
        foreach ($info['replys'] as &$reply) {
            $reply['paent_user'] = $dataList[$reply['pid']] ?? null;
        }
        foreach ($info['attachs'] as &$attach) {
            $attach['src'] = link_file($attach['src']);
        }

        // report member
        $members = [];
        foreach ($info['members'] as $member) {
            $members[] = ['card' => $member];
        }
        $info['members'] = $members;
        return $info;
    }

    /**
     * 获取某一月的计划列表.
     *
     * @return array
     * @throws \ReflectionException
     * @throws BindingResolutionException
     */
    public function getMonthDailyList(string $uid, int $entid, string $time)
    {
        $data = $this->dao->search(['uid' => $uid, 'entid' => $entid])
            ->where('created_at', 'like', $time . '%')
            ->groupBy('day')->select(['finish', 'plan', DB::raw('day(created_at) as day')])->get()->toArray();

        return array_combine(array_column($data, 'day'), $data);
    }

    /**
     * 获取今日计划.
     *
     * @return null|BaseModel|Model|object
     * @throws \ReflectionException
     * @throws BindingResolutionException
     */
    public function getDayDaily(string $uid)
    {
        return $this->dao->search(['time' => 'today', 'uid' => $uid])->select(['finish', 'plan'])->first();
    }

    /**
     * 创建汇报.
     *
     * @return BaseModel|mixed|Model
     * @throws \ReflectionException
     * @throws BindingResolutionException
     */
    public function resourceSave(array $data)
    {
        if ($data['types'] != 3 && ! $data['plan']) {
            throw $this->exception('请填写工作计划');
        }
        if (! $data['entid']) {
            throw $this->exception('请先加入企业再尝试填写汇报');
        }
        return $this->transaction(function () use ($data) {
            $attachIds = $data['attach_ids'];
            $members   = $data['members'];
            unset($data['attach_ids'], $data['members']);
            $data['user_id']  = uuid_to_uid($data['uid']);
            $data['plan']     = is_array($data['plan']) ? array_filter($data['plan']) : [$data['plan']];
            $data['finish']   = is_array($data['finish']) ? array_filter($data['finish']) : [$data['finish']];
            $res              = $this->dao->create($data);
            $data['daily_id'] = $res->daily_id;

            $this->handleMember($members, $res->daily_id);
            app()->get(AttachService::class)->saveRelation($attachIds, $data['uid'], $res->daily_id, AttachService::RELATION_TYPE_DAILY);
            // 消息提醒
            Task::deliver(new DailyReportRemind($data['entid'], $data['uid'], $res->daily_id, $data, true));
            // 待办同步
            Task::deliver(new DailyCreateTask($data['entid'], $data['uid'], $res->daily_id, $data));

            return $res;
        });
    }

    /**
     * 修改汇报.
     *
     * @param int $id
     *
     * @return bool|mixed
     * @throws \ReflectionException
     * @throws BindingResolutionException
     */
    public function resourceUpdate($id, array $data)
    {
        if ($data['types'] != 3 && ! $data['plan']) {
            throw $this->exception('请填写工作计划');
        }

        $daily = $this->dao->get($id);
        if (! $daily) {
            throw $this->exception('汇报不存在');
        }

        return $this->transaction(function () use ($id, $daily, $data) {
            $timezone  = config('app.timezone');
            $createdAt = strtotime((string) $daily->created_at);
            switch ($daily->types) {
                case 0:
                    if ($createdAt < now()->startOfDay()->timezone($timezone)->timestamp || $createdAt > now()->endOfDay()->timezone($timezone)->timestamp) {
                        throw $this->exception('您只能修改当天的汇报');
                    }
                    break;
                case 1:
                    if ($createdAt > now()->endOfWeek()->timezone($timezone)->timestamp || $createdAt < now()->startOfWeek()->timezone($timezone)->timestamp) {
                        throw $this->exception('您只能修改本周的汇报');
                    }
                    break;
                case 2:
                    if ($createdAt > now()->endOfMonth()->timezone($timezone)->timestamp || $createdAt < now()->startOfMonth()->timezone($timezone)->timestamp) {
                        throw $this->exception('您只能修改本月的汇报');
                    }
                    break;
            }

            $daily->finish    = array_filter($data['finish']);
            $daily->plan      = array_filter($data['plan']);
            $daily->mark      = $data['mark'];
            $res              = $daily->save();
            $data['daily_id'] = $daily->daily_id;
            $this->handleMember($data['members'], $daily->daily_id);
            app()->get(AttachService::class)->saveRelation($data['attach_ids'], $daily->uid, $daily->daily_id, AttachService::RELATION_TYPE_DAILY);
            // 消息提醒
            Task::deliver(new DailyReportRemind($data['entid'], $data['uid'], $id, $data));
            // 待办同步
            Task::deliver(new DailyCreateTask($data['entid'], $data['uid'], $id, $data));
            return $res;
        });
    }

    /**
     * @return bool
     * @throws \ReflectionException
     * @throws BindingResolutionException
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function resourceDelete($id, ?string $key = null)
    {
        $info = toArray($this->dao->get($id));
        if (! $info) {
            throw $this->exception('未找到删除记录');
        }
        $tz   = config('app.timezone');
        $time = strtotime($info['created_at']);
        if ($time < now()->startOfDay()->timezone($tz)->timestamp || $time > now()->endOfDay()->timezone($tz)->timestamp) {
            throw $this->exception('您只能删除当天提交的汇报');
        }

        if ($this->dao->delete($id, $key)) {
            Task::deliver(new StatusChangeTask(DailyEnum::LINK_NOTICE, DailyEnum::DAILY_DELETE, $info['entid'], $id));
            return true;
        }
        return false;
    }

    /**
     * 获取下级日报人员.
     *
     * @return array
     * @throws \ReflectionException
     * @throws BindingResolutionException
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function getDailyUser(int $uid, string $viewer = 'user')
    {
        $ids = [];
        if ($viewer != 'user') {
            if (! in_array($uid, app()->get(RolesService::class)->getRuleUserIds(1, RuleEnum::PERSONNEL_TYPE))) {
                $ids = [];
            }
        } else {
            $ids = app()->get(RolesService::class)->getDataUids($uid);
        }
        return app()->get(AdminService::class)->select(['id' => $ids])?->toArray();
    }

    /**
     * 日报定时任务
     *
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function timer(int $entid, int $type, int $page, int $limit)
    {
        $user = app()->get(AdminInfoService::class)->getEntUserIdsCache($entid, $page, $limit);
        if (! $user) {
            return;
        }
        $where         = ['entid' => $entid, 'user_ids' => array_column($user, 'id'), 'types' => $type];
        $where['time'] = match ($type) {
            1       => 'week',
            2       => 'month',
            default => 'today',
        };
        $unUserIds = $this->dao->column($where, 'user_id');
        // 比较出没有提交的用户
        $diffUserId = array_diff(array_column($user, 'id'), $unUserIds);
        if (! $diffUserId) {
            return;
        }
        $userList      = app()->get(AdminService::class)->select(['id' => $diffUserId])?->toArray();
        $batchData     = [];
        $remindService = app()->get(UserRemindLogService::class);
        $remindList    = $remindService->batchUserRemindList([
            'entid'       => $entid,
            'year'        => now()->year,
            'month'       => now()->month,
            'day'         => now()->day,
            'remind_type' => MessageType::DAILY_REMIND_TYPE,
            'user_id'     => $diffUserId,
        ]);

        foreach ($userList as $item) {
            if (isset($remindList[$item['id']])) {
                continue;
            }
            $batchData[] = ['to_uid' => $item['id'], 'phone' => $item['phone']];
        }
        $task = new MessageSendTask(
            entid: $entid,
            i: $entid,
            type: MessageType::DAILY_REMIND_TYPE,
            bathTo: $batchData
        );
        Task::deliver($task);
        $remind = [];
        foreach ($batchData as $item) {
            $remind[] = [
                'remind_type' => MessageType::DAILY_REMIND_TYPE,
                'user_id'     => $item['to_uid'],
                'entid'       => $entid,
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
    }

    /**
     * 获取用户汇报统计
     * @param string $types
     * @return array
     * @throws BindingResolutionException
     */
    public function getUserDailyCount($uuid, $entId, $types = 'day')
    {
        $frameService = app()->get(FrameService::class);
        $uid          = $frameService->uuidToUid($uuid, $entId);
        switch ($types) {
            case 'day':
                $data['count']   = now()->daysInMonth;
                $data['finish']  = count($this->dao->getOnceList(['user_id' => $uid, 'time' => 'month', 'types' => 0]));
                $data['surplus'] = bcsub((string) $data['count'], (string) $data['finish'], 0);
                break;
            case 'week':
                $data['count']   = 4;
                $data['finish']  = count($this->dao->getOnceList(['user_id' => $uid, 'time' => 'month', 'types' => 1]));
                $data['surplus'] = bcsub((string) $data['count'], (string) $data['finish'], 0);
                break;
            case 'month':
                $data['count']   = 1;
                $data['finish']  = count($this->dao->getOnceList(['user_id' => $uid, 'time' => 'month', 'types' => 2]));
                $data['surplus'] = bcsub((string) $data['count'], (string) $data['finish'], 0);
                break;
            default:
                $data = [];
        }
        return $data;
    }

    /**
     * 汇报提交统计
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function submitStatistics(string $uuid, int $type = 0, int $types = 0, string $time = '', array $userIds = []): array
    {
        $types          = $type == 1 ? 0 : (! in_array($types, [0, 1, 2, 3]) ? 0 : $types);
        $arrangeService = app()->get(AttendanceArrangeService::class);

        $list       = [];
        $format     = 'Y/m/d H:i:s';
        $uid        = uuid_to_uid($uuid);
        $tz         = config('app.timezone');
        $now        = Carbon::now($tz);
        $originTime = $this->parseTime($time, $types);
        $origin     = explode('-', $originTime);

        $userNum = 1;
        if ($type == 1) {
            $where = ['entid' => 1, 'types' => $types, 'user_id' => $uid];
        } else {
            $userNum = count($userIds);
            $where   = ['entid' => 1, 'types' => $types, 'user_ids' => $userIds];
        }
        switch ($types) {
            case 2:// 月报
                $now = now($tz)->toDateTimeString();
                for ($i = 1; $i <= 12; ++$i) {
                    $start = Carbon::parse($origin[0], $tz)->month($i);
                    if ($start->gt($now)) {
                        break;
                    }
                    $where['time'] = $start->format($format) . '-' . Carbon::parse($origin[0], $tz)->month($i)->endOfMonth()->format($format);
                    $num           = $this->dao->search($where)->groupBy('user_id')->get(DB::raw('distinct(user_id)'))->count();
                    $list[]        = ['time' => $start->format('Y-m'), 'no_submit' => $num == $userNum ? 0 : 1];
                }
                break;
            case 1:// 周报
                $weekData    = [];
                $endOfWeek   = Carbon::parse($origin[0], $tz)->endOfWeek();
                $startOfWeek = Carbon::parse($origin[0], $tz)->startOfWeek();

                $weekData[] = [$startOfWeek, $endOfWeek];
                for ($i = 0; $i < 4; ++$i) {
                    $endOfWeek   = Carbon::parse($endOfWeek, $tz)->endOfWeek()->addWeek();
                    $startOfWeek = Carbon::parse($endOfWeek, $tz)->startOfWeek();
                    $weekData[]  = [$startOfWeek, $endOfWeek];
                }
                foreach ($weekData as $week) {
                    $timeZone  = CarbonPeriod::create(Carbon::parse($week[0], $tz)->toDateString(), Carbon::parse($week[1], $tz)->toDateString())->toArray();
                    $notSubmit = false;

                    foreach ($timeZone as $day) {
                        if ($day->startOfDay()->gt($now->toDateTimeString())) {
                            break;
                        }
                        $where['time'] = $day->startOfDay()->format($format) . '-' . $day->endOfDay()->format($format);
                        $result        = $this->dao->search($where)->groupBy('user_id')->get(DB::raw('distinct(user_id)'))->count() == $userNum;
                        if (! $result) {
                            $notSubmit = true;
                            break;
                        }
                    }
                    $list[] = ['time' => $week[1]->toDateString(), 'no_submit' => $notSubmit ? 1 : 0];
                }
                break;
            default:// 日报
                $timeZone = CarbonPeriod::create(Carbon::parse($origin[0], $tz)->toDateString(), Carbon::parse($origin[1], $tz)->toDateString())->toArray();
                foreach ($timeZone as $day) {
                    if ($day->startOfDay()->gt($now->toDateTimeString())) {
                        continue;
                    }

                    $date = $day->startOfDay()->toDateString();

                    // rest day no prompt
                    $isRest = false;
                    if ($type == 1) {
                        $isRest = $arrangeService->dayIsRest($uid, $date);
                    } else {
                        $restNum = 0;
                        foreach ($where['user_ids'] as $userId) {
                            if ($arrangeService->dayIsRest((int) $userId, $date)) {
                                ++$restNum;
                            }
                        }

                        if ($restNum == $userNum) {
                            $isRest = true;
                        }
                    }

                    if ($isRest) {
                        $list[] = ['time' => $day->toDateString(), 'no_submit' => 0];
                        continue;
                    }

                    $where['time'] = $day->startOfDay()->format($format) . '-' . $day->endOfDay()->format($format);
                    $result        = $this->dao->search($where)->groupBy('user_id')->get(DB::raw('distinct(user_id)'))->count() == $userNum;
                    $list[]        = ['time' => $day->toDateString(), 'no_submit' => $result ? 0 : 1];
                }
        }
        return $list;
    }

    /**
     * 提交统计
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function statistics(array $where): array
    {
        $data           = $submit = [];
        $where['entid'] = 1;
        $where['time']  = $this->parseTime($where['time'], $where['types']);
        $data['submit'] = $this->dao->search($where)->select(['user_id'])->groupBy('user_id')->get()->each(function ($item) use (&$submit) {
            $submit[] = $item['user_id'];
        })->count();
        $data['no_submit'] = count(array_diff($where['user_ids'], $submit));
        $data['total']     = $data['submit'] + $data['no_submit'];
        return $data;
    }

    /**
     * 提交统计
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function statisticsByFrameId(string $uuid, int $entId, int $frameId = 0, int $types = 0, string $time = ''): array
    {
        $userIds = $this->getScopeUserIds($uuid, $entId, $frameId);
        return $this->statistics(['types' => $types, 'time' => $time, 'user_ids' => $userIds]);
    }

    /**
     * 解析时间.
     */
    public function parseTime(string $originTime, int $types = 0, string $format = 'Y/m/d H:i:s'): string
    {
        $start  = $end = '';
        $tz     = config('app.timezone');
        $now    = Carbon::now($tz);
        $origin = explode('-', $originTime);
        if (! $originTime) {
            switch ($types) {
                case 0:
                    $yesterday = $now->subDay();
                    $start     = $yesterday->startOfDay()->format($format);
                    $end       = $yesterday->endOfDay()->format($format);
                    break;
                case 1:
                    $start = $now->startOfWeek()->format($format);
                    $end   = $now->endOfWeek()->endOfDay()->format($format);
                    break;
                case 2:
                    $start = $now->month(1)->startOfMonth()->startOfDay()->format($format);
                    $end   = $now->month(12)->endOfMonth()->endOfDay()->format($format);
                    break;
            }
        } else {
            if ($types == 2) {
                if (strlen($originTime) == 4) {
                    $origin = [$originTime . '/01/01', $originTime . '/12/31'];
                }
            }
            $startCarbon = Carbon::parse($origin[0], $tz);
            $endCarbon   = Carbon::parse($origin[1], $tz);
            if ($endCarbon->gt($now->toDateTimeString())) {
                $endCarbon = match ($types) {
                    1       => $endCarbon,
                    default => $now,
                };
            }

            if ($types == 2) {
                $start = $startCarbon->startOfMonth()->startOfDay()->format($format);
                $end   = $endCarbon->endOfMonth()->endOfDay()->format($format);
            } else {
                $start = $startCarbon->startOfDay()->format($format);
                $end   = $endCarbon->endOfDay()->format($format);
            }
        }
        return $start . '-' . $end;
    }

    /**
     * 已提交统计列表.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getSubmitList(array $where): array
    {
        $where['entid'] = 1;
        $where['time']  = $this->parseTime($where['time'], $where['types']);
        $field          = ['daily_id', 'uid', 'user_id', 'types', 'created_at'];
        [$page, $limit] = $this->getPageValue();
        $list           = toArray($this->dao->getList($where, $field, $page, $limit, 'created_at', [
            'user' => fn ($q) => $q->select(['id', 'uid', 'name', 'avatar']),
            'frame',
        ]));

        $count = $this->dao->count($where);
        foreach ($list as &$item) {
            $item = $this->submitUserListFormat($item);
        }

        return $this->listData($list, $count);
    }

    /**
     * 获取统计范围提交汇报用户列表.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getSubmitListByFrameId(string $uuid, int $frameId, int $types, string $time): array
    {
        $userIds = $this->getScopeUserIds($uuid, 1, $frameId);
        return $this->getSubmitList(['types' => $types, 'time' => $time, 'user_ids' => $userIds]);
    }

    /**
     * 获取统计范围未提交汇报用户列表.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function getNoSubmitUserListByFrameId(string $uuid, int $frameId, int $types, string $time): array
    {
        $userIds = $this->getScopeUserIds($uuid, 1, $frameId);
        return $this->getNoSubmitUserList(['types' => $types, 'time' => $time, 'user_ids' => $userIds]);
    }

    /**
     * 未提交汇报用户列表.
     * @throws BindingResolutionException
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function getNoSubmitUserList(array $where): array
    {
        $submit         = [];
        $where['entid'] = 1;
        $where['time']  = $this->parseTime($where['time'], $where['types']);
        $this->dao->search($where)->select(['user_id'])->groupBy('user_id')->get()->each(function ($item) use (&$submit) {
            $submit[] = $item['user_id'];
        });

        $field     = ['id', 'name', 'avatar'];
        $userWhere = ['id' => array_diff($where['user_ids'], $submit)];
        $users     = app()->get(AdminService::class)->getList($userWhere, $field, 'created_at', ['frame']);
        foreach ($users['list'] as &$item) {
            $item['types']      = $where['types'];
            $item['frame_name'] = $item['frame']['name'] ?? '';
            unset($item['frame']);
        }
        return $users;
    }

    /**
     * 统计范围.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getScopeUserIds(string $uuid, int $entId, int $frameId): array
    {
        return match ($frameId) {
            -1      => app()->get(FrameService::class)->getLevelSub(uuid_to_uid($uuid, $entId)),
            0       => app()->get(RolesService::class)->getDataUids(uuid_to_uid($uuid, $entId)),
            default => app()->get(FrameService::class)->scopeUser($frameId)
        };
    }

    /**
     * 获取日报表单回显数据.
     * @return array[]
     * @throws BindingResolutionException
     */
    public function dailyScheduleRecord($uuid, $type): array
    {
        $where = [
            'uid'   => $uuid,
            'types' => $type,
            'time'  => match ((int) $type) {
                1       => 'week',
                2       => 'month',
                default => 'today'
            },
        ];

        $scheduleService = app()->get(ScheduleInterface::class);
        return [
            'plan'   => $scheduleService->getNextWorkDayPlan($uuid),
            'finish' => ! $this->dao->exists($where) ? $scheduleService->dailyCompleteRecord($uuid, $type) : [],
        ];
    }

    /**
     * 直属上级
     * 兼容前端组件 card.uid.
     * @throws BindingResolutionException
     */
    public function getReportMember(int $uid): array
    {
        $data = [];
        $info = app()->get(FrameService::class)->getLevelSuperUser($uid);
        if ($info) {
            $data[] = [['card' => $info]];
        }
        return $data;
    }

    /**
     * 直属上级
     * 兼容前端组件 card.uid.
     * @throws BindingResolutionException
     */
    public function getReportMemberPC(int $uid): array
    {
        $data = [];
        $info = app()->get(FrameService::class)->getLevelSuperUser($uid);
        if ($info) {
            $data[] = ['card' => $info];
        }
        return $data;
    }

    /**
     * 默认列表.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function defaultList(array $where, array $field = ['*'], null|array|string $sort = null, array $with = []): array
    {
        unset($where['dep_report'], $where['viewer'], $where['finish_like']);
        [$page, $limit] = $this->getPageValue();
        $list           = $this->dao->getList($where, $field, $page, $limit, $sort, [
            'user' => fn ($q) => $q->select(['id', 'uid', 'name', 'avatar']), 'frame',
        ]);

        foreach ($list as &$item) {
            $create = Carbon::make($item['created_at']);
            switch ($item['types']) {
                case 2:
                    $item['start_time'] = $create->startOfMonth()->toDateTimeString();
                    $item['end_time']   = $create->endOfMonth()->toDateTimeString();
                    break;
                case 1:
                    $item['start_time'] = $create->startOfWeek()->toDateTimeString();
                    $item['end_time']   = $create->endOfWeek()->toDateTimeString();
                    break;
                default:
                    $item['start_time'] = $create->startOfDay()->toDateTimeString();
                    $item['end_time']   = $create->endOfDay()->toDateTimeString();
            }
            $item = $this->submitUserListFormat($item);
        }
        $count = $this->dao->count($where);
        return $this->listData($list, $count);
    }

    /**
     * 汇报人查看汇报列表.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getReportDailyList(array $where, array $field = ['*'], $sort = 'created_at', array $with = []): array
    {
        // get report member
        $where['daily_id'] = $this->memberDao->column(['member' => uuid_to_uid($this->uuId(false))], 'daily_id');

        if (empty($where['daily_id'])) {
            return $this->listData([]);
        }

        return $this->defaultList($where, $field, $sort, $with);
    }

    /**
     * 数据处理.
     * @return mixed
     */
    public function submitUserListFormat(array $item): array
    {
        $item['name']       = $item['user']['name'] ?? '';
        $item['avatar']     = $item['user']['avatar'] ?? '';
        $item['frame_name'] = $item['frame']['name'] ?? '';
        unset($item['user'], $item['frame']);
        return $item;
    }

    /**
     * 按周期获取已提交次数及未提交次数.
     * @return int[]
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getSubmitNumberByCycle(int $types, int $uid, array $cycle, string $tz): array
    {
        $submitTotal = $unSubmitTotal = 0;
        foreach ($cycle as $item) {
            $timeZone            = CarbonPeriod::create(Carbon::parse($item[0], $tz)->toDateString(), Carbon::parse($item[1], $tz)->toDateString())->toArray();
            [$submit, $unSubmit] = $this->getSubmitNumberByDay($types, $uid, $timeZone);
            $submitTotal += $submit;
            $unSubmit && $unSubmitTotal++;
        }
        return [$submitTotal, $unSubmitTotal];
    }

    /**
     * 按天为单位获取已提交次数及未提交次数.
     * @return int[]
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function getSubmitNumberByDay(int $types, int $uid, array $cycle): array
    {
        $unSubmit       = $submit = 0;
        $arrangeService = app()->get(AttendanceArrangeService::class);
        foreach ($cycle as $day) {
            $isRest = $arrangeService->dayIsRest($uid, $day->startOfDay()->toDateString());
            if ($isRest) {
                continue;
            }

            $num = $this->dao->search(['types' => $types, 'user_id' => $uid, 'day' => $day->format('Y-m-d')])->count();
            if ($num > 0) {
                $submit += $num;
            } else {
                ++$unSubmit;
            }
        }
        return [$submit, $unSubmit];
    }

    /**
     * 汇报统计导出.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function export(array $where): array
    {
        $adminWhere = ['status' => 1];
        if ($where['viewer'] == 'hr') {
            unset($where['user_id']);
        } else {
            $adminWhere['id'] = $where['user_id'];
        }
        $adminWhere['name_like'] = $where['name_like'];
        $types                   = (int) $where['types'];
        $tz                      = config('app.timezone');
        [$startTime,$endTime]    = explode('-', $where['time']);
        if (! $startTime || ! $endTime) {
            throw $this->exception('时间格式错误');
        }
        switch ($types) {
            case 1:
                $field  = 'WEEK(created_at) as days,user_id';
                $sumNum = Carbon::parse($startTime, $tz)->diffInWeeks(Carbon::parse($endTime, $tz));
                break;
            case 2:
                $field  = 'MONTH(created_at) as days,user_id';
                $sumNum = Carbon::parse($startTime, $tz)->diffInMonths(Carbon::parse($endTime, $tz));
                break;
            default:
                $field  = "DATE_FORMAT(created_at,'%Y-%m-%d') as days,user_id";
                $sumNum = Carbon::parse($startTime, $tz)->diffInDays(Carbon::parse($endTime, $tz)->addDays());
                break;
        }
        $typeName = DailyEnum::getDailyTypeName($types);
        $list     = app()->get(AdminService::class)->select($adminWhere, ['id', 'name'], ['frame' => fn ($query) => $query->select(['frame.id', 'frame.name'])])?->toArray();
        $data     = $this->dao->getGroupSearch(['types' => $types, 'time' => $where['time']], $field)->get()->toArray();
        foreach ($list as &$item) {
            $item['frame_name'] = $item['frame']['name'] ?? '';
            $item['type_name']  = $typeName;
            $submitNum          = 0;
            array_walk_recursive($data, function ($value, $key) use (&$submitNum, $item) {
                if ($value === $item['id'] && $key === 'user_id') {
                    ++$submitNum;
                }
            });
            $item['submit']    = $submitNum;
            $item['no_submit'] = $sumNum - $submitNum;
            unset($item['frame']);
        }
        return $list;
    }

    /**
     * 处理汇报人.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    private function handleMember(array $members, int $dailyId): void
    {
        $data = [];
        foreach ($this->memberDao->column(['daily_id' => $dailyId], 'member', 'id') as $key => $item) {
            $data[$item] = $key;
        }

        foreach ($members as $member) {
            if ($dailyId && isset($data[$member])) {
                unset($data[$member]);
                continue;
            }
            $this->memberDao->create(['daily_id' => $dailyId, 'member' => $member]);
        }

        if ($data) {
            $this->memberDao->delete(['daily_id' => $dailyId, 'id' => array_values($data)]);
        }
    }
}
