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

namespace App\Http\Contract\Schedule;

interface ScheduleInterface
{
    /**
     * 日程类型列表.
     */
    public function typeList(int $uid, array $field = ['*']): array;

    /**
     * 日程类型创建表单.
     */
    public function typeCreateForm(): array;

    /**
     * 日程类型保存.
     */
    public function saveType(int $uid, array $data): bool;

    /**
     * 日程类型修改表单.
     */
    public function typeEditForm(int $id, int $uid): array;

    /**
     * 日程类型修改.
     */
    public function updateType(int $id, int $uid, array $data): bool;

    /**
     * 日程类型删除.
     */
    public function deleteType(int $id, int $uid): bool;

    public function scheduleList(int $userId, int $entId, string $start, string $end, array $cid = [], int $period = 1): array;

    public function scheduleCount(int $userId, int $entId, string $start, string $end, array $cid = [], int $period = 1): array;

    /**
     * 日程保存.
     */
    public function saveSchedule(int $userId, int $entId, array $data, int $id = 0): bool;

    public function updateSchedule(int $userId, int $entId, int $id, array $data): bool;

    public function updateStatus(int $id, int $uid, int $entId, int $status, array $timeZone = []): bool;

    public function scheduleInfo(int $id, int $userId, array $field = ['*'], $where = []): array;

    public function deleteSchedule(int $userId, int $entId, int $id, array $data): bool;

    public function getEntListCache(): mixed;

    public function getEntCountCache(array $where): mixed;

    public function scheduleTimer(array $where, int $page, int $limit): void;

    public function deleteRemind(int $uid, string $unique): bool;

    public function deleteFromLinkId(int $uid, int $linkId, int $type): bool;

    public function dailyCompleteRecord(string $uuid, int $type): array;

    public function delScheduleByLinkId(int $linkId, array|int $cid): void;

    public function getNextWorkDayPlan(string $uuid): array;

    public function scheduleDateList(int $uid, int $entId, string $start, string $end, array $cid, int $period = 1): array;
}
