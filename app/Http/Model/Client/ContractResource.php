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

namespace App\Http\Model\Client;

use App\Http\Model\Attach\SystemAttach;
use App\Http\Model\BaseModel;
use App\Http\Model\User\UserEnterprise;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * 合同附件
 * Class ContractResource.
 */
class ContractResource extends BaseModel
{
    /**
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * @var string
     */
    protected $table = 'contract_resource';

    /**
     * 附件一对多关联.
     *
     * @return HasMany
     */
    public function attachs()
    {
        return $this->hasMany(SystemAttach::class, 'relation_id', 'id')
            ->where('relation_type', 3)->select(['id', 'att_dir as url', 'relation_id', 'real_name as name', 'att_size as size']);
    }

    /**
     * 名片.
     * @return HasOne
     */
    public function user()
    {
        return $this->hasOne(UserEnterprise::class, 'id', 'uid')->select(['id', 'name', 'avatar', 'phone']);
    }
}
