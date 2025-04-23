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

namespace App\Console\Commands;

use App\Constants\CustomEnum\CustomEnum;
use App\Http\Service\Config\FormService;
use App\Http\Service\Open\OpenapiRuleService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpgradeCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'tl:up';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): void
    {
        if ($this->confirm('为了您的数据安全, 更新前请确认是否做好数据备份? (数据库/项目文件)', true)) {
            $this->upgrade();
        }
    }

    /**
     * 读取版本号.
     */
    protected function getVersion(string $key = ''): array|string
    {
        $version_arr    = [];
        $curent_version = @file(base_path('.version'));
        foreach ($curent_version as $val) {
            [$k, $v]         = explode('=', $val);
            $version_arr[$k] = $v;
        }
        return $key ? trim($version_arr[$key]) : $version_arr;
    }

    private function upgrade(): void
    {
        $this->info('即将开始执行升级程序...');

        // composer install
        @shell_exec('composer install');
        $this->info('Composer 安装成功!');
        $this->newLine();

        // update database migration files
        $this->info('正在检查是否需要更新数据库...');
        $version = $this->getVersion('version_num');
        $path    = database_path('migrations/v' . $version);
        if (is_dir($path)) {
            $isAllMigrationSucc = true;
            $this->newLine();
            $this->info('开始执行数据迁移...');
            $this->newLine();
            $files = opendir($path);
            while ($file = readdir($files)) {
                if ($file == '.' || $file == '..') {
                    continue;
                }

                $path = 'database/migrations/v' . $version . '/' . $file;
                $this->info('执行文件: ' . $path);
                try {
                    Artisan::call('migrate', ['--path' => $path]);
                } catch (\Throwable $e) {
                    $isAllMigrationSucc = false;
                    $this->error('数据库更新失败: ' . $e->getMessage());
                    $this->error('迁移文件地址: ' . $path);
                    $this->newLine();
                    Log::error($e->getMessage(), ['file path' => $path]);
                }
            }
            $this->newLine();
            if ($isAllMigrationSucc) {
                $this->info('恭喜, 数据库更新成功!');
            } else {
                $this->error('部分数据更新失败, 详情请查看日志');
            }
        } else {
            $this->info('数据库无需更新');
        }

        $handlerFile = database_path('seeders/v' . $version . '/DataUpdateHandler.php');
        if (file_exists($handlerFile)) {
            require_once $handlerFile;
            if (class_exists('DataUpdateHandler')) {
                app()->get('DataUpdateHandler');
            }
        }

        // update system menus
        $sqlPath = database_path('seeders/v' . $version . '.sql');
        if (file_exists($sqlPath)) {
            DB::unprepared(prefix_correction(file_get_contents($sqlPath)));
        }
        $this->info('清理缓存中...');
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        $this->info('缓存清理完成.');

        $this->newLine();
        $this->info('更新完成, 您的系统版本已成功升级为' . $version . ',建议您重启服务...');
    }
}
