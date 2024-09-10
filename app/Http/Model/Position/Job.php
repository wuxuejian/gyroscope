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

namespace App\Http\Model\Position;

use App\Http\Model\Admin\Admin;
use App\Http\Model\BaseModel;
use crmeb\traits\model\PathAttrTrait;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Query\Builder;

/**
 * 企业岗位
 * Class Job.
 */
class Job extends BaseModel
{
    use PathAttrTrait;

    /**
     * 表名.
     * @var string
     */
    protected $table = 'rank_job';

    /**
     * 主键.
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * 一对一关联职级分类.
     * @return HasOne
     */
    public function cate()
    {
        return $this->hasOne(Category::class, 'id', 'cate_id');
    }

    /**
     * 一对一关联职级.
     * @return HasOne
     */
    public function rank()
    {
        return $this->hasOne(Position::class, 'id', 'rank_id');
    }

    /**
     * 一对一关联创建人.
     * @return HasOne
     */
    public function card()
    {
        return $this->hasOne(Admin::class, 'id', 'user_id');
    }

    /**
     * duty获取器.
     * @return string
     */
    public function getDutyAttribute($value)
    {
        return htmlspecialchars_decode($value);
    }

    /**
     * Duty修改器.
     */
    public function setDutyAttribute($value)
    {
        $this->attributes['duty'] = htmlspecialchars($value);
    }

    /**
     * name作用域
     * @param Builder $query
     * @return mixed
     */
    public function scopeNameLike($query, $value)
    {
        if ($value) {
            return $query->where('name', 'like', '%' . $value . '%');
        }
    }

    /**
     * rank_id作用域
     * @param Builder $query
     * @return mixed
     */
    public function scopeRankId($query, $value)
    {
        if ($value !== '') {
            $query->where('rank_id', $value);
        }
    }

    /**
     * id作用域
     * @param Builder $query
     * @return mixed
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
     * entid作用域
     * @param Builder $query
     * @return mixed
     */
    public function scopeEntid($query, $value)
    {
        if ($value !== '') {
            $query->where('entid', $value);
        }
    }
}
