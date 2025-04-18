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
use Illuminate\Database\Eloquent\Builder;

/**
 * Class CompanyConfig.
 */
class CompanyConfig extends BaseModel
{
    /**
     * 表名.
     * @var string
     */
    protected $table = 'enterprise_config';

    /**
     * 主键.
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * value修改器.
     * @param mixed $value
     */
    public function setValueAttribute($value)
    {
        $this->attributes['value'] = json_encode($value);
    }

    /**
     * parameter 获取器.
     * @param mixed $value
     */
    public function getParameterAttribute($value)
    {
        return htmlspecialchars_decode($value);
    }

    /**
     * value获取器.
     * @param mixed $value
     * @return mixed
     */
    public function getValueAttribute($value)
    {
        return json_decode($value, true);
    }

    /**
     * 配置动态作用域
     * @param Builder $query
     * @param mixed $value
     * @return Builder
     */
    public function scopeKey($query, $value)
    {
        if (is_array($value)) {
            return $query->whereIn('key', $value);
        }
        if ($value) {
            return $query->where('key', $value);
        }
    }

    /**
     * 配置动态作用域
     * @param Builder $query
     * @param mixed $value
     * @return Builder
     */
    public function scopeEntid($query, $value)
    {
        if ($value) {
            return $query->where('entid', $value);
        }
    }

    /**
     * 分类动态作用域
     * @param Builder $query
     * @param mixed $value
     * @return Builder
     */
    public function scopeCategory($query, $value)
    {
        if ($value) {
            return $query->where('category', $value);
        }
    }
}
