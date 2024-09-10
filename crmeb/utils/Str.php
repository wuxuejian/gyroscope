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

namespace crmeb\utils;

class Str
{
    /**
     * 过滤字段.
     * @param mixed $value
     * @return mixed
     */
    public static function filterValue($value, array $filter = [])
    {
        $filter = $filter ?: ['strip_tags', 'addslashes', 'trim', 'htmlspecialchars'];
        foreach ($filter as $closure) {
            if (function_exists($closure)) {
                $value = $closure($value);
            }
        }
        return $value;
    }
}
