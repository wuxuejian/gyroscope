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

namespace App\Http\Service\System;

use App\Http\Dao\Config\CityDao;
use App\Http\Service\BaseService;
use Illuminate\Support\Facades\Cache;

/**
 * 省市区
 * Class SystemCityService.
 */
class SystemCityService extends BaseService
{
    public function __construct(CityDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取城市数据tree型结构.
     * @return mixed
     */
    public function cityTree()
    {
        return Cache::remember('cityTree', 0, function () {
            return get_tree_children($this->dao->getCityList([], ['city_id as value', 'name as label', 'parent_id as pid']), 'children', 'value');
        });
    }
}
