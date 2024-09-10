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

namespace App\Http\Model\Assess;

use App\Http\Model\BaseModel;
use App\Http\Model\User\UserEnterprise;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class AssessPlanUser.
 */
class AssessPlanUser extends BaseModel
{
    /**
     * 表名.
     * @var string
     */
    protected $table = 'assess_plan_user';

    /**
     * 主键.
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * 一对一关联用户.
     * @return HasOne
     */
    public function userEnt()
    {
        return $this->hasOne(UserEnterprise::class, 'id', 'user_id')->with(['card' => function ($query) {
            $query->select(['id', 'name']);
        }])->select(['id', 'card_id']);
    }

    public function scopePlanid($query, $value)
    {
        if ($value !== '') {
            $query->where('planid', $value);
        }
    }

    public function scopeTestUid($query, $value)
    {
        if ($value !== '') {
            $query->where('test_uid', $value);
        }
    }
}
