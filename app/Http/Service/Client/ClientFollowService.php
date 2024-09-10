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

namespace App\Http\Service\Client;

use App\Constants\CacheEnum;
use App\Constants\ClientEnum;
use App\Constants\CommonEnum;
use App\Constants\ScheduleEnum;
use App\Http\Contract\Client\ClientFollowInterface;
use App\Http\Contract\Schedule\ScheduleInterface;
use App\Http\Dao\Client\ClientFollowDao;
use App\Http\Dao\Schedule\ScheduleRemindDao;
use App\Http\Service\Attach\AttachService;
use App\Http\Service\BaseService;
use App\Task\message\StatusChangeTask;
use Carbon\Carbon;
use crmeb\traits\service\ResourceServiceTrait;
use Hhxsv5\LaravelS\Swoole\Task\Task;
use Illuminate\Contracts\Container\BindingResolutionException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * 客户追踪记录
 * Class ClientFollowService.
 */
class ClientFollowService extends BaseService implements ClientFollowInterface
{
    use ResourceServiceTrait;

    protected array $tags;

    public function __construct(ClientFollowDao $dao)
    {
        $this->dao  = $dao;
        $this->tags = [CacheEnum::TAG_SCHEDULE];
    }

    /**
     * 获取列表.
     * @param array|string[] $field
     * @param string $sort
     * @param array|string[] $with
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getList(array $where, array $field = ['*'], $sort = 'id', array $with = []): array
    {
        [$page, $limit] = $this->getPageValue();
        $with           = $with + ['card', 'attachs'];
        $list           = $this->dao->getList($where, $field, $page, $limit, $sort, $with);
        foreach ($list as &$item) {
            foreach ($item['attachs'] as &$v) {
                $v['url'] = link_file($v['url']);
            }
        }
        $count = $this->dao->count($where);
        return $this->listData($list, $count);
    }

    /**
     * 保存.
     * @return mixed
     * @throws \ReflectionException
     * @throws BindingResolutionException
     */
    public function resourceSave(array $data)
    {
        $uuid            = (string) $this->uuId(false);
        $entId           = 1;
        $data['user_id'] = uuid_to_uid($uuid, $entId);
        $attachIds       = $data['attach_ids'];
        $followId        = $data['follow_id'] ?? 0;
        unset($data['attach_ids'], $data['follow_id']);

        $customer = app()->get(CustomerService::class)->get((int) $data['eid'], ['id', 'return_num', 'uid']);
        if (! $customer) {
            throw $this->exception('客户数据异常');
        }

        if ($customer->uid < 1) {
            throw $this->exception('公海客户不支持填写跟进');
        }

        $data['follow_version'] = $customer['return_num'];
        return $this->transaction(function () use ($data, $attachIds, $uuid, $entId, $followId) {
            if ($data['types']) {
                if (! $data['time']) {
                    throw $this->exception('common.empty.attrs');
                }
                $data['uniqued'] = md5(json_encode($data) . time());
                $res1            = $this->dao->create($data);

                $timeZone  = config('app.timezone');
                $startTime = Carbon::parse($data['time'], $timeZone)->startOfDay()->toDateTimeString();
                $endTime   = Carbon::parse($data['time'], $timeZone)->endOfDay()->toDateTimeString();
                $res2      = app()->get(ScheduleInterface::class)->saveSchedule($data['user_id'], $entId, [
                    'title'       => $data['content'],
                    'content'     => $data['content'],
                    'remind'      => 1,
                    'remind_time' => $data['time'],
                    'all_day'     => 1,
                    'cid'         => ScheduleEnum::TYPE_CLIENT_TRACK,
                    'period'      => 0,
                    'rate'        => 1,
                    'start_time'  => $startTime,
                    'end_time'    => $endTime,
                    'fail_time'   => $endTime,
                    'link_id'     => $data['eid'],
                    'uniqued'     => $data['uniqued'],
                    'member'      => [$data['user_id']],
                ]);
                return $res1 && $res2 ? $res1 : [];
            }

            // 更新日程状态
            ! $data['types'] && $this->updateSchedule((int) $data['eid']);

            // 完成日程关联跟进提醒
            if ($followId) {
                $this->dao->update(['id' => $followId, 'types' => 1], ['status' => 2]);
            }
            unset($data['time']);
            $res = $this->dao->create($data);
            if ($attachIds) {
                app()->get(AttachService::class)->saveRelation($attachIds, $uuid, $res->id, AttachService::RELATION_TYPE_FOLLOW);
            }
            return $res;
        });
    }

    /**
     * 修改.
     * @param mixed $id
     * @return mixed
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function resourceUpdate($id, array $data)
    {
        $info = $this->dao->get($id, ['*'], ['file' => fn ($q) => $q->select(['id', 'fid'])]);
        if (! $info) {
            throw $this->exception('common.operation.noExists');
        }
        $uuid            = (string) $this->uuId(false);
        $entId           = 1;
        $attachIds       = $data['attach_ids'];
        $data['user_id'] = uuid_to_uid($uuid, $entId);
        unset($data['attach_ids'], $data['follow_id'], $data['eid']);
        /** @var ScheduleInterface $schedule */
        $schedule = app()->get(ScheduleInterface::class);
        return $this->transaction(function () use ($data, $info, $id, $schedule, $attachIds, $uuid, $entId) {
            if ($info->types) {
                if (! $data['time']) {
                    throw $this->exception('common.empty.attrs');
                }
                $data['uniqued'] = md5(json_encode($data) . time());
                unset($data['files']);
                $res1 = $this->dao->update($id, $data);
                if ($info->uniqued && $info->uniqued != $data['uniqued']) {
                    $schedule->deleteRemind($data['user_id'], $info->uniqued);
                    $schedule->saveSchedule($data['user_id'], $entId, [
                        'title'       => $data['content'],
                        'content'     => $data['content'],
                        'remind'      => 1,
                        'remind_time' => $data['time'],
                        'all_day'     => 1,
                        'cid'         => ScheduleEnum::TYPE_CLIENT_TRACK,
                        'period'      => 0,
                        'rate'        => 1,
                        'start_time'  => Carbon::parse($data['time'], config('app.timezone'))->startOfDay()->toDateTimeString(),
                        'end_time'    => Carbon::parse($data['time'], config('app.timezone'))->endOfDay()->toDateTimeString(),
                        'fail_time'   => Carbon::parse($data['time'], config('app.timezone'))->endOfDay()->toDateTimeString(),
                        'link_id'     => $info->eid,
                        'uniqued'     => $data['uniqued'],
                        'member'      => [$data['user_id']],
                    ]);
                }
                return $res1;
            }
            if ($attachIds) {
                app()->get(AttachService::class)->saveRelation($attachIds, $uuid, (int) $id, AttachService::RELATION_TYPE_FOLLOW);
            }
            unset($data['time']);
            return $this->dao->update($id, $data);
        });
    }

    /**
     * 删除.
     * @param mixed $id
     * @return int
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function resourceDelete($id, ?string $key = null)
    {
        $info = $this->dao->get($id);
        if (! $info) {
            throw $this->exception('common.operation.noExists');
        }
        if ($info->types && $info->uniqued) {
            $userId = uuid_to_uid((string) $this->uuId(false), 1);
            app()->get(ScheduleInterface::class)->deleteRemind($userId, $info->uniqued);
        }
        $res = $this->dao->delete($id);
        if ($res) {
            Task::deliver(new StatusChangeTask(ClientEnum::CLIENT_FOLLOW_NOTICE, CommonEnum::STATUS_DELETE, 1, $info->eid));
        }
        return $res;
    }

    /**
     * 未完成待办提醒客户ID.
     */
    public function getEidBySchedule(array $where, array $field = ['client_follow.eid']): array
    {
        return $this->dao->scheduleSearch($where)->select($field)->distinct()->get()->map(function ($item) {
            return $item['eid'];
        })->filter()->all();
    }

    /**
     * 提醒删除.
     * @param mixed $uniqued
     * @throws BindingResolutionException
     */
    public function delScheduleAfter($uniqued): void
    {
        $this->dao->update(['uniqued' => $uniqued], ['types' => 0, 'uniqued' => '']);
    }

    /**
     * 获取客户最后跟进时间.
     * @throws BindingResolutionException
     */
    public function getLastFollowTime(int $eid): string
    {
        return (string) $this->dao->setDefaultSort('created_at')->value(['eid' => $eid, 'types' => 0], 'created_at');
    }

    /**
     * 更新日程状态
     * @param mixed $eid
     * @return bool|void
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    private function updateSchedule(int $eid): void
    {
        $list = $this->dao->column(['eid' => $eid, 'types' => 1, 'time_lt' => date('Y-m-d H:i:s'), 'status' => 0], 'id,uniqued');
        if (! $list) {
            return;
        }
        $scheduleRemindService = app()->get(ScheduleRemindDao::class);
        $scheduleService       = app()->get(ScheduleInterface::class);
        try {
            foreach ($list as $item) {
                $this->dao->update($item['id'], ['status' => 2]);
                $scheduleRemindInfo = $scheduleRemindService->get(['uniqued' => $item['uniqued']], ['id', 'sid', 'uid', 'entid'], ['schedule']);
                if (! $scheduleRemindInfo || ! $scheduleRemindInfo->schedule) {
                    continue;
                }

                if ($scheduleRemindInfo->schedule->status !== 0) {
                    continue;
                }

                $scheduleService->updateStatus(
                    $scheduleRemindInfo->sid,
                    (int) $scheduleRemindInfo->uid,
                    $scheduleRemindInfo->entid,
                    3,
                    [$scheduleRemindInfo->schedule->start_time, $scheduleRemindInfo->schedule->end_time]
                );
            }
        } catch (\Exception) {
        }
    }
}
