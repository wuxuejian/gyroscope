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

namespace crmeb\traits;

/**
 * 增加版本验证
 */
trait TokenTrait
{
    private string $assessTokenKey = 'ff75b0e6-7785-424b-b6eb-f2f1c3ba7089';

    public function getHeader(array $header = []): array
    {
        $header['version']     = $this->getVersion('version_code');
        $header['versionCode'] = password_hash($this->assessTokenKey, PASSWORD_BCRYPT);
        return $header;
    }

    /**
     * 读取版本号.
     * @param mixed|string $key
     */
    protected function getVersion(string $key = ''): array|string
    {
        $version_arr    = [];
        $curent_version = @file(base_path('.version'));
        foreach ($curent_version as $val) {
            [$k, $v]         = explode('=', $val);
            $version_arr[$k] = $v;
        }
        if ($key) {
            return trim($version_arr[$key]);
        }
        return $version_arr;
    }
}
