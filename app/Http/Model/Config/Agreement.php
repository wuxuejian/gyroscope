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

use App\Http\Model\BaseModel;

class Agreement extends BaseModel
{
    /**
     * 表名.
     * @var string
     */
    protected $table = 'agreement';

    /**
     * 主键.
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * data获取器.
     * @param mixed $value
     * @return string
     */
    public function getContentAttribute($value)
    {
        return htmlspecialchars_decode($value);
    }

    /**
     * data修改器.
     * @param mixed $value
     */
    public function setContentAttribute($value)
    {
        $this->attributes['content'] = htmlspecialchars($value);
    }
}
