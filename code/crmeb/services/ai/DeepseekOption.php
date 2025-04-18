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

namespace crmeb\services\ai;

/**
 * 深度搜索.
 */
class DeepseekOption extends BaseOption
{
    public const MODEL_OPTIONS = [
        'deepseek-chat' => [
            'min' => 1,
            'max' => 8192,
        ],
        'deepseek-reasoner' => [
            'min' => 1,
            'max' => 8192,
        ],
    ];

    public $baseUrl = 'https://api.deepseek.com';

    /**
     * @var string
     */
    public $url = '/chat/completions';

    /**
     * 模型名称.
     * @var string
     */
    public $model = 'deepseek-chat';

    public $stream = true;

    /**
     * @var true[]
     */
    public $streamOptions = [
        'include_usage' => true,
    ];
}
