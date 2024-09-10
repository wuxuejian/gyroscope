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

namespace App\Http\Dao\Client;

use App\Http\Dao\BaseDao;
use App\Http\Model\BaseModel;
use App\Http\Model\Client\ClientLiaison;
use crmeb\traits\dao\ListSearchTrait;
use Illuminate\Contracts\Container\BindingResolutionException;

class ClientLiaisonDao extends BaseDao
{
    use ListSearchTrait;

    /**
     * 搜索.
     * @return BaseModel
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function search($where, ?bool $authWhere = null)
    {
        $value = $where['name_tel_email'] ?? '';
        if (isset($where['name_tel_email'])) {
            unset($where['name_tel_email']);
        }
        return parent::search($where, $authWhere)->where(function ($query) use ($value) {
            $query->when($value, function ($query) use ($value) {
                $query->orWhere('name', 'like', '%' . $value . '%')
                    ->orWhere('tel', 'like', '%' . $value . '%')
                    ->orWhere('mail', 'like', '%' . $value . '%');
            });
        });
    }

    /**
     * @return string
     */
    protected function setModel()
    {
        return ClientLiaison::class;
    }
}
