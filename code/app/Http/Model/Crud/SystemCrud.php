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

use App\Http\Model\Admin\Admin;
use App\Http\Model\BaseModel;
use App\Observers\SystemCrudObserver;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class SystemCrud.
 * @email 136327134@qq.com
 * @date 2024/2/26
 */
class SystemCrud extends BaseModel
{
    use SoftDeletes;

    /**
     * 表明.
     * @var string
     */
    protected $table = 'system_crud';

    /**
     * 主键.
     * @var string
     */
    protected $primaryKey = 'id';

    public static function boot()
    {
        parent::boot();
        static::observe(SystemCrudObserver::class);
    }

    /**
     * 字段表.
     * @return HasMany
     * @email 136327134@qq.com
     * @date 2024/2/24
     */
    public function field()
    {
        return $this->hasMany(SystemCrudField::class, 'crud_id', 'id');
    }

    /**
     * 表单信息.
     * @return HasOne
     * @email 136327134@qq.com
     * @date 2024/2/26
     */
    public function form()
    {
        return $this->hasOne(SystemCrudForm::class, 'id', 'crud_id');
    }

    /**
     * 表格信息.
     * @return HasOne
     * @email 136327134@qq.com
     * @date 2024/2/26
     */
    public function table()
    {
        return $this->hasOne(SystemCrudTable::class, 'id', 'crud_id');
    }

    /**
     * 辅助表.
     * @return HasMany
     * @email 136327134@qq.com
     * @date 2024/2/26
     */
    public function children()
    {
        return $this->hasMany(self::class, 'crud_id', 'id');
    }

    /**
     * 辅助表.
     * @return HasOne
     * @email 136327134@qq.com
     * @date 2024/2/26
     */
    public function child()
    {
        return $this->hasOne(self::class, 'crud_id', 'id');
    }

    /**
     * 创建者.
     * @return HasOne
     * @email 136327134@qq.com
     * @date 2024/2/26
     */
    public function user()
    {
        return $this->hasOne(Admin::class, 'id', 'user_id');
    }

    /**
     * 关联事件.
     * @return HasMany
     * @email 136327134@qq.com
     * @date 2024/2/28
     */
    public function event()
    {
        return $this->hasMany(SystemCrudEvent::class, 'id', 'crud_id')->orderByDesc('sort')->orderByDesc('id');
    }

    /**
     * 关联流程.
     * @return HasMany
     * @email 136327134@qq.com
     * @date 2024/2/28
     */
    public function approve()
    {
        return $this->hasMany(SystemCrudApprove::class, 'id', 'crud_id');
    }

    /**
     * @email 136327134@qq.com
     * @date 2024/3/5
     * @param mixed $value
     * @return array
     */
    public function getCateIdsAttribute($value)
    {
        $value = explode('/', $value);
        return array_map('intval', array_merge(array_filter($value)));
    }

    /**
     * 关联查询权限.
     * @return HasOne
     */
    public function role()
    {
        return $this->hasOne(SystemCrudRole::class, 'crud_id', 'id');
    }

    /**
     * @email 136327134@qq.com
     * @date 2024/2/24
     * @param mixed $value
     */
    public function setFormFieldsAttribute($value)
    {
        $this->attributes['form_fields'] = json_encode($value);
    }

    /**
     * @email 136327134@qq.com
     * @date 2024/2/24
     * @param mixed $value
     * @return mixed
     */
    public function getFormFieldsAttribute($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    public function scopeNotName($query, $value)
    {
        $query->whereNotIn('table_name_en', $value);
    }
}
