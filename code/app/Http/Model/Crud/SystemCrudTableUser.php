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

namespace App\Http\Model\Crud;

use App\Http\Model\BaseModel;
use App\Observers\SystemCrudTableUserObserver;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class SystemCrudTableUser.
 * @email 136327134@qq.com
 * @date 2024/3/9
 */
class SystemCrudTableUser extends BaseModel
{
    use SoftDeletes;

    /**
     * 表明.
     * @var string
     */
    protected $table = 'system_crud_table_user';

    /**
     * 主键.
     * @var string
     */
    protected $primaryKey = 'id';

    public static function boot()
    {
        parent::boot();
        static::observe(SystemCrudTableUserObserver::class);
    }

    /**
     * @email 136327134@qq.com
     * @date 2024/2/24
     * @param mixed $value
     */
    public function setSeniorSearchAttribute($value)
    {
        $this->attributes['senior_search'] = json_encode($value);
    }

    /**
     * @email 136327134@qq.com
     * @date 2024/2/24
     * @param mixed $value
     * @return mixed
     */
    public function getSeniorSearchAttribute($value)
    {
        return json_decode($value, true);
    }

    /**
     * @email 136327134@qq.com
     * @date 2024/2/24
     * @param mixed $value
     */
    public function setShowFieldAttribute($value)
    {
        $this->attributes['show_field'] = json_encode($value);
    }

    /**
     * @email 136327134@qq.com
     * @date 2024/2/24
     * @param mixed $value
     * @return mixed
     */
    public function getShowFieldAttribute($value)
    {
        return json_decode($value, true);
    }

    /**
     * @email 136327134@qq.com
     * @date 2024/2/24
     * @param mixed $value
     */
    public function setOptionsAttribute($value)
    {
        $this->attributes['options'] = json_encode($value);
    }

    /**
     * @email 136327134@qq.com
     * @date 2024/2/24
     * @param mixed $value
     * @return mixed
     */
    public function getOptionsAttribute($value)
    {
        return json_decode($value, true);
    }
}
