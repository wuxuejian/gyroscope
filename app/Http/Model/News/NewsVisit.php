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

namespace App\Http\Model\News;

use App\Http\Model\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class NewsVisit.
 */
class NewsVisit extends BaseModel
{
    /**
     * 表名.
     * @var string
     */
    protected $table = 'enterprise_notice_visit';

    /**
     * 主键.
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * 一对一关联通知.
     * @param mixed $query
     * @param mixed $value
     * @return HasOne
     */
    public function scopeNoticeId($query, $value)
    {
        if ($value !== '') {
            $query->where('notice_id', $value);
        }
    }

    /**
     * 一对一关联创建人.
     * @param mixed $query
     * @param mixed $value
     * @return HasOne
     */
    public function scopeUuid($query, $value)
    {
        if ($value !== '') {
            $query->where('uuid', $value);
        }
    }
}
