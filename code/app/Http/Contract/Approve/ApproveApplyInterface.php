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

namespace App\Http\Contract\Approve;

interface ApproveApplyInterface
{
    /**
     * 获取列表.
     */
    public function getList(array $where, array $field = ['*'], $sort = 'id', array $with = [], int $page = 0, int $limit = 0): array;

    /**
     * 获取详情.
     */
    public function getInfo(int $id, array $other = []): mixed;
}
