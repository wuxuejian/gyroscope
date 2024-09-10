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

namespace App\Http\Model\Assess;

use App\Http\Model\BaseModel;

/**
 * 被考核组织架构
 * Class AssessFrame.
 * @mixin \Eloquent
 */
class AssessFrame extends BaseModel
{
    /**
     * @var string
     */
    protected $table = 'assess_frame';

    /**
     * @var string
     */
    protected $primaryKey = 'id';
}
