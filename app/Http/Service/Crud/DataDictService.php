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

use App\Http\Dao\Config\DictTypeDao;
use App\Http\Service\BaseService;
use Illuminate\Contracts\Container\BindingResolutionException;

/**
 * Class DataDictService.
 * @email 136327134@qq.com
 * @date 2024/2/29
 */
class DataDictService extends BaseService
{
    /**
     * DataDictService constructor.
     */
    public function __construct(DictTypeDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * @return array
     * @throws BindingResolutionException
     * @throws \ReflectionException
     * @email 136327134@qq.com
     * @date 2024/2/29
     */
    public function getDataDicList(array $where)
    {
        [$page, $limit] = $this->getPageValue();
        $list           = $this->dao->getList($where, ['*'], $page, $limit, 'id');
        $count          = $this->dao->count($where);
        return $this->listData($list, $count);
    }
}
