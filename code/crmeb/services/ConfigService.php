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

namespace crmeb\services;

use App\Constants\CacheEnum;
use crmeb\interfaces\ConfigInterface;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Cache;

/**
 * 系统配置
 * Class ConfigService.
 */
class ConfigService
{
    public const CONFIG_KEY_GAT = 'config_tl:';

    /**
     * 配置services类名.
     * @var string
     */
    protected $configServiceClass;

    protected $entid;

    /**
     * 缓存时间.
     * @var int
     */
    protected $cacheTime;

    public function __construct(string $service, int $cacheTime = 3600)
    {
        $this->configServiceClass = $service;
        $this->cacheTime          = $cacheTime;
    }

    /**
     * 实例化本类.
     * @param mixed $name
     * @return ConfigService
     * @throws BindingResolutionException
     */
    public static function instance($name = 'config_crmeb')
    {
        return app()->get($name);
    }

    /**
     * 获取services实例.
     * @return ConfigInterface
     * @throws BindingResolutionException
     */
    public function getConfigService()
    {
        return app()->get($this->configServiceClass);
    }

    /**
     * 获取单个配置.
     * @param null $default
     * @return null|mixed
     */
    public function get(string $key, $default = null, bool $isSet = false, bool $nowConfig = false)
    {
        $name           = self::CONFIG_KEY_GAT . $key;
        $configCallable = function () use ($key, $default, $isSet) {
            try {
                $value = $this->getConfigService()->getConfig($key, $default, $isSet);
                if (is_null($value)) {
                    return $default;
                }
                return $value;
            } catch (\Throwable) {
                return $default;
            }
        };

        if ($nowConfig || $isSet) {
            return $configCallable();
        }

        try {
            return Cache::tags([CacheEnum::TAG_CONFIG])->remember($name, $this->cacheTime, $configCallable);
        } catch (\Throwable) {
            return $configCallable();
        }
    }

    /**
     * 获取多个配置.
     * @return array|mixed
     * @throws BindingResolutionException
     */
    public function more(array $moreKes, bool $nowConfig = false)
    {
        $name           = self::CONFIG_KEY_GAT . md5(json_encode($moreKes));
        $configCallable = function () use ($moreKes) {
            try {
                return $this->getConfigService()->getConfigs($moreKes);
            } catch (\Throwable) {
                return $this->getMoreValue($moreKes);
            }
        };
        if ($nowConfig) {
            return $configCallable();
        }
        try {
            return Cache::tags([CacheEnum::TAG_CONFIG])->remember($name, $this->cacheTime, $configCallable);
        } catch (\Throwable) {
            return $configCallable();
        }
    }

    /**
     * 对数组增加默认值
     * @return array
     */
    private function getDefaultValue(array $keys, array $configList = [])
    {
        $value = [];
        foreach ($keys as $val) {
            if (is_array($val)) {
                $k = $val[0] ?? '';
                $v = $val[1] ?? '';
            } else {
                $k = $val;
                $v = '';
            }
            $value[$k] = $configList[$k] ?? $v;
        }
        return $value;
    }

    private function getMoreValue(array $keys)
    {
        $value = [];
        foreach ($keys as $key) {
            $value[$key] = '';
        }
        return $value;
    }
}
