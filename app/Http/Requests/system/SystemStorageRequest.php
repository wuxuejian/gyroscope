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

namespace App\Http\Requests\system;

use App\Http\Requests\ApiRequest;

class SystemStorageRequest extends ApiRequest
{
    /**
     * 开启自动验证
     * @var bool
     */
    public $authValidate = true;

    /**
     * 规则.
     * @var string[]
     */
    protected $rules = [
        'thumb_big_height'    => 'number|egt:0',
        'thumb_big_width'     => 'number|egt:0',
        'thumb_mid_height'    => 'number|egt:0',
        'thumb_mid_width'     => 'number|egt:0',
        'thumb_small_height'  => 'number|egt:0',
        'thumb_small_width'   => 'number|egt:0',
        'watermark_opacity'   => 'number|between:0,100',
        'watermark_text'      => 'chsAlphaNum|length:1,10',
        'watermark_text_size' => 'number|egt:0',
        'watermark_x'         => 'number|egt:0',
        'watermark_y'         => 'number|egt:0',
    ];

    /**
     * 提示.
     * @var string[]
     */
    protected $message = [
        'thumb_big_height.number'    => '缩略图大图尺寸（高）必须为数字',
        'thumb_big_height.egt'       => '缩略图大图尺寸（高）必须大于等于0',
        'thumb_big_width.number'     => '缩略图大图尺寸（宽）必须为数字',
        'thumb_big_width.egt'        => '缩略图大图尺寸（宽）必须大于等于0',
        'thumb_mid_height.number'    => '缩略图中图尺寸（高）必须为数字',
        'thumb_mid_height.egt'       => '缩略图中图尺寸（高）必须大于等于0',
        'thumb_mid_width.number'     => '缩略图中图尺寸（宽）必须为数字',
        'thumb_mid_width.egt'        => '缩略图中图尺寸（宽）必须大于等于0',
        'thumb_small_height.number'  => '缩略图小图尺寸（高）必须为数字',
        'thumb_small_height.egt'     => '缩略图小图尺寸（高）必须大于等于0',
        'thumb_small_width.number'   => '缩略图小图尺寸（宽）必须为数字',
        'thumb_small_width.egt'      => '缩略图小图尺寸（宽）必须大于等于0',
        'watermark_text.chsAlphaNum' => '水印文字只能是汉字、字母、数字',
        'watermark_text.length'      => '水印文字长度为1-10位',
        'watermark_text_size.number' => '水印文字大小必须为数字',
        'watermark_text_size.egt'    => '水印文字大小必须大于等于0',
        'watermark_x.number'         => '水印横坐标偏移量必须为数字',
        'watermark_x.egt'            => '水印横坐标偏移量必须大于等于0',
        'watermark_y.number'         => '水印纵坐标偏移量必须为数字',
        'watermark_y.egt'            => '水印纵坐标偏移量必须大于等于0',
    ];
}
