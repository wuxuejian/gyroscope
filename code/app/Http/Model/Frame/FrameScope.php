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

namespace App\Http\Model\Frame;

use App\Http\Model\Admin\Admin;
use App\Http\Model\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class FrameScope.
 */
class FrameScope extends BaseModel
{
    /**
     * 表名.
     * @var string
     */
    protected $table = 'enterprise_user_scope';

    /**
     * 主键.
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * 关联类型.
     * @param mixed $query
     * @param mixed $value
     * @return mixed
     */
    public function scopeTypes($query, $value)
    {
        if ($value !== '') {
            return $query->where('types', $value);
        }
    }

    /**
     * 企业ID.
     * @param mixed $query
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
     * 用户ID.
     * @param mixed $query
     * @param mixed $value
     * @return mixed
     */
    public function scopeUid($query, $value)
    {
        if ($value !== '') {
            return $query->where('uid', $value);
        }
    }

    /**
     * 关联查询企业.
     * @return HasMany
     */
    public function frames()
    {
        return $this->hasMany(Frame::class, 'id', 'link_id');
    }

    /**
     * 关联查询用户名片.
     * @return HasMany
     */
    public function cards()
    {
        return $this->hasMany(Admin::class, 'id', 'link_id');
    }
}
