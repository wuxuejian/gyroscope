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

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\TransformsRequest;

/**
 * 过滤参数
 * Class FilterStrings.
 */
class FilterStrings extends TransformsRequest
{
    /**
     * 过滤方法名.
     * @var string[]
     */
    protected $filter = ['addslashes', 'trim', 'e'];

    /**
     * 排除那些值
     * @var array
     */
    protected $except = [
        'password',
        'password_confirmation',
        'password_confirm',
        'confirm_password',
        'introduce',
        'duty',
        'content',
        'standard',
    ];

    /**
     * 过滤form.
     * @param string $key
     * @param mixed $value
     * @return array|mixed
     */
    protected function transform($key, $value)
    {
        if (in_array($key, $this->except, true)) {
            return $value;
        }
        if (str_contains(request()->path(), 'crud')) {
            return $value;
        }
        if (is_array($value)) {
            return $value;
        }
        foreach ($this->filter as $closure) {
            if (function_exists($closure) && ! is_bool($value) && $value !== '') {
                $value = $closure((string) $value);
            }
        }
        return $value;
    }
}
