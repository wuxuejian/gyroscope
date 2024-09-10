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

use App\Constants\AttendanceClockEnum;
use App\Http\Dao\Attendance\AttendanceClockDao;
use App\Http\Service\Admin\AdminService;
use App\Http\Service\BaseService;
use App\Http\Service\Frame\FrameAssistService;
use App\Jobs\AttendanceImportJob;
use crmeb\services\GroupDataService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * 考勤打卡记录
 * Class AttendanceClockService.
 */
class AttendanceClockService extends BaseService
{
    public function __construct(AttendanceClockDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 基础数据.
     * @return null[]
     * @throws \ReflectionException
     * @throws BindingResolutionException
     */
    public function getBasic(string $uuid, string $date = '', int $userId = 0): array
    {
        $uid = $this->getStatisticsUserId($uuid, $userId);
        $tz  = config('app.timezone');
        if (! $date) {
            $date = now($tz)->toDateString();
        } else {
            $dateObj = Carbon::parse($date, $tz);
            $date    = $dateObj->toDateString();
        }
        $groupService = app()->get(AttendanceGroupService::class);

        $isWhitelist = $groupService->isWhitelist($uid);

        // 考勤排班
        $clockGroup = $this->getClockBasicByUid($uid, $date);

        $group = $groupService->getMemberClockGroup($uid, $clockGroup['group_id'], true, true) ?: null;

        // 无需考勤
        if ($isWhitelist || app()->get(AttendanceArrangeService::class)->dayIsRest($uid, $date)) {
            return ['group' => $group, 'shift' => null, 'whitelist' => $isWhitelist];
        }

        return [
            'shift'     => app()->get(AttendanceShiftService::class)->getArrangeShiftById($clockGroup['shift_id'], $date) ?: null,
            'group'     => $group,
            'whitelist' => $isWhitelist,
        ];
    }

    /**
     * 打卡
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function shiftClock(int $uid, Carbon $dateObj, array $data): mixed
    {
        $tz = config('app.timezone');
        if (! in_array((int) $data['is_external'], [0, 1])) {
            throw $this->exception('打卡类型异常');
        }

        // 班次打卡
        $statisticsService = app()->get(AttendanceStatisticsService::class);
        $statistics        = $statisticsService->renewStatistics($uid, $dateObj);

        $this->checkClock($uid, $data, $statistics);

        // 更新打卡
        $this->checkIsUpdate($uid, $data['update_number'], $statistics, $tz);

        $clockNumber = 0;
        if ($data['update_number'] === '') {
            // 打卡班次
            [$status, $clockNumber] = $statisticsService->getClockNumber($dateObj, $statistics, $tz);
            if (! $status) {
                throw $this->exception('未到打卡时间');
            }
            $statisticsService->checkWorkTime($uid, $dateObj, $statistics, $clockNumber, $tz);
        }

        if (! app()->get(AttendanceGroupService::class)->isWhitelist($uid, $statistics->group_id) && $data['is_external']) {
            $data['location_status'] = 2;
        }

        return $this->transaction(function () use ($uid, $dateObj, $data, $statistics, $statisticsService, $clockNumber) {
            $res = $this->dao->create([
                'uid'         => $uid,
                'frame_id'    => $statistics->frame_id,
                'group_id'    => $statistics->group_id,
                'group'       => $statistics->group,
                'shift_id'    => $statistics?->shift_data['id'] ?? 0,
                'shift_data'  => $statistics->shift_data,
                'is_external' => $data['is_external'],
                'address'     => $data['address'],
                'lat'         => $data['lat'],
                'lng'         => $data['lng'],
                'remark'      => $data['remark'],
                'image'       => $data['image'],
            ]);

            if (! $res) {
                throw $this->exception('打卡异常');
            }

            $data['record_id'] = $res->id;
            $statisticsService->updateShiftStatistics($statistics, $dateObj, $clockNumber, $data);
            return true;
        });
    }

    /**
     * 打卡
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function saveClock(string $uuid, array $data): array
    {
        if ($data['update_number'] !== '' && ! in_array($data['update_number'], [0, 1, 2, 3])) {
            throw $this->exception('打卡班次错误');
        }

        $uid     = uuid_to_uid($uuid);
        $dateObj = now(config('app.timezone'));

        // 无需打卡
        if (app()->get(AttendanceGroupService::class)->isWhitelist($uid)
            || app()->get(AttendanceArrangeService::class)->dayIsRest($uid, $dateObj->toDateString())) {
            $this->defaultClock($uid, $dateObj, $data);
        } else {
            $this->shiftClock($uid, $dateObj, $data);
        }

        return ['clock_time' => $dateObj->format('H:i:s')];
    }

    /**
     * 获取考勤班次
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getClockBasicByUid(int $uid, string $date): array
    {
        $groupName           = '';
        [$groupId, $shiftId] = app()->get(AttendanceArrangeService::class)->getRecordByUid($uid, $date);
        $group               = app()->get(AttendanceGroupService::class)->getGroupByUidAndGroupId($uid, $groupId);
        if ($group) {
            $groupId   = $group->id;
            $groupName = $group->name;
        }

        if (! $shiftId && app()->get(CalendarConfigService::class)->dayIsRest($date)) {
            $shiftId = 1;
        }

        return ['group_id' => $groupId, 'shift_id' => $shiftId ?: 2, 'group_name' => $groupName];
    }

    /**
     * 获取考勤人员数据.
     *
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getAttendanceUser(string $uuid, int $userId): array
    {
        $this->checkMemberByUserId($userId, app()->get(AttendanceGroupService::class)->getTeamMember($uuid));
        $field = ['id', 'name as real_name', 'avatar', 'job'];
        $with  = ['job' => fn ($q) => $q->select(['id', 'name'])];
        $user  = app()->get(AdminService::class)->get($userId, $field, $with);
        if (! $user) {
            throw $this->exception('人员数据异常');
        }

        $user['frames'] = app()->get(FrameAssistService::class)->getUserFrames($userId ? uid_to_uuid($userId) : $uuid);
        return toArray($user);
    }

    /**
     * 获取考勤人员数据.
     * @return Application|array|GroupDataService|int|mixed
     * @throws \ReflectionException
     * @throws BindingResolutionException
     */
    public function getStatisticsUserId(string $uuid, int $userId): mixed
    {
        $uid = uuid_to_uid($uuid);
        if (! $userId) {
            return $uid;
        }

        // 考勤范围
        $this->checkMemberByUserId($userId, app()->get(AttendanceGroupService::class)->getTeamMember($uuid));
        return $userId;
    }

    /**
     * 打卡记录.
     * @param string $sort
     */
    public function getList(array $where, array $field = ['*'], $sort = 'id', array $with = []): array
    {
        $field = ['id', 'frame_id', 'group_id', 'group', 'shift_id', 'uid', 'created_at'];
        return parent::getList($where, $field, $sort, ['card', 'frame']);
    }

    /**
     * 打卡详情.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getInfo(int $id): array
    {
        return toArray($this->dao->get($id, ['*'], ['card', 'frame']));
    }

    /**
     * 导入打卡记录.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws Exception
     * @throws NotFoundExceptionInterface
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     * @throws \ReflectionException
     */
    public function saveRecord(array $data): void
    {
        $count = count($data);
        if (! $count) {
            throw $this->exception('导入内容不能为空');
        }

        $shifts = ['第一次上班', '第一次下班', '第二次上班', '第二次下班'];
        $fields = array_merge(['时间', '姓名'], $shifts);
        foreach ($fields as $field) {
            if (! isset($data[0][$field])) {
                throw $this->exception($field . '数据不存在');
            }
        }
        $limit = 10;
        $page  = $count < $limit ? 1 : ceil($count / $limit);
        for ($i = 1; $i <= $page; ++$i) {
            AttendanceImportJob::dispatch('singleImport', collect($data)->forPage($i, $limit)->toArray());
        }
    }

    /**
     * 默认打卡
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function defaultClock(int $uid, Carbon $dateObj, $data): void
    {
        $time              = $dateObj->toDateTimeString();
        $statisticsService = app()->get(AttendanceStatisticsService::class);
        $statistics        = $statisticsService->renewStatistics($uid, $dateObj);

        $recordData = [
            'uid'        => $uid,
            'frame_id'   => $statistics->frame_id,
            'group_id'   => $statistics->group_id,
            'group'      => $statistics->group,
            'shift_id'   => $statistics->shift_data['id'] ?? 0,
            'shift_data' => $statistics->shift_data,
            'lat'        => $data['lat'],
            'lng'        => $data['lng'],
            'address'    => $data['address'],
            'remark'     => $data['remark'],
            'image'      => $data['image'],
            'created_at' => $time,
            'updated_at' => $time,
        ];

        if ($data['is_external']) {
            $data['location_status'] = 1;
        }

        $this->transaction(function () use ($dateObj, $data, $statisticsService, $statistics, $recordData) {
            $res = $this->dao->create($recordData);
            if (! $res) {
                throw $this->exception('打卡异常');
            }

            $data['record_id'] = $res->id;
            if (! $statisticsService->updateDefaultStatistics($statistics, $dateObj, $data)) {
                throw $this->exception('打卡异常');
            }
        });
    }

    /**
     * 导入打卡数据.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function importClock($statistics, Carbon $dateObj, int $uid, int $clockNumber, bool $freeClock): void
    {
        $time = $dateObj->toDateTimeString();
        $res  = $this->dao->create([
            'uid'         => $uid,
            'frame_id'    => $statistics->frame_id,
            'group_id'    => $statistics->group_id,
            'group'       => $statistics->group,
            'shift_id'    => $statistics?->shift_data['id'] ?? 0,
            'shift_data'  => $statistics->shift_data,
            'is_external' => 0,
            'address'     => '',
            'lat'         => '',
            'lng'         => '',
            'remark'      => '',
            'image'       => '',
            'created_at'  => $time,
            'updated_at'  => $time,
        ]);

        if (! $res) {
            throw $this->exception('打卡数据导入异常');
        }

        $data['record_id'] = $res->id;
        app()->get(AttendanceStatisticsService::class)->updateShiftStatistics($statistics, $dateObj, $clockNumber, $data, $freeClock);
    }

    /**
     * 单条导入.
     */
    public function singleImport(array $record): void
    {
        try {
            $this->transaction(function () use ($record) {
                $tz               = config('app.timezone');
                $shifts           = ['第一次上班', '第一次下班', '第二次上班', '第二次下班'];
                $shiftStatus      = ['one_shift_status', 'two_shift_status', 'three_shift_status', 'four_shift_status'];
                $defaultClockData = ['remark' => '', 'image' => '', 'lat' => '', 'lng' => '', 'address' => '', 'is_external' => 0, 'update_number' => ''];

                $adminService      = app()->get(AdminService::class);
                $shiftService      = app()->get(AttendanceShiftService::class);
                $groupService      = app()->get(AttendanceGroupService::class);
                $statisticsService = app()->get(AttendanceStatisticsService::class);
                $adminId           = (int) $adminService->value(['name' => $record['姓名']], 'id');
                if (! $adminId) {
                    return;
                }

                $timeData = explode(' ', $record['时间']);
                if (count($timeData) != 2) {
                    Log::error('打卡记录导入时间格式异常', ['record' => $record]);
                    return;
                }

                $dateObj    = Carbon::parse(substr($timeData[0], 0, 10), $tz);
                $statistics = $statisticsService->renewStatistics($adminId, $dateObj);
                if ($groupService->isWhitelist($adminId) || $statistics->shift_data['number'] <= 0) {
                    for ($i = 0; $i < 4; ++$i) {
                        $shiftTime = trim($record[$shifts[$i]] ?? '');
                        if (! $shiftTime || $shiftTime == '--' || $shiftTime == '未打卡') {
                            continue;
                        }

                        if (strlen($shiftTime) < 10) {
                            $shiftTime = $dateObj->format('Y/m/d ') . $shiftTime;
                        }

                        // 默认打卡
                        $this->defaultClock($adminId, Carbon::parse($shiftTime, $tz), $defaultClockData);
                    }
                } else {
                    $dateString = $statistics->created_at->toDateString();
                    for ($i = 0; $i < $statistics->shift_data['number'] * 2; ++$i) {
                        $shiftTime   = trim($record[$shifts[$i]] ?? '');
                        $invalidTime = ! $shiftTime || $shiftTime == '--' || $shiftTime == '未打卡';
                        $rule        = $statistics->shift_data['rules'][$i > 1 ? 1 : 0];

                        // associated approve
                        [$approveFreeClock, $approveLocationStatus] = $statisticsService->calcAssociatedApprove($statistics, $dateString, $i, $rule, $tz);
                        if ($approveLocationStatus == 1 && $statistics->{$shifts[$i] . '_shift_location_status'} == 2) {
                            $statistics->{$shifts[$i] . '_shift_location_status'} = $approveLocationStatus;
                        }

                        if ($approveFreeClock || (in_array($i, [1, 3]) && $rule['free_clock'] > 0)) {
                            $workObj = $statisticsService->{in_array($i, [0, 2]) ? 'getWorkObj' : 'getOffWorkObj'}($rule, $dateString, $tz);
                            $data    = [
                                'status'   => AttendanceClockEnum::NORMAL,
                                'time'     => $workObj->toDateTimeString(),
                                'is_after' => $workObj->gt($statistics->created_at->endOfDay()) ? 1 : 0,
                            ];
                            if ($invalidTime) {
                                $clockTime = $statisticsService->{in_array($i, [0, 2]) ? 'getWorkObj' : 'getOffWorkObj'}($rule, $statistics->created_at->toDateString(), $tz)->toDateTimeString();
                            } else {
                                if (strlen($shiftTime) < 10) {
                                    $shiftTime = $dateObj->format('Y/m/d ') . $shiftTime;
                                }
                                $clockTime = Carbon::parse($shiftTime, $tz)->toDateTimeString();
                            }

                            $res = $this->dao->create([
                                'uid'         => $adminId,
                                'frame_id'    => $statistics->frame_id,
                                'group_id'    => $statistics->group_id,
                                'group'       => $statistics->group,
                                'shift_id'    => $statistics?->shift_data['id'] ?? 0,
                                'shift_data'  => $statistics->shift_data,
                                'is_external' => 0,
                                'address'     => '',
                                'lat'         => '',
                                'lng'         => '',
                                'remark'      => '',
                                'image'       => '',
                                'created_at'  => $clockTime,
                                'updated_at'  => $clockTime,
                            ]);

                            if (! $res) {
                                throw $this->exception('打卡数据导入异常');
                            }
                            $data['record_id'] = $res->id;
                            $this->updateStatisticsStatusAndTime($statistics, $shifts[$i], $data, $tz);
                            continue;
                        }

                        if ($invalidTime) {
                            $statistics->{$shiftStatus[$i]} = AttendanceClockEnum::LACK_CARD;
                            $statistics->actual_work_hours  = $shiftService->getActualWorkHours($statistics, $dateString, $tz);
                            $statistics->save();
                            continue;
                        }

                        if (strlen($shiftTime) < 10) {
                            $shiftTime = $dateObj->format('Y/m/d ') . $shiftTime;
                        }

                        $clockObj = Carbon::parse($shiftTime, $tz);

                        // 导入打卡数据
                        $this->importClock($statistics, $clockObj, $adminId, $i, $rule['free_clock'] > 0);
                    }
                }
            });
        } catch (\Throwable $e) {
            Log::error('打卡记录导入失败：' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine(), 'func' => 'singleImport', 'record' => $record]);
        }
    }

    /**
     * 导入三方考勤记录.
     */
    public function importThirdParty(int $type, array $originalRecords): void
    {
        $importName = match ($type) {
            1       => 'dingTalkImport',
            2       => 'qyImport',
            default => throw $this->exception('导入类型错误')
        };

        $data = [];
        if ($type == 1) {
            foreach ($originalRecords as $item) {
                $data[] = [
                    'name'       => $item['姓名'] ?? '',
                    'date'       => $item['考勤日期'] ?? '',
                    'clock_time' => $item['打卡时间'] ?? '',
                    'address'    => $item['打卡地址'] ?? '',
                    'remark'     => $item['打卡备注'] ?? '',
                ];
            }
        } else {
            foreach ($originalRecords as $item) {
                $data[] = [
                    'name'             => $item['姓名'] ?? '',
                    'date'             => $item['时间'] ?? '',
                    'one_shift_time'   => $item['上班1'] ?? '',
                    'two_shift_time'   => $item['下班1'] ?? '',
                    'three_shift_time' => $item['上班2'] ?? '',
                    'four_shift_time'  => $item['下班2'] ?? '',
                ];
            }
        }

        $count = count($data);
        if (! $count) {
            throw $this->exception('导入内容不能为空');
        }

        unset($originalRecords);

        $limit = 10;
        $page  = $count < $limit ? 1 : ceil($count / $limit);
        for ($i = 1; $i <= $page; ++$i) {
            AttendanceImportJob::dispatch($importName, collect($data)->forPage($i, $limit)->toArray());
        }
    }

    /**
     * 钉钉导入.
     */
    public function dingTalkImport(array $record): void
    {
        if (! $record['name'] ?? '' || ! $record['date'] ?? '') {
            return;
        }
        try {
            $tz             = config('app.timezone');
            $adminService   = app()->get(AdminService::class);
            $groupService   = app()->get(AttendanceGroupService::class);
            $arrangeService = app()->get(AttendanceArrangeService::class);
            $adminId        = (int) $adminService->value(['name' => $record['name']], 'id');
            if (! $adminId) {
                return;
            }

            $timeData = explode(' ', $record['date']);
            if (count($timeData) != 2) {
                Log::error('打卡记录导入日期格式异常', ['record' => $record]);
                return;
            }

            $dateObj = Carbon::parse(substr($timeData[0], 0, 10), $tz); // 考勤日期

            $clockObj = Carbon::parse($record['clock_time'], $tz);
            if ($clockObj->diffInDays($dateObj) > 2) {
                Log::error('打卡记录导入时间格式异常', ['record' => $record]);
                return;
            }

            // 白名单 || 休息
            if ($groupService->isWhitelist($adminId) || $arrangeService->dayIsRest($adminId, $dateObj->toDateString())) {
                $this->defaultClock($adminId, $clockObj, ['remark' => $record['remark'] ?? '', 'image' => '', 'lat' => '', 'lng' => '', 'address' => $record['address'] ?? [], 'is_external' => 0, 'update_number' => '']);
                return;
            }

            $statisticsService = app()->get(AttendanceStatisticsService::class);
            $statistics        = $statisticsService->renewStatistics($adminId, $dateObj);
            $clockTime         = $clockObj->toDateTimeString();
            $res               = $this->dao->create([
                'uid'         => $adminId,
                'frame_id'    => $statistics->frame_id,
                'group_id'    => $statistics->group_id,
                'group'       => $statistics->group,
                'shift_id'    => $statistics?->shift_data['id'] ?? 0,
                'shift_data'  => $statistics->shift_data,
                'is_external' => $record['is_external'] ?? '',
                'address'     => $record['address'] ?? '',
                'lat'         => '',
                'lng'         => '',
                'image'       => '',
                'remark'      => $record['remark'] ?? '',
                'created_at'  => $clockTime,
                'updated_at'  => $clockTime,
            ]);

            if (! $res) {
                throw $this->exception('打卡记录导入异常');
            }

            // 计算考勤
            $this->calcClockTimeWithAttendance($adminId, $dateObj);
        } catch (\Throwable $e) {
            Log::error('打卡记录文件导入失败：' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine(), 'func' => 'dingTalkImport', 'record' => $record]);
        }
    }

    /**
     * 计算考勤数据.
     */
    public function calcClockTimeWithAttendance(int $userId, Carbon $dateObj): void
    {
        try {
            $statisticsService = app()->get(AttendanceStatisticsService::class);
            $statistics        = $statisticsService->renewStatistics($userId, $dateObj);
            $this->transaction(function () use ($userId, $statistics, $statisticsService) {
                $tz     = config('app.timezone');
                $shifts = AttendanceClockEnum::SHIFT_CLASS;
                for ($i = 0; $i <= $statistics->shift_data['number'] * 2 - 1; ++$i) {
                    if (in_array($i, [0, 2]) && $statistics->{$shifts[$i] . '_shift_status'} == 1) {
                        continue;
                    }

                    $rule    = $statistics->shift_data['rules'][$i > 1 ? 1 : 0];
                    $workObj = $statisticsService->{in_array($i, [0, 2]) ? 'getWorkObj' : 'getOffWorkObj'}($rule, $statistics->created_at->toDateString(), $tz);

                    match ($i) {
                        1, 3 => $this->clockWithImport($userId, $statistics, $i, $workObj, (clone $workObj)->addSeconds((int) $rule['delay_card']), $tz, AttendanceClockEnum::NORMAL, 'desc') || $this->clockWithImport($userId, $statistics, $i, $statisticsService->getClockEndTime($userId, $statistics, $i - 1, $tz, false), (clone $workObj)->subSeconds(min((int) $rule['early_leave'], (int) $rule['early_lack_card'])), $tz, 0, 'desc'),
                        default => $this->clockWithImport($userId, $statistics, $i, (clone $workObj)->subSeconds((int) $rule['early_card']), (clone $workObj)->addSeconds(min((int) $rule['late'], (int) $rule['extreme_late'], (int) $rule['late_lack_card'])), $tz, AttendanceClockEnum::NORMAL) || $this->clockWithImport($userId, $statistics, $i, $workObj, (clone $workObj)->addSeconds(max((int) $rule['late'], (int) $rule['extreme_late'], (int) $rule['late_lack_card'])), $tz),
                    };
                }
            });
        } catch (\Throwable $e) {
            Log::error('考勤数据更新失败：' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine(), 'data' => [
                'user_id' => $userId,
                'date'    => $dateObj->toDateString(),
            ]]);
        }
    }

    /**
     * 企业微信导入.
     */
    public function qyImport(array $record): void
    {
        if (! $record['name'] ?? '' || ! $record['date'] ?? '') {
            return;
        }
        try {
            $this->transaction(function () use ($record) {
                $tz                = config('app.timezone');
                $adminService      = app()->get(AdminService::class);
                $shiftService      = app()->get(AttendanceShiftService::class);
                $groupService      = app()->get(AttendanceGroupService::class);
                $statisticsService = app()->get(AttendanceStatisticsService::class);
                $shifts            = AttendanceClockEnum::SHIFT_CLASS;
                $defaultClockData  = ['remark' => '', 'image' => '', 'lat' => '', 'lng' => '', 'address' => '', 'is_external' => 0, 'update_number' => ''];
                $adminId           = (int) $adminService->value(['name' => $record['name']], 'id');
                if (! $adminId) {
                    return;
                }

                $timeData = explode(' ', $record['date']);
                if (count($timeData) != 2) {
                    Log::error('打卡记录导入日期格式异常', ['record' => $record]);
                    return;
                }

                $dateObj    = Carbon::parse(substr($timeData[0], 0, 10), $tz);
                $statistics = $statisticsService->renewStatistics($adminId, $dateObj);
                if ($groupService->isWhitelist($adminId) || $statistics->shift_data['number'] <= 0) {
                    for ($i = 0; $i < 4; ++$i) {
                        $shiftTime = trim($record[$shifts[$i] . '_shift_time'] ?? '');
                        if (! $shiftTime || $shiftTime == '--' || $shiftTime == '未打卡') {
                            continue;
                        }

                        if (strlen($shiftTime) < 10) {
                            $shiftTime = $dateObj->format('Y/m/d ') . $shiftTime;
                        }

                        // 默认打卡
                        $this->defaultClock($adminId, Carbon::parse($shiftTime, $tz), $defaultClockData);
                    }
                } else {
                    $dateString = $statistics->created_at->toDateString();
                    for ($i = 0; $i < $statistics->shift_data['number'] * 2; ++$i) {
                        $shiftTime   = trim($record[$shifts[$i] . '_shift_time'] ?? '');
                        $invalidTime = ! $shiftTime || $shiftTime == '--' || $shiftTime == '未打卡';
                        $rule        = $statistics->shift_data['rules'][$i > 1 ? 1 : 0];

                        // associated approve
                        [$approveFreeClock, $approveLocationStatus] = $statisticsService->calcAssociatedApprove($statistics, $dateString, $i, $rule, $tz);
                        if ($approveLocationStatus == 1 && $statistics->{$shifts[$i] . '_shift_location_status'} == 2) {
                            $statistics->{$shifts[$i] . '_shift_location_status'} = $approveLocationStatus;
                        }

                        if ($approveFreeClock || (in_array($i, [1, 3]) && $rule['free_clock'] > 0)) {
                            $workObj = $statisticsService->{in_array($i, [0, 2]) ? 'getWorkObj' : 'getOffWorkObj'}($rule, $dateString, $tz);
                            $data    = [
                                'status'   => AttendanceClockEnum::NORMAL,
                                'time'     => $workObj->toDateTimeString(),
                                'is_after' => $workObj->gt($statistics->created_at->endOfDay()) ? 1 : 0,
                            ];
                            if ($invalidTime) {
                                $clockTime = $statisticsService->{in_array($i, [0, 2]) ? 'getWorkObj' : 'getOffWorkObj'}($rule, $statistics->created_at->toDateString(), $tz)->toDateTimeString();
                            } else {
                                if (strlen($shiftTime) < 10) {
                                    $shiftTime = $dateObj->format('Y/m/d ') . $shiftTime;
                                }
                                $clockTime = Carbon::parse($shiftTime, $tz)->toDateTimeString();
                            }

                            $res = $this->dao->create([
                                'uid'         => $adminId,
                                'frame_id'    => $statistics->frame_id,
                                'group_id'    => $statistics->group_id,
                                'group'       => $statistics->group,
                                'shift_id'    => $statistics?->shift_data['id'] ?? 0,
                                'shift_data'  => $statistics->shift_data,
                                'is_external' => 0,
                                'address'     => '',
                                'lat'         => '',
                                'lng'         => '',
                                'remark'      => '',
                                'image'       => '',
                                'created_at'  => $clockTime,
                                'updated_at'  => $clockTime,
                            ]);

                            if (! $res) {
                                throw $this->exception('打卡数据导入异常');
                            }
                            $data['record_id'] = $res->id;
                            $this->updateStatisticsStatusAndTime($statistics, $shifts[$i], $data, $tz);
                            continue;
                        }

                        if ($invalidTime) {
                            $statistics->{$shifts[$i] . '_shift_status'} = AttendanceClockEnum::LACK_CARD;
                            $statistics->actual_work_hours               = $shiftService->getActualWorkHours($statistics, $dateString, $tz);
                            $statistics->save();
                            continue;
                        }

                        if (strlen($shiftTime) < 10) {
                            $shiftTime = $dateObj->format('Y/m/d ') . $shiftTime;
                        }
                        $clockObj = Carbon::parse($shiftTime, $tz);

                        // 导入打卡数据
                        $this->importClock($statistics, $clockObj, $adminId, $i, $rule['free_clock'] > 0);
                    }
                }
            });
        } catch (\Throwable $e) {
            Log::error('打卡记录导入失败：' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine(), 'func' => 'qyImport', 'record' => $record]);
        }
    }

    /**
     * 是否更新打卡
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    private function checkIsUpdate(int $uid, string $number, $statistics, string $tz = ''): void
    {
        if ($number === '') {
            return;
        }

        $clockNumber = (int) $number;
        if (! in_array($clockNumber, [0, 1, 2, 3])) {
            throw $this->exception('班次错误');
        }

        if ($clockNumber > 1 && count($statistics->shift_data['rules']) < 2) {
            throw $this->exception('更新班次错误');
        }

        if (now($tz)->timestamp > app()->get(AttendanceStatisticsService::class)->getClockEndTime($uid, $statistics, $clockNumber, $tz)) {
            throw $this->exception('无法更新打卡, 请刷新后重试');
        }
    }

    /**
     * 打卡核对.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    private function checkClock(int $uid, array $data, mixed $statistics): void
    {
        if ($statistics->group_id < 1) {
            return;
        }
        $groupService = app()->get(AttendanceGroupService::class);
        $group        = $groupService->get($statistics->group_id);
        if (! $group) {
            throw $this->exception('考勤组异常');
        }

        if (! in_array($uid, $groupService->getMemberIdsById($statistics->group_id))) {
            return;
        }

        // 拍照
        if (count($data['image']) > 9) {
            throw $this->exception('上传拍照最多9张！');
        }

        if ($group->is_photo) {
            $this->checkImages($data['image']);
        }

        if ($data['is_external'] && ! $group->is_external) {
            throw $this->exception('不允许外勤打卡！');
        }

        $crossBorder = false;
        if ($this->calcDistance((float) $data['lat'], (float) $data['lng'], (float) $group['lat'], (float) $group['lng']) >= $group['effective_range']) {
            $crossBorder = true;
        }

        if ($crossBorder && ! $group->is_external) {
            throw $this->exception('不在打卡范围！');
        }

        // 外勤
        if ($group->is_external) {
            if ($crossBorder && $group->is_external_note && ! $data['remark']) {
                throw $this->exception('请填写备注！');
            }

            if ($group->is_external_photo) {
                $this->checkImages($data['image']);
            }
        }
    }

    /**
     * 获取坐标距离.
     */
    private function calcDistance(float $rLat, float $rLng, float $gLat, float $gLng): float
    {
        $pi      = pi();
        $earthR  = 6378.137 * 1000;
        $radRLat = $rLat * $pi / 180.;
        $radGLat = $gLat * $pi / 180.;
        $s       = cos($radRLat) * cos($radGLat) * cos(($rLng - $gLng) * $pi / 180.0) + sin($radRLat) * sin($radGLat);
        if ($s > 1) {
            $s = 1;
        }
        if ($s < -1) {
            $s = -1;
        }
        return ceil(acos($s) * $earthR);
    }

    /**
     * 图片验证
     */
    private function checkImages(array $images): void
    {
        if (count($images) < 1) {
            throw $this->exception('请进行拍照打卡！');
        }
    }

    /**
     * 核对考勤权限.
     */
    private function checkMemberByUserId(int $userId, array $members): void
    {
        if ($userId && ! in_array($userId, $members)) {
            throw $this->exception('不能查看该员工考勤数据');
        }
    }

    /**
     * 上班卡
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    private function clockWithImport(int $userId, mixed $statistics, int $clockNumber, Carbon $startObj, Carbon $endObj, string $tz, int $clockStatus = 0, string $sort = 'asc'): bool
    {
        $statisticsService = app()->get(AttendanceStatisticsService::class);
        $shifts            = AttendanceClockEnum::SHIFT_CLASS;
        $dateString        = $statistics->created_at->toDateString();
        $rule              = $statistics->shift_data['rules'][$clockNumber > 1 ? 1 : 0];

        // associated approve
        [$approveFreeClock, $approveLocationStatus] = $statisticsService->calcAssociatedApprove($statistics, $dateString, $clockNumber, $rule, $tz);
        if ($approveLocationStatus == 1 && $statistics->{$shifts[$clockNumber] . '_shift_location_status'} == 2) {
            $statistics->{$shifts[$clockNumber] . '_shift_location_status'} = $approveLocationStatus;
        }

        // 是否免打卡
        if ($approveFreeClock || (in_array($clockNumber, [1, 3]) && $rule['free_clock'] > 0)) {
            $workObj = $statisticsService->{in_array($clockNumber, [0, 2]) ? 'getWorkObj' : 'getOffWorkObj'}($rule, $dateString, $tz);
            $data    = [
                'status'   => AttendanceClockEnum::NORMAL,
                'time'     => $workObj->toDateTimeString(),
                'is_after' => $workObj->gt($statistics->created_at->endOfDay()) ? 1 : 0,
            ];
            $this->updateStatisticsStatusAndTime($statistics, $shifts[$clockNumber], $data, $tz);
            return true;
        }
        $record = $this->dao->get([
            'uid'      => $userId,
            'shift_id' => $statistics->shift_id,
            'time'     => $startObj->format('Y/m/d H:i:s') . '-' . $endObj->format('Y/m/d H:i:s'),
        ], ['id', 'created_at'], sort: ['created_at' => $sort]);
        if ($record) {
            $data = [
                'status'    => $clockStatus ?: $statisticsService->getClockStatus($clockNumber, $record->created_at, $statistics),
                'time'      => $record->created_at->toDateTimeString(),
                'is_after'  => $record->created_at->gt($statistics->created_at->endOfDay()) ? 1 : 0,
                'record_id' => $record->id,
            ];
            $this->updateStatisticsStatusAndTime($statistics, $shifts[$clockNumber], $data, $tz);
            return true;
        }

        return false;
    }

    /**
     * 更新打卡状态和时间.
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function updateStatisticsStatusAndTime(mixed $statistics, string $prefix, array $data, string $tz): void
    {
        $statistics->{$prefix . '_shift_time'}      = $data['time'];
        $statistics->{$prefix . '_shift_status'}    = $data['status'];
        $statistics->{$prefix . '_shift_is_after'}  = $data['is_after'];
        $statistics->{$prefix . '_shift_record_id'} = $data['record_id'] ?? 0;

        $statistics->actual_work_hours = app()->get(AttendanceShiftService::class)->getActualWorkHours($statistics, $statistics->created_at->toDateString(), $tz);
        if (! $statistics->save()) {
            throw $this->exception('打卡数据更新异常, 请稍后再试');
        }
    }
}
