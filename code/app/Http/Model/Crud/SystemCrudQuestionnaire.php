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
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * 问卷调查.
 */
class SystemCrudQuestionnaire extends BaseModel
{
    /**
     * 表名.
     * @var string
     */
    protected $table = 'system_crud_questionnaire';

    /**
     * 主键.
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * 用户.
     * @return HasOne
     */
    public function user()
    {
        return $this->hasOne(Admin::class, 'id', 'user_id');
    }

    /**
     * crud.
     * @return HasOne
     */
    public function crud()
    {
        return $this->hasOne(SystemCrud::class, 'id', 'crud_id');
    }
}
