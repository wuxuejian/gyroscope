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

namespace App\Http\Model\Config;

use App\Constants\Crud\CrudFormEnum;
use App\Http\Model\BaseModel;

/**
 * 字典类型.
 */
class DictType extends BaseModel
{
    protected $table = 'dict_type';

    /**
     * 主键.
     * @var string
     */
    protected $primaryKey = 'id';

    public function scopeId($query, $value)
    {
        if (is_array($value)) {
            $query->whereIn('id', $value);
        } elseif ($value !== '') {
            $query->where('id', $value);
        }
    }

    public function scopeNotId($query, $value)
    {
        if (is_array($value)) {
            $query->whereNotIn('id', $value);
        } elseif ($value !== '') {
            $query->whereNot('id', $value);
        }
    }

    public function scopeLinkType($query, $value)
    {
        if ($value !== '') {
            $query->where('link_type', $value);
        }
    }

    public function scopeLevel($query, $value)
    {
        if ($value !== '') {
            $query->where('level', $value);
        }
    }

    public function scopeNameLike($query, $value)
    {
        $query->where(fn ($q) => $q->orWhere('name', 'like', "%{$value}%")->orWhere('ident', 'like', "%{$value}%"));
    }

    public function scopeCrudId($query, $value)
    {
        $query->whereIn('id', fn ($q) => $q->from('system_crud_field')->where('crud_id', $value)->select('data_dict_id'));
    }

    public function scopeCateId($query, $value)
    {
        $query->whereIn('id', function ($query) use ($value) {
            $query->from('system_crud_field')
                ->whereIn('crud_id', fn ($q) => $q->from('system_crud')->where('cate_ids', 'like', '%/' . $value . '/%')->select('id'))
                ->select('data_dict_id');
        });
    }

    public function scopeFormValue($query, $value)
    {
        if (in_array($value, [CrudFormEnum::FORM_RADIO, CrudFormEnum::FORM_CHECKBOX])) {
            $query->where('level', 1);
        } elseif ($value === CrudFormEnum::FORM_TAG) {
            $query->where('level', 2);
        }
    }

    public function data()
    {
        $this->hasMany(DictData::class, 'type_id', 'id');
    }
}
