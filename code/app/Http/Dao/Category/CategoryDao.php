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

namespace App\Http\Dao\Category;

use App\Http\Dao\BaseDao;
use App\Http\Model\Category\Category;
use crmeb\traits\dao\ListSearchTrait;
use Illuminate\Contracts\Container\BindingResolutionException;

class CategoryDao extends BaseDao
{
    use ListSearchTrait;

    /**
     * 分级排序列表.
     * @param mixed $where
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getTierList($where, array $field = ['*']): array
    {
        return $this->search($where)->orderBy('sort', 'desc')->get($field)->toArray();
    }

    /**
     * 设置模型.
     * @return mixed|string
     */
    protected function setModel()
    {
        return Category::class;
    }
}
