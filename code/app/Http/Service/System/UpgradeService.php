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

namespace App\Http\Service\System;

use App\Constants\CacheEnum;
use App\Http\Dao\System\UpgradeLogDao;
use App\Http\Service\BaseService;
use App\Task\frame\upgrade\BackupJob;
use App\Task\frame\upgrade\DownloadJob;
use crmeb\exceptions\AdminException;
use crmeb\services\FileService;
use crmeb\services\HttpService;
use crmeb\utils\fileVerification;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use SwooleTW\Http\Server\Facades\Server;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class UpgradeService extends BaseService
{
    protected string $upgradeHost = 'http://upgrade.crmeb.net';

    protected string $loginApi = '/api/login';

    protected string $logApi = '/api/upgrade/log';

    protected string $listApi = '/api/upgrade/list';

    protected string $statusApi = '/api/upgrade/status';

    protected string $ableApi = '/api/upgrade/current_list';

    protected string $downloadApi = '/api/upgrade/download';

    protected string $agreementApi = '/api/upgrade/agreement';

    private array $requestData = [];

    private int $timeStamp;

    private HttpService $httpService;

    private array $versionData;

    public function __construct(UpgradeLogDao $dao, HttpService $httpService)
    {
        $versionData = $this->getVersionData();
        if (empty($versionData)) {
            throw new AdminException('授权信息丢失');
        }
        $this->dao         = $dao;
        $this->timeStamp   = time();
        $this->httpService = $httpService;
        $this->versionData = $versionData;
        $this->requestData = [
            'nonce'     => mt_rand(111, 999),
            'host'      => app('request')->getHost(),
            'timestamp' => $this->timeStamp,
            'app_id'    => trim($this->versionData['app_id'] ?? ''),
            'app_key'   => trim($this->versionData['app_key'] ?? ''),
            'version'   => implode('.', $this->recombinationVersion($this->versionData['version'] ?? '')),
        ];

        //        $this->initialize();
    }

    public function initialize()
    {
        $this->getSign($this->timeStamp);
        if (! Cache::tags([CacheEnum::TAG_OTHER])->has('upgrade_auth_token')) {
            $this->getAuth();
        }

        $this->httpService->setHeader(['Access-Token' => 'Bearer ' . Cache::tags([CacheEnum::TAG_OTHER])->get('upgrade_auth_token')]);
    }

    /**
     * 获取签名.
     */
    public function getSign(int $timeStamp)
    {
        $data = $this->requestData;
        if ((! isset($data['host']) || ! $data['host'])
            || (! isset($data['nonce']) || ! $data['nonce'])
            || (! isset($data['app_id']) || ! $data['app_id'])
            || (! isset($data['version']) || ! $data['version'])
            || (! isset($data['app_key']) || ! $data['app_key'])) {
            throw new AdminException('验证失效，请重新请求');
        }

        $host    = $data['host'];
        $nonce   = $data['nonce'];
        $appId   = $data['app_id'];
        $appKey  = $data['app_key'];
        $version = $data['version'];
        unset($data['sign'], $data['nonce'], $data['host'], $data['version'], $data['app_id'], $data['app_key']);

        $params  = json_encode($data);
        $shaiAtt = [
            'host'       => $host,
            'nonce'      => $nonce,
            'app_id'     => $appId,
            'params'     => $params,
            'app_key'    => $appKey,
            'version'    => $version,
            'time_stamp' => $timeStamp,
        ];

        sort($shaiAtt, SORT_STRING);
        $shaiStr                   = implode(',', $shaiAtt);
        $this->requestData['sign'] = hash('SHA256', $shaiStr);
    }

    /**
     * 获取文件配置信息.
     */
    public function getVersionData(string $name = ''): array|string
    {
        $list = parse_ini_file(app()->basePath() . '/.version');
        return ! empty($name) ? $list[$name] ?? '' : $list;
    }

    /**
     * 获取版本号.
     * @param mixed $input
     */
    public function recombinationVersion($input): array
    {
        $version = substr($input, strpos($input, '-v') + 1);
        return array_map(function ($item) {
            if (preg_match('/\d+/', $item, $arr)) {
                $item = $arr[0];
            }
            return (int) $item;
        }, explode('.', $version));
    }

    /**
     * 升级状态
     */
    public function getUpgradeStatus(): array
    {
        $data                          = $this->request($this->statusApi, HttpService::METHOD_GET);
        $upgradeData['status']         = $data['status'] ?? 0;
        $upgradeData['version']        = $data['version'] ?? '';
        $upgradeData['release_time']   = $data['release_time'] ?? '';
        $upgradeData['force_reminder'] = $data['force_reminder'] ?? 0;
        $upgradeData['title']          = $upgradeData['status'] < 1 ? '您已升级至最新版本，无需更新' : '系统有新版本可更新';
        return $upgradeData;
    }

    /**
     * 数据库备份.
     */
    public function databaseBackup(string $token)
    {
        $path = storage_path('backup');
        if (! is_dir($path) || ! file_exists($path)) {
            mkdir($path, 0700, true);
        }

        $version  = str_replace('.', '', $this->requestData['version']);
        $fileName = date('YmdHis') . '_' . $version . '.sql';
        try {
            $command = sprintf('mysqldump -h%s --no-tablespaces -P%s -u%s -p%s %s > %s', env('DB_HOST'), env('DB_PORT'), env('DB_USERNAME'), env('DB_PASSWORD'), env('DB_DATABASE'), $path . DIRECTORY_SEPARATOR . $fileName);
            $process = Process::fromShellCommandline($command)->mustRun();
        } catch (ProcessFailedException $e) {
            Log::error('数据库备份失败,失败原因:' . $e->getMessage());
        }

        if (! is_file($path . DIRECTORY_SEPARATOR . $fileName)) {
            throw new AdminException('数据库备份失败');
        }
        Cache::put($token . '_database_backup', 2, 86400);
        Cache::put($token . '_database_backup_name', $fileName, 86400);
        Log::notice('数据库备份：', ['path' => $path, 'file' => $fileName, 'command' => $command, 'out' => $process->getOutput()]);
    }

    /**
     * 项目备份.
     */
    public function projectBackup(string $token): bool
    {
        try {
            ini_set('memory_limit', '-1');
            $basePath = base_path();
            /** @var FileService $fileService */
            $fileService = app()->get(FileService::class);
            $backupPath  = $basePath . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'backup' . DIRECTORY_SEPARATOR . date('Ymd') . DIRECTORY_SEPARATOR . $token;

            $projectPath = $this->getProjectDir($basePath);
            if (empty($projectPath)) {
                throw new AdminException('项目目录获取异常');
            }

            foreach ($projectPath as $key => $path) {
                foreach ($path as $item) {
                    $oldPath = $basePath . DIRECTORY_SEPARATOR . $item;
                    $newPath = $backupPath . DIRECTORY_SEPARATOR . $item;
                    if ($key == 'file') {
                        $fileService->handleFile($oldPath, $newPath, 'copy', false, ['zip']);
                    } else {
                        $fileService->handleDir($oldPath, $newPath, 'copy', false, ['uploads']);
                    }
                }
            }

            $version  = str_replace('.', '', $this->requestData['version']);
            $fileName = date('YmdHis') . '_' . $version . '_project.zip';
            $filePath = $basePath . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'backup' . DIRECTORY_SEPARATOR . $fileName;
            if (! $fileService->addZip($backupPath, $filePath, $backupPath)) {
                throw new AdminException('项目备份失败');
            }

            Cache::put($token . '_project_backup', 2, 86400);
            Cache::put($token . '_project_backup_name', $fileName, 86400);

            // 检测项目备份
            if (! is_file($filePath)) {
                throw new AdminException('项目备份检测失败');
            }

            return true;
        } catch (\Exception $e) {
            Log::error('升级失败,失败原因:' . $e->getMessage());
            Cache::put($token . 'upgrade_status', -1, 86400);
            Cache::put($token . 'upgrade_status_tip', '升级失败,失败原因:' . $e->getMessage(), 86400);
        }
        return false;
    }

    /**
     * 获取项目目录.
     * @param mixed $path
     * @throws BindingResolutionException
     */
    public function getProjectDir($path): array
    {
        /** @var FileService $fileService */
        $fileService = app()->get(FileService::class);
        $list        = $fileService->getDirs($path);
        $ignore      = ['.', '..', '.git', '.idea', 'runtime', 'backup', 'upgrade', 'storage'];
        foreach ($list as $key => $path) {
            if (empty($key)) {
                unset($list[$key]);
                continue;
            }
            if (is_array($path)) {
                foreach ($path as $key2 => $item) {
                    if (in_array($item, $ignore) && $item) {
                        unset($list[$key][$key2]);
                    }
                }
            }
        }
        return $list;
    }

    /**
     * 升级协议.
     * @return array|mixed
     */
    public function getAgreement(): mixed
    {
        return $this->request($this->agreementApi, HttpService::METHOD_GET);
    }

    /**
     * 升级列表.
     * @return array|mixed
     */
    public function getUpgradeList(): mixed
    {
        [$page, $limit]             = $this->getPageValue();
        $this->requestData['page']  = (string) ($page ?: 1);
        $this->requestData['limit'] = (string) ($limit ?: 10);
        $this->getSign($this->timeStamp);
        return $this->request($this->listApi, HttpService::METHOD_GET);
    }

    /**
     * 可升级数据.
     */
    public function getEnableData()
    {
        $data = $this->request($this->ableApi, HttpService::METHOD_GET);
        return (is_array($data) && isset($data[0])) ? $data[0] : [];
    }

    /**
     * 开始升级.
     * @throws \Exception
     */
    public function startUpgrade(string $packageKey): bool
    {
        if (! extension_loaded('zip')) {
            throw new AdminException('请安装zip扩展');
        }

        $token = md5(time());

        // 核对数据库
        $this->checkDatabaseSize();

        // 核对项目签名
        $this->checkSignature();

        $this->requestData['package_key'] = $packageKey;
        $this->getSign($this->timeStamp);
        $data = $this->request($this->downloadApi, HttpService::METHOD_GET);

        $serverPackage = $data['server_package_link'] ?? '';
        $clientPackage = $data['client_package_link'] ?? '';
        $pcPackage     = $data['pc_package_link'] ?? '';

        if (! $serverPackage && ! $clientPackage && ! $pcPackage) {
            Cache::put($token . 'upgrade_status', 2, 86400);
            return true;
        }

        $this->downloadFile($serverPackage, $token . '_server_package');
        $this->downloadFile($clientPackage, $token . '_client_package');
        $this->downloadFile($pcPackage, $token . '_pc_package');

        Cache::put($token . '_database_backup', 1, 86400);
        BackupJob::dispatch($token, 'databaseBackup');

        Cache::put($token . '_project_backup', 1, 86400);
        BackupJob::dispatch($token, 'projectBackup');

        Cache::put('upgrade_token', $token, 86400);
        Cache::put($token . '_upgrade_data', $data, 86400);
        return true;
    }

    /**
     * 获取升级目录.
     */
    public function getUpgradePath(): string
    {
        $upgradePath = storage_path('upgrade') . DIRECTORY_SEPARATOR . date('Y-m-d');
        if (! is_dir($upgradePath)) {
            mkdir($upgradePath, 0755, true);
        }
        return $upgradePath;
    }

    /**
     * 执行下载.
     * @throws GuzzleException
     * @throws BindingResolutionException
     */
    public function download(string $url, string $seq, int $timeout = 60): bool
    {
        ini_set('memory_limit', '-1');
        $upgradePath = $this->getUpgradePath();

        $fileName = substr($url, strrpos($url, '/') + 1);
        $filePath = $upgradePath . DIRECTORY_SEPARATOR . $fileName;

        $statusCode = (new Client())->get($url, [RequestOptions::SINK => fopen($filePath, 'w+'), 'timeout' => $timeout])->getStatusCode();
        if ($statusCode != 200) {
            throw new AdminException($seq . '安装包下载错误');
        }

        $pathInfo = pathinfo($filePath);
        if ($pathInfo['extension'] != 'zip') {
            throw new AdminException($seq . '安装包格式错误');
        }

        /** @var FileService $fileService */
        $fileService  = app()->get(FileService::class);
        $downloadPath = $upgradePath . DIRECTORY_SEPARATOR . $pathInfo['filename'];
        if (! $fileService->extractFile($filePath, $downloadPath)) {
            throw new AdminException($seq . '升级包解压失败');
        }

        Cache::put($seq . '_path', $downloadPath, 86400);
        Cache::put($seq . '_name', $filePath, 86400);
        Cache::put($seq, 2, 86400);
        return true;
    }

    /**
     * 检查数据库大小.
     */
    public function checkDatabaseSize()
    {
        if (! $database = config('database.connections.' . config('database.default') . '.database')) {
            throw new AdminException('数据库信息获取失败');
        }

        $data = DB::selectOne('select concat(round(sum(data_length/1024/1024))) as size from information_schema.tables where table_schema=?;', [$database]);
        if ((int) $data->size > 500) {
            throw new AdminException('数据库文件过大, 请手动升级');
        }
    }

    /**
     * 升级记录.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getLogList(): array
    {
        [$page, $limit] = $this->getPageValue();
        $list           = $this->dao->getList([], ['id', 'title', 'content', 'first_version', 'second_version', 'third_version', 'fourth_version', 'upgrade_time', 'package_link', 'data_link'], $page, $limit, 'upgrade_time');
        $count          = $this->dao->count();
        return $this->listData($list, $count);
    }

    /**
     * 升级进度.
     * @throws BindingResolutionException
     */
    public function getProgress(): array
    {
        $token = Cache::tags([CacheEnum::TAG_OTHER])->get('upgrade_token');
        if (empty($token)) {
            throw new AdminException('请重新升级');
        }

        $serverProgress         = Cache::tags([CacheEnum::TAG_OTHER])->get($token . '_server_package'); // 服务端包下载进度
        $clientProgress         = Cache::tags([CacheEnum::TAG_OTHER])->get($token . '_client_package'); // 客户端包下载进度
        $pcProgress             = Cache::tags([CacheEnum::TAG_OTHER])->get($token . '_pc_package'); // PC端包下载进度
        $databaseBackupProgress = Cache::tags([CacheEnum::TAG_OTHER])->get($token . '_database_backup'); // 数据库备份进度
        $projectBackupProgress  = Cache::tags([CacheEnum::TAG_OTHER])->get($token . '_project_backup'); // 项目备份备份进度

        $databaseUpgradeProgress = Cache::tags([CacheEnum::TAG_OTHER])->get($token . '_database_upgrade'); // 数据库升级进度
        $coverageProjectProgress = Cache::tags([CacheEnum::TAG_OTHER])->get($token . '_coverage_project'); // 项目覆盖进度

        $stepNum = 1;
        $tip     = '开始升级';
        if ($serverProgress == $clientProgress && $clientProgress == $pcProgress) {
            $tip = $serverProgress == 1 ? '开始下载安装包' : '安装包下载完成';
            if ($serverProgress == 2) {
                ++$stepNum;
            }
        } else {
            $tip = '正在下载安装包';
        }

        if ($databaseBackupProgress == 2) {
            $tip = '数据库备份完成';
            ++$stepNum;
        }

        if ($projectBackupProgress == 2) {
            $tip = '项目备份完成';
            ++$stepNum;
        }

        if ((int) $databaseUpgradeProgress == 2) {
            $tip = '数据库升级完成';
            ++$stepNum;
        }

        if ((int) $coverageProjectProgress == 2) {
            $tip = '项目升级完成';
            ++$stepNum;
        }

        $upgradeStatus = (int) Cache::tags([CacheEnum::TAG_OTHER])->get($token . 'upgrade_status');
        if ($upgradeStatus == 2) {
            $stepNum = 6;
            $tip     = '升级完成';
            if (! Cache::tags([CacheEnum::TAG_OTHER])->has($token . '_upgrade_over')) {
                Cache::add($token . '_upgrade_over', 1, 86400);
                Artisan::call('config:clear');
                app()->get(Server::class)->reload();
            }
        } elseif ($upgradeStatus < 0) {
            $this->saveLog($token);
            throw new AdminException(Cache::tags([CacheEnum::TAG_OTHER])->get($token . 'upgrade_status_tip', '升级失败'));
        } elseif ($serverProgress == 2 && $clientProgress == 2 && $pcProgress == 2 && $databaseBackupProgress == 2 && $projectBackupProgress == 2) {
            try {
                $this->overwriteProject();
            } catch (\Exception $e) {
                Log::error('项目升级失败,失败原因:' . $e->getMessage());
                $this->sendUpgradeLog($token);
            }
        }

        $speed = sprintf('%.1f', $stepNum / 6 * 100);
        return compact('speed', 'tip');
    }

    /**
     * 升级.
     * @throws \Exception
     */
    public function overwriteProject(): bool
    {
        try {
            if (! $token = Cache::tags([CacheEnum::TAG_OTHER])->get('upgrade_token')) {
                throw new AdminException('请重新下载升级包');
            }

            if (Cache::tags([CacheEnum::TAG_OTHER])->get($token . 'is_execute') == 2) {
                return true;
            }
            Cache::put($token . 'is_execute', 2, 86400);

            $serverPackageFilePath = Cache::tags([CacheEnum::TAG_OTHER])->get($token . '_server_package_path');
            if (is_dir($serverPackageFilePath)) {
                // 执行数据库升级
                if (! $this->databaseUpgrade($token, $serverPackageFilePath)) {
                    throw new AdminException('数据库升级失败');
                }
            }

            // 替换文件目录
            $this->coverageProject($token);

            // 发送升级日志
            $this->sendUpgradeLog($token);
            $this->saveLog($token);
            Cache::put($token . 'upgrade_status', 2, 86400);
            return true;
        } catch (\Exception $e) {
            Log::error('升级失败,失败原因:' . $e->getMessage());
            Cache::put($token . 'upgrade_status', -1, 86400);
            Cache::put($token . 'upgrade_status_tip', '升级失败,失败原因:' . $e->getMessage(), 86400);
        }
        return false;
    }

    /**
     * 覆盖项目.
     * @throws \Exception
     */
    public function coverageProject(string $token): bool
    {
        $basePath = base_path();
        Cache::put('version_before', $this->recombinationVersion($versionData['version'] ?? ''), 86400);

        /** @var FileService $fileService */
        $fileService = app()->get(FileService::class);

        // 服务端项目
        $serverPackageName = Cache::tags([CacheEnum::TAG_OTHER])->get($token . '_server_package_name');

        // 客户端项目
        $clientPackageName = Cache::tags([CacheEnum::TAG_OTHER])->get($token . '_client_package_name');

        // PC端项目
        $pcPackageName = Cache::tags([CacheEnum::TAG_OTHER])->get($token . '_pc_package_name');

        if (! is_file($serverPackageName) && ! is_file($clientPackageName) && ! is_file($pcPackageName)) {
            throw new AdminException('升级文件异常,请重新下载');
        }

        if (is_file($serverPackageName) && ! $fileService->extractFile($serverPackageName, $basePath)) {
            throw new AdminException('服务端升级失败');
        }

        if (is_file($clientPackageName) && ! $fileService->extractFile($clientPackageName, $basePath)) {
            throw new AdminException('客户端升级失败');
        }

        if (is_file($pcPackageName)) {
            if (! $fileService->delDir(public_path('admin'))) {
                throw new AdminException('请检查public/admin 目录/文件权限是否正确');
            }
            if (! $fileService->extractFile($pcPackageName, $basePath)) {
                throw new AdminException('PC端升级失败');
            }
        }

        // 生成项目签名
        $this->generateSignature();

        Cache::put($token . '_coverage_project', 2, 86400);
        return true;
    }

    /**
     * 写入日志.
     * @param mixed $token
     * @throws BindingResolutionException
     */
    public function saveLog($token)
    {
        if (Cache::tags([CacheEnum::TAG_OTHER])->get($token . 'is_save') == 2) {
            return true;
        }
        Cache::put($token . 'is_save', 2, 86400);

        $upgradeData = Cache::tags([CacheEnum::TAG_OTHER])->get($token . '_upgrade_data');

        $this->dao->getModel(false)->create([
            'title'          => $upgradeData['title'] ?? '',
            'content'        => $upgradeData['content'] ?? '',
            'first_version'  => $upgradeData['first_version'] ?? '',
            'second_version' => $upgradeData['second_version'] ?? '',
            'third_version'  => $upgradeData['third_version'] ?? '',
            'fourth_version' => $upgradeData['fourth_version'] ?? '',
            'upgrade_time'   => date('Y-m-d H:i:s', time()),
            'error_data'     => Cache::tags([CacheEnum::TAG_OTHER])->get($token . 'upgrade_status_tip', ''),
            'package_link'   => Cache::tags([CacheEnum::TAG_OTHER])->get($token . '_project_backup_name', ''),
            'data_link'      => Cache::tags([CacheEnum::TAG_OTHER])->get($token . '_database_backup_name', ''),
        ]);
    }

    /**
     * 发送日志.
     */
    public function sendUpgradeLog(string $token): bool
    {
        try {
            $versionBefore = Cache::tags([CacheEnum::TAG_OTHER])->get('version_before', '');
            $versionAfter  = $this->recombinationVersion($this->versionData['version'] ?? '');

            $this->requestData['version_before'] = implode('.', $versionBefore);
            $this->requestData['version_after']  = implode('.', $versionAfter);
            $this->requestData['error_data']     = Cache::tags([CacheEnum::TAG_OTHER])->get($token . 'upgrade_status_tip', '');

            $this->request($this->loginApi, HttpService::METHOD_POST);
        } catch (\Exception $e) {
            Log::error('升级日志发送失败:,失败原因:' . $e->getMessage());
        }
        return true;
    }

    /**
     * 数据库升级.
     */
    public function databaseUpgrade(string $token, string $serverPackageFilePath): bool
    {
        $dataBackupName = Cache::tags([CacheEnum::TAG_OTHER])->get($token . '_database_backup_name');
        if (! $dataBackupName || ! is_file(storage_path('backup') . DIRECTORY_SEPARATOR . $dataBackupName)) {
            throw new AdminException('数据库备份获取失败');
        }

        $databaseFilePath = $serverPackageFilePath . DIRECTORY_SEPARATOR . 'upgrade' . DIRECTORY_SEPARATOR . 'update.sql';
        if (! is_file($databaseFilePath)) {
            Cache::put($token . '_database_upgrade', 2, 86400);
            return true;
        }

        $originData = file($databaseFilePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (empty($originData)) {
            Cache::put($token . '_database_upgrade', 2, 86400);
            return true;
        }

        Cache::put($token . '_database_upgrade', 1, 86400);

        $temp    = '';
        $sqlList = $structUpgradeSql = $upgradeSql = [];
        foreach ($originData as $line) {
            if (str_contains($line, '#')) {
                continue;
            }
            if (str_ends_with(rtrim($line), ';')) {
                if ($temp) {
                    $temp .= $line;
                } else {
                    $temp = $line;
                }

                $sqlList[] = $temp;
                $temp      = '';
            } else {
                $temp .= $line;
            }
        }

        $prefixType = ['INSERT' => 1, 'UPDATE' => 2, 'DELETE' => 3];
        foreach ($sqlList as $item) {
            $type = substr($item, 0, strpos($item, ' '));
            $data = ['sql' => $item, 'type' => $prefixType[$type] ?? 4];
            if ($data['type'] < 4) {
                $upgradeSql[] = $data;
            } else {
                $structUpgradeSql[] = $data;
            }
        }

        if (! $this->structUpgrade($token, $structUpgradeSql)) {
            return false;
        }

        if (empty($upgradeSql)) {
            Cache::put($token . '_database_upgrade', 2, 86400);
            return true;
        }

        DB::beginTransaction();
        try {
            foreach ($upgradeSql as $item) {
                $result = true;
                // 1添加数据 2修改数据 3删数据
                switch ($item['type']) {
                    case 1:
                        $result = DB::insert($item['sql']);
                        break;
                    case 2:
                        DB::update($item['sql']);
                        break;
                    case 3:
                        $result = DB::delete($item['sql']);
                        break;
                }

                if (! $result) {
                    Log::error('数据操作失败, sql:' . $item['sql']);
                    throw new AdminException('数据操作失败');
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('数据库升级失败,失败原因:' . $e->getMessage(), $upgradeSql);
            Cache::put($token . 'upgrade_status', -1, 86400);
            Cache::put($token . 'upgrade_status_tip', '数据库升级失败,失败原因:' . $e->getMessage(), 86400);
            return false;
        }
        Cache::put($token . '_database_upgrade', 2, 86400);
        return true;
    }

    /**
     * 结构更新.
     */
    public function structUpgrade(string $token, array $updateSql): bool
    {
        if (! $updateSql) {
            return true;
        }
        try {
            foreach ($updateSql as $item) {
                DB::statement($item['sql']);
            }
        } catch (\Exception $e) {
            $tip = '数据库结构更新失败,失败原因:' . $e->getMessage();
            Cache::put($token . 'upgrade_status', -1, 86400);
            Cache::put($token . 'upgrade_status_tip', $tip, 86400);
            Log::error($tip, ['data' => $updateSql]);
            return false;
        }
        return true;
    }

    /**
     * 生成签名.
     * @throws \Exception
     */
    public function generateSignature()
    {
        $basePath = base_path();
        $file     = $basePath . DIRECTORY_SEPARATOR . '.version';
        if (! $data = @file($file)) {
            throw new AdminException('.version读取失败');
        }
        $list = [];
        if (! empty($data)) {
            foreach ($data as $datum) {
                [$name, $value] = explode('=', $datum);
                $list[$name]    = rtrim($value);
            }
        }

        if (! isset($list['project_signature'])) {
            $list['project_signature'] = '';
        }

        /** @var fileVerification $verification */
        $verification              = app()->get(fileVerification::class);
        $list['project_signature'] = $verification->getSignature($basePath);

        $str = '';
        foreach ($list as $key => $item) {
            $str .= "{$key}={$item}\n";
        }
        file_put_contents($file, $str);
    }

    /**
     * 发送请求
     */
    public function request(string $api, string $method): mixed
    {
        $response = $this->httpService->parseJSON($this->upgradeHost . $api, $this->requestData, $method);
        if (! $this->checkStatus($response)) {
            $response = $this->httpService->parseJSON($this->upgradeHost . $api, $this->requestData, $method);
        }

        if ($response->get('status') == 200) {
            return $response->get('data');
        }
        throw new AdminException('数据获取失败,请稍后重试');
    }

    /**
     * 检查状态
     * @param mixed $response
     */
    public function checkStatus($response): bool
    {
        $status = (int) $response->get('status');
        if ($status != 200) {
            if ($status == 400) {
                throw new AdminException($response->get('msg', '未知错误'));
            }
            if ($status == 410000) {
                $this->getAuth();
            }
            return false;
        }
        return true;
    }

    /**
     * 获取Token.
     */
    protected function getAuth()
    {
        $response = $this->httpService->parseJSON($this->upgradeHost . $this->loginApi, $this->requestData, HttpService::METHOD_POST);
        if ($response->get('status') == 200) {
            $data = $response->get('data', '');
            Cache::put('upgrade_auth_token', $data['access_token'] ?? null, 600);
        } else {
            throw new AdminException('授权失败');
        }
    }

    /**
     * 检查访问权限.
     */
    protected function checkAuth(array $data): bool
    {
        if (! isset($data['status']) || $data['status'] != 200) {
            if ($data['status'] == 400) {
                throw new AdminException($data['msg']);
            }
            if ($data['status'] == 410000) {
                $this->getAuth();
            }
            return false;
        }
        return true;
    }

    /**
     * 开始下载.
     */
    private function downloadFile(string $packageLink, string $seq)
    {
        if (! $packageLink) {
            Cache::put($seq, 2, 86400);
        } else {
            Cache::put($seq, 1, 86400);
            DownloadJob::dispatch($packageLink, $seq, 120);
        }
    }

    /**
     * 核对签名.
     * @throws \Exception
     */
    private function checkSignature()
    {
        $oldSignature = $this->versionData['project_signature'] ?? '';
        if (! $oldSignature) {
            throw new AdminException('项目签名获取异常');
        }

        /** @var fileVerification $verification */
        $verification = app()->get(fileVerification::class);
        $newSignature = $verification->getSignature(base_path());
        if ($oldSignature != $newSignature) {
            throw new AdminException('项目签名核对异常');
        }
    }
}
