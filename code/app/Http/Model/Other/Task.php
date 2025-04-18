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

namespace App\Http\Model\Other;

use App\Http\Model\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 任务
 * Class Task.
 */
class Task extends BaseModel
{
    use SoftDeletes;

    public const DELETED_AT = 'delete';

    /**
     * 表名.
     * @var string
     */
    protected $table = 'task';

    /**
     * 主键.
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * interval修改器.
     * @param mixed $value
     */
    public function setIntervalAttribute($value)
    {
        $this->attributes['interval'] = json_encode($value);
    }

    /**
     * interval提取.
     * @param mixed $value
     * @return mixed
     */
    public function getIntervalAttribute($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    /**
     * parameter修改器.
     * @param mixed $value
     */
    public function setParameterAttribute($value)
    {
        $this->attributes['parameter'] = json_encode($value);
    }

    /**
     * parameter提取.
     * @param mixed $value
     * @return mixed
     */
    public function getParameterAttribute($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    /**
     * event提取.
     * @return array
     */
    public function getEventAttribute()
    {
        return [$this->class_name, $this->action];
    }

    /**
     * 搜索.
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeUniqued($query, $value)
    {
        if ($value !== '') {
            $query->where('uniqued', $value);
        }
    }
}
