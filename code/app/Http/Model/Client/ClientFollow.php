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

namespace App\Http\Model\Client;

use App\Http\Model\Admin\Admin;
use App\Http\Model\BaseModel;
use App\Http\Model\System\Attach;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class ClientFollow.
 */
class ClientFollow extends BaseModel
{
    use SoftDeletes;

    /**
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * @var string
     */
    protected $table = 'client_follow';

    /**
     * 一对一关联客户.
     * @return HasOne
     */
    public function client()
    {
        return $this->hasOne(Customer::class, 'id', 'eid')->select([
            'customer.id',
            'customer.contract_name',
        ]);
    }

    /**
     * 一对一远程关联用户.
     * @return HasOne
     */
    public function card()
    {
        return $this->hasOne(Admin::class, 'id', 'user_id')->select(['id', 'uid', 'name', 'avatar', 'phone']);
    }

    /**
     * @return HasMany
     */
    public function file()
    {
        return $this->hasMany(ClientFile::class, 'fid', 'id');
    }

    public function setFilesAttribute($value)
    {
        $this->attributes['files'] = is_array($value) ? json_encode($value) : '';
    }

    public function getFilesAttribute($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    public function scopeEid($query, $value)
    {
        if (is_array($value)) {
            $query->whereIn('eid', $value);
        } elseif ($value !== '') {
            $query->where('eid', $value);
        }
    }

    public function scopeTypes($query, $value)
    {
        if ($value !== '') {
            $query->where('types', $value);
        }
    }

    public function scopeUniqued($query, $value)
    {
        if ($value !== '') {
            $query->where('uniqued', $value);
        }
    }

    public function scopeId($query, $value)
    {
        if ($value !== '') {
            $query->where('id', $value);
        }
    }

    /**
     * 附件一对多关联.
     *
     * @return HasMany
     */
    public function attachs()
    {
        return $this->hasMany(Attach::class, 'relation_id', 'id')
            ->where('relation_type', 5)->select(['id', 'att_dir as url', 'relation_id', 'name', 'real_name', 'att_type']);
    }

    /**
     * time作用域
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeTimeLt($query, $value)
    {
        if ($value !== '') {
            $query->where('time', '<', $value);
        }
    }

    /**
     * user_id作用域
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeUserId($query, $value)
    {
        if (is_array($value)) {
            $query->whereIn('user_id', $value);
        } elseif ($value !== '') {
            $query->where('user_id', $value);
        }
    }
}
