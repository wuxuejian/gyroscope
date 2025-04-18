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

use App\Http\Model\Admin\Admin;
use App\Http\Model\BaseModel;
use App\Http\Model\Company\Company;
use crmeb\traits\model\TimeDataTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * 企业消息
 * Class MessageNotice.
 */
class MessageNotice extends BaseModel
{
    use TimeDataTrait;

    /**
     * 自动写入时间戳.
     * @var bool
     */
    public $timestamps = false;

    /**
     * 表名.
     * @var string
     */
    protected $table = 'enterprise_message_notice';

    /**
     * 主键.
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * 一对一关联企业.
     * @return HasOne
     */
    public function enterprise()
    {
        return $this->hasOne(Company::class, 'id', 'entid');
    }

    /**
     * 一对一关联用户.
     * @return HasOne
     */
    public function user()
    {
        return $this->hasOne(Admin::class, 'id', 'to_uid');
    }

    public function scopeIds($query, $value)
    {
        $query->whereIn('id', $value);
    }

    /**
     * @param mixed $value
     * @return array|mixed
     */
    public function getButtonTemplateAttribute($value)
    {
        return $value ? json_decode($value, true) : null;
    }

    public function setButtonTemplateAttribute($value)
    {
        $this->attributes['button_template'] = json_encode($value);
    }

    /**
     * @param mixed $value
     * @return array|mixed
     */
    public function getOtherAttribute($value)
    {
        return $value ? json_decode($value, true) : null;
    }

    public function setOtherAttribute($value)
    {
        $this->attributes['other'] = json_encode($value);
    }

    public function scopeUid($query, $value)
    {
        if (is_array($value)) {
            $query->whereIn('to_uid', $value);
        }
    }

    public function scopeOtherId($query, $value)
    {
        if ($value !== '') {
            $query->where('other->id', $value);
        }
    }

    public function scopeLinkId($query, $value)
    {
        if (is_array($value)) {
            $query->where('link_id', $value);
        } elseif ($value !== '') {
            $query->where('link_id', $value);
        }
    }

    public function scopeTemplateType($query, $value)
    {
        if (is_array($value)) {
            $query->whereIn('template_type', $value);
        } else {
            $query->where('template_type', $value);
        }
    }

    /**
     * 消息类型作用域
     * @param Builder $query
     * @param mixed $value
     * @return Builder
     */
    public function scopeCateId($query, $value)
    {
        if (is_array($value)) {
            $query->whereIn('cate_id', $value);
        } elseif ($value !== '') {
            $query->where('cate_id', $value);
        }
    }

    public function scopeToUid($query, $value)
    {
        if (is_array($value)) {
            $query->whereIn('to_uid', $value);
        } elseif ($value !== '') {
            $query->where('to_uid', $value);
        }
    }

    /**
     * 关联查询消息模板
     */
    public function template(): HasOne
    {
        return $this->hasOne(MessageTemplate::class, 'message_id', 'message_id')->where('type', 0);
    }
}
