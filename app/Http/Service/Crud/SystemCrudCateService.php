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

namespace App\Http\Service\Crud;

use App\Http\Dao\Crud\SystemCrudCateDao;
use App\Http\Service\BaseService;
use Illuminate\Contracts\Container\BindingResolutionException;

/**
 * 实体分类
 * Class SystemCrudCateService.
 * @email 136327134@qq.com
 * @date 2024/2/29
 */
class SystemCrudCateService extends BaseService
{
    /**
     * SystemCrudCateService constructor.
     */
    public function __construct(SystemCrudCateDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取分类名称.
     * @return array
     * @throws BindingResolutionException
     * @email 136327134@qq.com
     * @date 2024/4/16
     */
    public function idsByNameColumn(array $ids)
    {
        $list   = $this->dao->idsByName($ids);
        $column = [];
        foreach ($list as $item) {
            $column[$item['id']] = $item['name'];
        }
        return $column;
    }
}
