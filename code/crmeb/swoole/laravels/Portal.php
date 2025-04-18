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

namespace crmeb\swoole\laravels;

use Hhxsv5\LaravelS\Console\Portal as LaravelsPortal;

/**
 * 重写.
 */
class Portal extends LaravelsPortal
{
    /**
     * 启动.
     * @return false|int
     */
    public function start()
    {
        if (! extension_loaded('swoole') && ! extension_loaded('openswoole')) {
            $this->error('LaravelS requires swoole / openswoole extension, try to `pecl install swoole` and `php --ri swoole` OR `pecl install openswoole` and `php --ri openswoole`.');
            return 1;
        }

        // Generate conf file storage/laravels.conf
        $options = $this->input->getOptions();
        if (isset($options['env']) && $options['env'] !== '') {
            $_SERVER['_ENV'] = $_ENV['_ENV'] = $options['env'];
        }
        if (isset($options['x-version']) && $options['x-version'] !== '') {
            $_SERVER['X_VERSION'] = $_ENV['X_VERSION'] = $options['x-version'];
        }

        // Load Apollo configurations to .env file
        if (! empty($options['enable-apollo'])) {
            $this->loadApollo($options);
        }

        $passOptionStr = '';
        $passOptions   = ['daemonize', 'ignore', 'x-version'];
        foreach ($passOptions as $key) {
            if (! isset($options[$key])) {
                continue;
            }
            $value = $options[$key];
            if ($value === false) {
                continue;
            }
            $passOptionStr .= sprintf('--%s%s ', $key, is_bool($value) ? '' : ('=' . $value));
        }
        $statusCode = self::runArtisanCommand($this->basePath, trim('laravels config ' . $passOptionStr));
        if ($statusCode !== 0) {
            return $statusCode;
        }

        // Here we go...
        $config = $this->getConfig();

        if (! $config['server']['ignore_check_pid'] && file_exists($config['server']['swoole']['pid_file'])) {
            $pid = (int) file_get_contents($config['server']['swoole']['pid_file']);
            if ($pid > 0 && self::kill($pid, 0)) {
                $this->warning(sprintf('Swoole[PID=%d] is already running.', $pid));
                return 1;
            }
        }

        if ($config['server']['swoole']['daemonize']) {
            $this->trace('Swoole is running in daemon mode, see "ps -ef|grep laravels".');
        } else {
            $this->trace('Swoole is running, press Ctrl+C to quit.');
        }

        (new LaravelS($config['server'], $config['laravel']))->run();

        return 0;
    }
}
