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

namespace App\Http\Model\News;

use App\Http\Model\Admin\Admin;
use App\Http\Model\BaseModel;
use crmeb\traits\model\TimeDataTrait;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Query\Builder;

/**
 * Class News.
 */
class News extends BaseModel
{
    use TimeDataTrait;

    /**
     * 表名.
     * @var string
     */
    protected $table = 'enterprise_notice';

    /**
     * 主键.
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * 一对一关联创建人.
     * @return HasOne
     */
    public function card()
    {
        return $this->hasOne(Admin::class, 'id', 'user_id')->select(['id', 'uid', 'name', 'avatar', 'phone']);
    }

    public function scopeCardId($query, $value)
    {
        if (is_array($value)) {
            $query->whereIn('card_id', $value);
        } elseif ($value !== '') {
            $query->where('card_id', $value);
        }
    }

    public function scopeTitleLike($query, $value)
    {
        if ($value !== '') {
            $query->where('title', 'like', '%' . $value . '%');
        }
    }

    /**
     * Content获取器.
     * @param mixed $value
     * @return string
     */
    public function getContentAttribute($value)
    {
        return $value ? htmlspecialchars_decode($value) : '';
    }

    /**
     * Content修改器.
     * @param mixed $value
     */
    public function setContentAttribute($value)
    {
        $this->attributes['content'] = htmlspecialchars($value);
    }

    public function scopePushType($query, $value)
    {
        if ($value !== '') {
            $query->where('push_type', $value);
        }
    }

    /**
     * @param Builder $query
     * @param mixed $value
     */
    public function scopeIsPush($query, $value)
    {
        if ($value !== '') {
            $query->where('push_time', '<', now()->toDateTimeString());
        }
    }

    /**
     * @param Builder $query
     * @param mixed $value
     */
    public function scopeStatus($query, $value)
    {
        if ($value !== '') {
            $query->where('status', $value);
        }
    }

    /**
     * @param Builder $query
     * @param mixed $value
     */
    public function scopeCateId($query, $value)
    {
        if ($value !== '') {
            $query->where('cate_id', $value);
        }
    }

    /**
     * @param Builder $query
     * @param mixed $value
     */
    public function scopeEntid($query, $value)
    {
        if ($value !== '') {
            $query->where('entid', $value);
        }
    }

    /**
     * @param Builder $query
     * @param mixed $value
     */
    public function scopeNotId($query, $value)
    {
        if (is_array($value)) {
            $query->whereNotIn('id', $value);
        } elseif ($value !== '') {
            $query->whereNotIn('id', [$value]);
        }
    }

    /**
     * @param Builder $query
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

    public function scopePushTime($query, $value)
    {
        if ($value !== '') {
            $query->whereTime('push_time', '<', $value);
        }
    }

    public function scopeDay($query, $value)
    {
        if ($value !== '') {
            $query->whereDate('created_at', $value);
        }
    }

    public function scopeEqualPushTime($query, $value)
    {
        if ($value !== '') {
            $query->whereDate('push_time', $value);
        }
    }
}
