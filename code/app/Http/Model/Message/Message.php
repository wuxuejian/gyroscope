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

namespace App\Http\Model\Message;

use App\Http\Model\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * 消息
 * Class Message.
 */
class Message extends BaseModel
{
    /**
     * @var string
     */
    protected $table = 'message';

    /**
     * @var string
     */
    protected $primaryKey = 'id';

    public function setTemplateVarAttribute($value)
    {
        $this->attributes['template_var'] = is_array($value) ? json_encode($value) : $value;
    }

    public function getTemplateVarAttribute($value)
    {
        return $value ? json_decode($value, true) : '';
    }

    /**
     * 获取多个模板
     * @return HasMany
     */
    public function messageTemplate()
    {
        return $this->hasMany(MessageTemplate::class, 'message_id', 'id');
    }

    /**
     * 获取单个消息模板
     * @return HasOne
     */
    public function messageTemplateOne()
    {
        return $this->hasOne(MessageTemplate::class, 'message_id', 'id');
    }

    /**
     * 搜索.
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeTitle($query, $value)
    {
        if ($value !== '') {
            $query->where(fn ($q) => $q->where('title', 'like', '%' . $value . '%')->orWhere('content', 'like', '%' . $value . '%'));
        }
    }

    /**
     * 搜索.
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeIds($query, $value)
    {
        if (is_array($value)) {
            $query->whereIn('id', $value);
        }
    }

    /**
     * template_type 作用域
     * @param mixed $query
     * @param mixed $value
     */
    public function scopeTemplateType($query, $value): void
    {
        if (is_array($value)) {
            $query->whereIn('template_type', $value);
        } elseif ($value !== '') {
            $query->where('template_type', $value);
        }
    }
}
