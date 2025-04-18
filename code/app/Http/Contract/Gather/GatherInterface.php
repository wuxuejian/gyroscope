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

namespace App\Http\Contract\Gather;

/**
 * 表单信息采集.
 */
interface GatherInterface
{
    /**
     * 生成问卷表单.
     * @param int $entId 企业ID
     * @param int $sponsor 发起人UUID/userId
     * @param array $form 表单内容
     * @param array $scope 可见范围(人员ID)
     * @param string $enableTime 启用时间
     */
    public function createGatherConfig(int $entId, int $sponsor, array $form, array $scope, string $enableTime = ''): bool;

    /**
     * 获取表单详情.
     * @param int $id 表单ID
     * @param int $userId 企业用户ID
     * @param int $entId 企业ID
     */
    public function getGatherConfig(int $id, int $userId, int $entId): array;

    /**
     * 获取表单需填写详情.
     * @param int $id 表单ID
     * @param int $userId 企业用户ID
     * @param int $entId 企业ID
     */
    public function getGatherInfo(int $id, int $userId, int $entId): array;

    /**
     * 保存表单填写内容.
     * @param int $id 表单ID
     * @param int $userId 企业用户ID
     * @param int $entId 企业ID
     * @return mixed
     */
    public function saveGatherInfo(int $id, array $data, int $userId, int $entId): bool;

    /**
     * 获取表单列表.
     */
    public function getGatherList(array $where, array $field = ['*'], array $with = []): array;

    /**
     * 获取表单采集信息列表.
     * @param int $id 表单ID
     * @param array $field 获取字段
     * @param array $with 关联查询
     */
    public function getGatherFillList(int $id, array $field = ['*'], array $with = []): array;

    /**
     * 获取表单填写记录详情.
     * @param int $id 表单填写记录ID
     * @param array $field 获取字段
     * @param array $with 关联查询
     */
    public function getGatherFillDetail(int $id, array $field = ['*'], array $with = []): array;
}
