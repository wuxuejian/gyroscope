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

namespace App\Http\Model\Chat;

use App\Http\Model\Admin\Admin;
use App\Http\Model\BaseModel;
use crmeb\traits\model\TimeDataTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class ClientBill.
 */
class ChatApplications extends BaseModel
{
    use TimeDataTrait;
    use SoftDeletes;

    /**
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * @var string
     */
    protected $table = 'chat_applications';

    /**
     * 修改权限.
     * @param mixed $value
     * @return mixed
     */
    public function getEditAttribute($value)
    {
        if ($value) {
            return explode('/', trim($value, '/'));
        }
        return [];
    }

    /**
     * 修改权限.
     * @param mixed $value
     * @return mixed
     */
    public function setEditAttribute($value)
    {
        $this->attributes['edit'] = '/' . implode('/', $value) . '/';
    }

    public function getAuthIdsAttribute($value)
    {
        if ($value) {
            return $value ? json_decode($value, true) : [];
        }
        return [];
    }

    public function setAuthIdsAttribute($value)
    {
        $this->attributes['auth_ids'] = json_encode($value);
    }

    public function getJsonAttribute($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    public function setJsonAttribute($value)
    {
        $this->attributes['json'] = json_encode($value);
    }

    public function getKeywordAttribute($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    public function setKeywordAttribute($value)
    {
        $this->attributes['keyword'] = json_encode($value);
    }

    public function getTablesAttribute($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    public function setTablesAttribute($value)
    {
        $this->attributes['tables'] = json_encode($value);
    }

    public function getPrologueListAttribute($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    public function setPrologueListAttribute($value)
    {
        $this->attributes['prologue_list'] = json_encode($value);
    }

    public function auth()
    {
        return $this->hasMany(ChatAppAuth::class, 'app_id', 'id');
    }

    public function user()
    {
        return $this->hasOne(Admin::class, 'id', 'uid')->select(['id', 'name', 'avatar', 'uid', 'phone']);
    }
}
