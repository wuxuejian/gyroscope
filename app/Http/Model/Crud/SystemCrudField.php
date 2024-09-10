<?php
/**
 *  +----------------------------------------------------------------------
 *  | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
 *  +----------------------------------------------------------------------
 *  | Copyright (c) 2016~2024 https://www.crmeb.com All rights reserved.
 *  +----------------------------------------------------------------------
 *  | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
 *  +----------------------------------------------------------------------
 *  | Author: CRMEB Team <admin@crmeb.com>
 *  +----------------------------------------------------------------------.
 */

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

namespace App\Http\Model\Crud;

use App\Http\Model\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class SystemCrudField extends BaseModel
{
    use SoftDeletes;

    /**
     * 表名.
     * @var string
     */
    protected $table = 'system_crud_field';

    /**
     * 主键.
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * @email 136327134@qq.com
     * @date 2024/2/24
     */
    public function setAssociationFieldNamesAttribute($value)
    {
        $this->attributes['association_field_names'] = json_encode($value);
    }

    /**
     * @return mixed
     * @email 136327134@qq.com
     * @date 2024/2/24
     */
    public function getAssociationFieldNamesAttribute($value)
    {
        return json_decode($value, true);
    }

    /**
     * @email 136327134@qq.com
     * @date 2024/2/24
     */
    public function setOptionsAttribute($value)
    {
        $this->attributes['options'] = json_encode($value);
    }

    /**
     * @return mixed
     * @email 136327134@qq.com
     * @date 2024/2/24
     */
    public function getOptionsAttribute($value)
    {
        return json_decode($value, true);
    }

    /**
     * @email 136327134@qq.com
     * @date 2024/3/7
     */
    public function scopeFieldName($query, $value)
    {
        if ($value !== '') {
            $query->where(fn ($q) => $q->where('field_name', 'like', '%' . $value . '%')
                ->orWhere('field_name_en', 'like', '%' . $value . '%'));
        }
    }

    /**
     * not field.
     * @email 136327134@qq.com
     * @date 2024/3/13
     */
    public function scopeNotField($query, $value)
    {
        if ($value) {
            if (is_array($value)) {
                $query->whereNotIn('field_name_en', $value);
            } else {
                $query->where('field_name_en', '<>', $value);
            }
        }
    }

    /**
     * 获取关联字段.
     * @return HasMany
     * @email 136327134@qq.com
     * @date 2024/3/7
     */
    public function associationField()
    {
        return $this->hasMany(self::class, 'association_crud_id', 'crud_id');
    }

    /**
     * @return HasOne
     * @email 136327134@qq.com
     * @date 2024/3/9
     */
    public function association()
    {
        return $this->hasOne(SystemCrud::class, 'id', 'association_crud_id');
    }

    /**
     * 关联实体.
     * @return HasOne
     * @email 136327134@qq.com
     * @date 2024/3/13
     */
    public function crud()
    {
        return $this->hasOne(SystemCrud::class, 'id', 'crud_id');
    }
}
