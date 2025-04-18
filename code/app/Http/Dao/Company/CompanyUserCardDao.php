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

namespace App\Http\Dao\Company;

use App\Http\Dao\BaseDao;
use App\Http\Model\Company\UserCard;
use crmeb\traits\dao\ListSearchTrait;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\DB;

class CompanyUserCardDao extends BaseDao
{
    use ListSearchTrait;

    public function getLangUser()
    {
        return $this->getModel()->orderBy(DB::raw('convert(letter using gbk)'))->select()->get()->toArray();
    }

    /**
     * @return mixed[]
     * @throws BindingResolutionException
     * @email 136327134@qq.com
     * @date 2024/3/28
     */
    public function userIdByUserInfo(array $userId)
    {
        return $this->getModel()->whereIn('id', $userId)->select(['id', 'phone', 'email', 'entid'])->get()->toArray();
    }

    /**
     * @return mixed|string
     */
    protected function setModel()
    {
        return UserCard::class;
    }
}
