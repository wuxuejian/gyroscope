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

namespace App\Http\Model\Assess;

use App\Http\Model\Admin\Admin;
use App\Http\Model\BaseModel;
use crmeb\traits\model\TimeDataTrait;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class AssessReply.
 */
class AssessReply extends BaseModel
{
    use TimeDataTrait;

    /**
     * 表名.
     * @var string
     */
    protected $table = 'assess_reply';

    /**
     * 主键.
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * 一对一关联用户.
     * @return HasOne
     */
    public function user()
    {
        return $this->hasOne(Admin::class, 'id', 'user_id')->select(['id', 'name', 'avatar', 'uid', 'phone']);
    }

    /**
     * id作用域
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeId($query, $value)
    {
        if (is_array($value)) {
            $query->whereIn('id', $value);
        } elseif ($value !== '') {
            $query->where('id', $value);
        }
    }

    /**
     * is_own作用域
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeIsOwn($query, $value)
    {
        if (is_array($value)) {
            $query->whereIn('is_own', $value);
        } elseif ($value !== '') {
            $query->where('is_own', $value);
        }
    }
}
