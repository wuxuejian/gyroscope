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

namespace crmeb\services\upload;

use crmeb\services\Manager;
use Illuminate\Support\Facades\Config;

/**
 * Class Upload.
 * @mixin \crmeb\services\upload\storage\Local
 * @mixin \crmeb\services\upload\storage\OSS
 * @mixin \crmeb\services\upload\storage\COS
 * @mixin \crmeb\services\upload\storage\Qiniu
 * @mixin \crmeb\services\upload\storage\Jdoss
 * @mixin \crmeb\services\upload\storage\Tyoss
 */
class Upload extends Manager
{
    /**
     * 空间名.
     * @var string
     */
    protected $namespace = '\crmeb\services\upload\storage\\';

    /**
     * 设置默认上传类型.
     * @return mixed
     */
    protected function getDefaultDriver()
    {
        return Config::get('upload.default', 'local');
    }
}
