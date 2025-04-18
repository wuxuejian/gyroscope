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

namespace App\Http\Model\Company;

use App\Http\Model\BaseModel;
use Illuminate\Database\Query\Builder;

/**
 * 企业邀请.
 */
class CompanyInvite extends BaseModel
{
    /**
     * 表名.
     * @var string
     */
    protected $table = 'user_enterprise_invite';

    /**
     * 主键.
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * uniqued作用域
     * @param Builder $query
     * @param mixed $value
     * @return mixed
     */
    public function scopeStatus($query, $value)
    {
        if ($value !== '') {
            return $query->where('uniqued', $value);
        }
    }

    /**
     * status作用域
     * @param Builder $query
     * @param mixed $value
     * @return mixed
     */
    public function scopeEntid($query, $value)
    {
        if ($value !== '') {
            return $query->where('entid', $value);
        }
    }

    /**
     * frame_id作用域
     * @param Builder $query
     * @param mixed $value
     * @return mixed
     */
    public function scopeFrameId($query, $value)
    {
        if ($value !== '') {
            return $query->where('frame_id', $value);
        }
    }
}
