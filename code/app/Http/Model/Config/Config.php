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

namespace App\Http\Model\Config;

use App\Constants\CacheEnum;
use App\Http\Model\BaseModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

/**
 * Class Config.
 */
class Config extends BaseModel
{
    /**
     * 表名.
     * @var string
     */
    protected $table = 'system_config';

    /**
     * 主键.
     * @var string
     */
    protected $primaryKey = 'id';

    public static function boot()
    {
        parent::boot();
        static::updated(fn () => Cache::tags([CacheEnum::TAG_CONFIG])->flush());
        static::created(fn () => Cache::tags([CacheEnum::TAG_CONFIG])->flush());
    }

    /**
     * value修改器.
     * @param mixed $value
     */
    public function setValueAttribute($value)
    {
        $this->attributes['value'] = is_array($value) ? json_encode($value) : $value;
    }

    /**
     * 修改pid.
     * @param mixed $value
     * @param mixed $data
     */
    public function setPidAttribute($value, $data)
    {
        $this->attributes['pid'] = isset($data['path']) && is_array($data['path']) ? $data['path'][count($data['path']) - 1] : 0;
    }

    /**
     * 格式化path字段.
     * @param mixed $value
     * @return false|string[]
     */
    public function setPathAttribute($value)
    {
        $this->attributes['path'] = is_array($value) ? implode('/', $value) : $value;
    }

    /**
     * 格式化path字段.
     * @param mixed $value
     * @return false|string[]
     */
    public function getPathAttribute($value)
    {
        return $value ? array_map('intval', explode('/', $value)) : [];
    }

    /**
     * parameter 获取器.
     * @param mixed $value
     */
    public function getParameterAttribute($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    /**
     * parameter 修改器.
     * @param mixed $value
     */
    public function setParameterAttribute($value): void
    {
        $this->attributes['parameter'] = is_array($value) ? json_encode($value) : $value;
    }

    /**
     * value获取器.
     * @param mixed $value
     * @return array|int|mixed
     */
    public function getValueAttribute($value)
    {
        $value = $value && str_contains($value, '[') ? json_decode($value, true) : $value;
        if (is_array($value)) {
            if (isset($value[0]) && preg_match('/^[0-9]+$/', (string) $value[0])) {
                return array_map('intval', $value);
            }
        }
        if (is_numeric($value)) {
            return intval($value);
        }
        return $value;
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
     * 分类动态作用域
     * @param Builder $query
     * @param mixed $value
     * @return Builder
     */
    public function scopeCateId($query, $value)
    {
        if ($value) {
            return $query->where('cate_id', $value);
        }
    }
}
