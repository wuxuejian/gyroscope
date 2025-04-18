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

namespace App\Http\Controller;

use App\Http\Controller\AdminApi\AuthController;
use App\Http\Service\Company\CompanyService;
use crmeb\utils\Regex;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Predis\Client;
use Predis\Response\Status;

/**
 * 程序安装控制器
 * Class InstallController.
 */
class Install extends AuthController
{
    private static int $countInitData = 0;

    private string $Title = '陀螺匠安装向导';

    private string $Powered = 'Powered by Tuoluojiang';

    private static int $countTotal = 0;

    public function index($step = 1)
    {
        if (file_exists(public_path('install/install.lock'))) {
            return '你已经安装过该系统，如需重新安装，请先删除public/install目录下的 install.lock 文件，然后再尝试安装。';
        }
        @set_time_limit(1000);
        if (phpversion() < '8.0') {
            return '您的php版本过低，不能安装本软件，兼容php版本8.0 +，谢谢！';
        }
        date_default_timezone_set('PRC');
        error_reporting(E_ALL & ~E_NOTICE);
        $configFile = '.env';
        if (! file_exists(base_path($configFile))) {
            return '缺少必要的安装文件!';
        }
        return $this->{'step' . (int) $step}();
    }

    /**
     * 写入安装信息.
     */
    public function installlog()
    {
        $mt_rand_str  = $this->sp_random_string(6);
        $str_constant = '<?php' . PHP_EOL . "define('INSTALL_DATE'," . time() . ');' . PHP_EOL . "define('SERIALNUMBER','" . $mt_rand_str . "');";
        @file_put_contents(base_path('.constant'), $str_constant);
    }

    public function sp_random_string($len = 8)
    {
        $chars = [
            'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k',
            'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v',
            'w', 'x', 'y', 'z', 'A', 'B', 'C', 'D', 'E', 'F', 'G',
            'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R',
            'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '0', '1', '2',
            '3', '4', '5', '6', '7', '8', '9',
        ];
        $charsLen = count($chars) - 1;
        shuffle($chars);    // 将数组打乱
        $output = '';
        for ($i = 0; $i < $len; ++$i) {
            $output .= $chars[mt_rand(0, $charsLen)];
        }
        return $output;
    }

    // 判断权限
    public function testwrite($d)
    {
        if (is_file($d)) {
            if (is_writeable($d)) {
                return true;
            }
            return false;
        }
        $tfile = '_test.txt';
        $fp    = @fopen($d . '/' . $tfile, 'w');
        if (! $fp) {
            return false;
        }
        fclose($fp);
        $rs = @unlink($d . '/' . $tfile);
        if ($rs) {
            return true;
        }
        return false;
    }

    // 获取客户端IP地址
    public function get_client_ip()
    {
        static $ip = null;
        if ($ip !== null) {
            return $ip;
        }
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $pos = array_search('unknown', $arr);
            if ($pos !== false) {
                unset($arr[$pos]);
            }
            $ip = trim($arr[0]);
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        // IP地址合法验证
        $ip = (ip2long((string) $ip) !== false) ? $ip : '0.0.0.0';
        return $ip;
    }

    // 创建目录
    public function dir_create($path, $mode = 0777)
    {
        if (is_dir($path)) {
            return true;
        }
        $ftp_enable = 0;
        $path       = $this->dir_path($path);
        $temp       = explode('/', $path);
        $cur_dir    = '';
        $max        = count($temp) - 1;
        for ($i = 0; $i < $max; ++$i) {
            $cur_dir .= $temp[$i] . '/';
            if (@is_dir($cur_dir)) {
                continue;
            }
            @mkdir($cur_dir, 0777, true);
            @chmod($cur_dir, 0777);
        }
        return is_dir($path);
    }

    public function dir_path($path)
    {
        $path = str_replace('\\', '/', $path);
        if (substr($path, -1) != '/') {
            $path = $path . '/';
        }
        return $path;
    }

    public function sql_split($sql, $tablepre)
    {
        if ($tablepre != 'eb_') {
            $sql = str_replace('eb_', $tablepre, $sql);
        }

        $sql = preg_replace('/TYPE=(InnoDB|MyISAM|MEMORY)( DEFAULT CHARSET=[^; ]+)?/', 'ENGINE=\1 DEFAULT CHARSET=utf8mb4', $sql);

        $sql          = str_replace("\r", "\n", $sql);
        $ret          = [];
        $num          = 0;
        $queriesarray = explode(";\n", trim($sql));
        unset($sql);
        foreach ($queriesarray as $query) {
            $ret[$num] = '';
            $queries   = explode("\n", trim($query));
            $queries   = array_filter($queries);
            foreach ($queries as $query) {
                $str1 = substr($query, 0, 1);
                if ($str1 != '#' && $str1 != '-') {
                    $ret[$num] .= $query;
                }
            }
            ++$num;
        }
        return $ret;
    }

    // 递归删除文件夹
    public function delFile($dir, $file_type = '')
    {
        if (is_dir($dir)) {
            $files = scandir($dir);
            // 打开目录 //列出目录中的所有文件并去掉 . 和 ..
            foreach ($files as $filename) {
                if ($filename != '.' && $filename != '..') {
                    if (! is_dir($dir . '/' . $filename)) {
                        if (empty($file_type)) {
                            unlink($dir . '/' . $filename);
                        } else {
                            if (is_array($file_type)) {
                                // 正则匹配指定文件
                                if (preg_match($file_type[0], $filename)) {
                                    unlink($dir . '/' . $filename);
                                }
                            } else {
                                // 指定包含某些字符串的文件
                                if (stristr($filename, $file_type) != false) {
                                    unlink($dir . '/' . $filename);
                                }
                            }
                        }
                    } else {
                        $this->delFile($dir . '/' . $filename);
                        rmdir($dir . '/' . $filename);
                    }
                }
            }
        } else {
            if (file_exists($dir)) {
                unlink($dir);
            }
        }
    }

    public function envData()
    {
        return [
            [
                'name'          => 'PHP 版本',
                'function_name' => 'phpv',
                'config'        => '8.0',
                'status'        => '',
                'lowest'        => '8.0',
                'types'         => 1,
            ],
            [
                'name'          => '附件上传',
                'function_name' => 'uploadSize',
                'config'        => '>=2M',
                'status'        => '',
                'lowest'        => '2M',
                'types'         => 1,
            ],
            [
                'name'          => 'session',
                'function_name' => 'session',
                'config'        => '开启',
                'status'        => '支持',
                'lowest'        => '开启',
                'types'         => 1,
            ],
            [
                'name'          => 'gd',
                'function_name' => 'gd',
                'config'        => '开启',
                'status'        => '支持',
                'lowest'        => '开启',
                'types'         => 1,
            ],
            [
                'name'          => 'pdo',
                'function_name' => 'mysql',
                'config'        => '开启',
                'status'        => '支持',
                'lowest'        => '开启',
                'types'         => 1,
            ],
            [
                'name'          => 'bcmath',
                'function_name' => 'bcmath',
                'config'        => '开启',
                'status'        => '支持',
                'lowest'        => '开启',
                'types'         => 1,
            ],
            [
                'name'          => 'curl',
                'function_name' => 'curl',
                'config'        => '开启',
                'status'        => '支持',
                'lowest'        => '开启',
                'types'         => 1,
            ],
            [
                'name'          => 'swoole',
                'function_name' => 'swoole',
                'config'        => '已安装',
                'status'        => '未安装',
                'lowest'        => '安装',
                'types'         => 1,
            ],
            [
                'name'          => 'openssl',
                'function_name' => 'openssl',
                'config'        => '开启',
                'status'        => '支持',
                'lowest'        => '开启',
                'types'         => 1,
            ],
            [
                'name'          => 'fileinfo',
                'function_name' => 'finfo_open',
                'config'        => '支持',
                'status'        => '不支持',
                'lowest'        => '支持',
                'types'         => 1,
            ],
        ];
    }

    public function funcData()
    {
        return [
            [
                'name'          => 'file_put_contents',
                'function_name' => 'file_put_contents',
                'config'        => '开启',
                'types'         => 1,
            ],
            [
                'name'          => 'imagettftext',
                'function_name' => 'imagettftext',
                'config'        => '开启',
                'types'         => 1,
            ],
        ];
    }

    /**
     * 读取版本号.
     * @return array
     */
    protected function getversion()
    {
        $version_arr    = [];
        $curent_version = @file(base_path('.version'));
        foreach ($curent_version as $val) {
            [$k, $v]         = explode('=', $val);
            $version_arr[$k] = $v;
        }
        return $version_arr;
    }

    private function step1()
    {
        return view('install/step1', [
            'title'   => $this->Title,
            'powered' => $this->Powered,
        ]);
    }

    private function step2()
    {
        $phpv       = @phpversion();
        $tmp        = function_exists('gd_info') ? gd_info() : [];
        $passOne    = $passTwo = true;
        $configData = $this->envData();
        foreach ($configData as &$data) {
            switch ($data['function_name']) {
                case 'phpv':
                    $data['status'] = $phpv;
                    if (version_compare(phpversion(), '8.0.0', '<')) {
                        $data['types'] = 0;
                        $passOne       = false;
                    }
                    break;
                case 'gd':
                    if (empty($tmp['GD Version'])) {
                        $data['status'] = 'Off';
                        $data['types']  = 0;
                        $passOne        = false;
                    } else {
                        $data['status'] = 'On' . $tmp['GD Version'];
                    }
                    break;
                case 'safe_mode':
                    if (ini_get('safe_mode')) {
                        $data['status'] = 'On';
                    } else {
                        $data['status'] = 'Off';
                        $data['types']  = 0;
                        $passOne        = false;
                    }
                    break;
                case 'swoole':
                    if (extension_loaded('swoole')) {
                        $data['status'] = '已安装';
                    } else {
                        $data['status'] = '未安装';
                        $data['types']  = 0;
                        $passOne        = false;
                    }
                    break;
                case 'mysql':
                    if (extension_loaded('pdo_mysql')) {
                        $data['status'] = '已安装';
                    } else {
                        $data['status'] = '未安装';
                        $data['types']  = 0;
                        $passOne        = false;
                    }
                    break;
                case 'uploadSize':
                    if (ini_get('file_uploads')) {
                        $data['status'] = ini_get('upload_max_filesize');
                    } else {
                        $data['status'] = '禁止上传';
                        $data['types']  = 0;
                        $passOne        = false;
                    }
                    break;
                case 'session':
                    if (function_exists('session_start')) {
                        $data['status'] = '支持';
                    } else {
                        $data['status'] = '不支持';
                        $data['types']  = 0;
                        $passOne        = false;
                    }
                    break;
                case 'curl':
                    if (function_exists('curl_init')) {
                        $data['status'] = '支持';
                    } else {
                        $data['status'] = '不支持';
                        $data['types']  = 0;
                        $passOne        = false;
                    }
                    break;
                case 'bcadd':
                    if (function_exists('bcadd')) {
                        $data['status'] = '支持';
                    } else {
                        $data['status'] = '不支持';
                        $data['types']  = 0;
                        $passOne        = false;
                    }
                    break;
                case 'openssl':
                    if (function_exists('openssl_encrypt')) {
                        $data['status'] = '支持';
                    } else {
                        $data['status'] = '不支持';
                        $data['types']  = 0;
                        $passOne        = false;
                    }
                    break;
                case 'finfo_open':
                    if (function_exists('finfo_open')) {
                        $data['status'] = '支持';
                    } else {
                        $data['status'] = '不支持';
                        $data['types']  = 0;
                        $passOne        = false;
                    }
                    break;
            }
        }
        $funcData = $this->funcData();
        foreach ($funcData as &$func) {
            switch ($func['function_name']) {
                case 'file_put_contents':
                    if (function_exists('file_put_contents')) {
                        $func['config'] = '开启';
                    } else {
                        $func['config'] = '关闭';
                        $data['types']  = 0;
                        $passOne        = false;
                    }
                    break;
                case 'imagettftext':
                    if (function_exists('imagettftext')) {
                        $func['config'] = '开启';
                    } else {
                        $func['config'] = '关闭';
                        $data['types']  = 0;
                        $passOne        = false;
                    }
                    break;
            }
        }
        $files = [
            'public',
            'storage',
            '.env',
            '.version',
        ];
        foreach ($files as $file) {
            if (! is_readable(base_path($file))) {
                $passTwo = false;
            }
            if (! is_writeable(base_path($file))) {
                $passTwo = false;
            }
        }
        return view('install/step2', [
            'Title'      => $this->Title,
            'Powered'    => $this->Powered,
            'configData' => $configData,
            'funcData'   => $funcData,
            'passOne'    => (int) $passOne,
            'passTwo'    => (int) $passTwo,
            'files'      => $files,
        ]);
    }

    private function step3()
    {
        if ($this->request->isMethod('POST')) {
            $post = $this->request->postMore([
                ['dbHost', ''],
                ['dbPort', ''],
                ['dbUser', ''],
                ['dbPwd', ''],
                ['dbPrefix', ''],
                ['dbName', ''],
                ['cacheDriver', 'redis'],
                ['rbHost', ''],
                ['rbPort', ''],
                ['rbNum', ''],
                ['rbPwd', ''],
                ['initData', ''],
                ['account', ''],
                ['password', ''],
                ['checkPass', ''],
            ]);
            try {
                $dsn  = "mysql:host={$post['dbHost']};port={$post['dbPort']};dbname={$post['dbName']}";
                $conn = new \PDO($dsn, $post['dbUser'], $post['dbPwd']);
                $conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                if (version_compare($conn->getAttribute(\PDO::ATTR_SERVER_VERSION), '5.7.0', '<')) {
                    return $this->success(['code' => -5]);
                }
            } catch (\Exception $e) {
                return $this->success(['code' => $e->getCode()]);
            }
            if ($post['cacheDriver'] == 'redis') {
                try {
                    $client = new Client([
                        'host'     => $post['rbHost'],
                        'port'     => (int) $post['rbPort'],
                        'database' => (int) $post['rbNum'],
                        'password' => $post['rbPwd'],
                    ]);
                    $response = $client->ping();
                    if ($response instanceof Status && $response->getPayload() === 'PONG') {
                        echo 'Redis connection successful!';
                    } else {
                        return $this->success(['code' => -3]);
                    }
                } catch (\Exception $e) {
                    return $this->success(['code' => $e->getCode()]);
                }
                // 请填写正确的手机号
                if (! preg_match(Regex::PHONE_NUMBER, $post['account'])) {
                    return $this->success(['code' => -2]);
                }
            }
            modify_env([
                'DB_USERNAME'     => $post['dbUser'],
                'DB_HOST'         => $post['dbHost'],
                'DB_PORT'         => $post['dbPort'],
                'DB_DATABASE'     => $post['dbName'],
                'DB_PASSWORD'     => $post['dbPwd'],
                'DB_PREFIX'       => $post['dbPrefix'],
                'CONFIG_INIT'     => $post['initData'],
                'REDIS_HOST'      => $post['rbHost'],
                'REDIS_PASSWORD'  => $post['rbPwd'],
                'REDIS_PORT'      => $post['rbPort'],
                'REDIS_DB'        => $post['rbNum'],
                'INIT_DATA'       => $post['initData'] ? 1 : 0,
                'CACHE_DRIVER'    => $post['cacheDriver'],
                'MANAGE_ACCOUNT'  => $post['account'],
                'MANAGE_PASSWORD' => password_hash($post['password'], PASSWORD_BCRYPT),
            ]);
            Artisan::call('config:clear');
            DB::reconnect();
            @shell_exec('php ' . base_path('bin/laravels') . ' reload');
            return $this->success(['code' => 1]);
        }
        return view('/install/step3', [
            'Title'   => $this->Title,
            'Powered' => $this->Powered,
            'form'    => [
                'dbUser'      => env('DB_USERNAME', ''),
                'dbHost'      => env('DB_HOST', '127.0.0.1'),
                'dbPort'      => env('DB_PORT', '3306'),
                'dbName'      => env('DB_DATABASE', ''),
                'dbPwd'       => env('DB_PASSWORD', ''),
                'dbPrefix'    => env('DB_PREFIX', 'tl_'),
                'cacheDriver' => 'redis',
                'rbHost'      => env('REDIS_HOST', '127.0.0.1'),
                'rbPort'      => env('REDIS_PORT', '6379'),
                'rbNum'       => env('REDIS_DB', '0'),
                'rbPwd'       => env('REDIS_PASSWORD', ''),
                'initData'    => (bool) get_env('INIT_DATA'),
                'account'     => '',
                'password'    => '',
                'checkPass'   => '',
            ],
        ]);
    }

    private function step4()
    {
        if (app('request')->isMethod('POST')) {
            $n = app('request')->post('n');
            if ($n >= 99999) {
                return $this->fail('创建企业用户数据失败');
            }
            if ($n < 0) {
                return $this->success([
                    'n'    => 0,
                    'msg'  => '',
                    'time' => date('Y-m-d H:i:s'),
                ]);
            }

            if (self::$countTotal < 1) {
                self::$countTotal = $this->getCounts();
            }
            if ($n <= self::$countTotal) {
                if ($initStatus = $this->createInitData($n, self::$countTotal)) {
                    return $initStatus;
                }

                if ($configStatus = $this->createConfigData($n, self::$countTotal)) {
                    return $configStatus;
                }
                DB::reconnect();
            }

            if (! app()->get(CompanyService::class)->install()) {
                return $this->fail('创建企业信息失败');
            }
            $this->createDefaultData();
            Artisan::call('db:seed');

            modify_env([
                'LARAVELS_TIMER'            => true,
                'LARAVELS_WEBSOCKET_ENABLE' => true,
                'QUEUE_CONNECTION'          => 'redis',
            ]);
            @shell_exec('php ' . base_path('bin/laravels') . ' reload');
            return $this->success([
                'n'    => 99999,
                'msg'  => '创建企业用户数据成功',
                'time' => date('Y-m-d H:i:s'),
            ]);
        }
        return view('/install/step4', [
            'Title'   => $this->Title,
            'Powered' => $this->Powered,
        ]);
    }

    private function step5()
    {
        $ip             = $this->get_client_ip();
        $curent_version = $this->getversion();
        $this->installlog();
        @touch(public_path('install/install.lock'));
        modify_env([
            'CACHE_PREFIX' => 'TL-' . substr((string) time(), -5, 5),
        ]);
        return view('/install/step5', [
            'Title'    => $this->Title,
            'Powered'  => $this->Powered,
            'ip'       => $ip,
            'version'  => trim($curent_version['version']),
            'platform' => trim($curent_version['platform']),
            'host'     => $this->request->getHost(),
        ]);
    }

    private function createInitData($n, $totalCount)
    {
        $sqlData   = file_get_contents(database_path('schema/mysql-schema.sql'));
        $sqlFormat = $this->sql_split($sqlData, 'eb_');
        $counts    = count($sqlFormat);
        $dbPrefix  = get_env('DB_PREFIX');
        do {
            $sql = isset($sqlFormat[$n]) ? trim($sqlFormat[$n]) : '';
            ++$n;
            if (str_contains($sql, 'CREATE TABLE')) {
                preg_match('/CREATE TABLE `eb_([^ ]*)`/is', $sql, $matches);
                $sql = str_replace('`eb_', '`' . $dbPrefix, $sql); // 替换表前缀
                if (trim($sql) == '') {
                    continue;
                }
                $dbName = isset($matches[1]) ? $dbPrefix . $matches[1] : '';
                try {
                    DB::unprepared($sql);
                    $message = $dbName ? '创建数据表[' . $dbName . ']完成!' : '';
                } catch (QueryException $exception) {
                    $message = $dbName ? '创建数据表[' . $dbName . ']失败!失败原因：' . $exception->getMessage() : '执行sql错误，错误原因：' . $exception->getMessage();
                }
                return $this->success([
                    'n'     => $n,
                    'count' => $totalCount,
                    'msg'   => $message,
                    'time'  => date('Y-m-d H:i:s'),
                ]);
            }
            if (! trim($sql)) {
                continue;
            }
            if (str_contains($sql, '@')) {
                return $this->success([
                    'n'     => $n,
                    'count' => $totalCount,
                    'msg'   => '',
                    'time'  => date('Y-m-d H:i:s'),
                ]);
            }
            $sql = str_replace('`eb_', '`' . $dbPrefix, $sql); // 替换表前缀
            DB::unprepared($sql);
            return $this->success([
                'n'     => $n,
                'count' => $totalCount,
                'msg'   => '',
                'time'  => date('Y-m-d H:i:s'),
            ]);
        } while ($n < $counts);
        return false;
    }

    private function createConfigData($n, $totalCount)
    {
        $configData = file_get_contents(public_path('install/config.sql'));
        $sqlFormat  = $this->sql_split($configData, 'eb_');
        $counts     = count($sqlFormat);
        $dbPrefix   = get_env('DB_PREFIX');
        $i          = $n - self::$countInitData;
        do {
            $sql = isset($sqlFormat[$i]) ? trim($sqlFormat[$i]) : '';
            ++$i;
            ++$n;
            if (str_contains($sql, 'INSERT INTO')) {
                preg_match('/INSERT INTO `eb_([^ ]*)`/is', $sql, $matches);
                $sql = str_replace('`eb_', '`' . $dbPrefix, $sql); // 替换表前缀
                if (trim($sql) == '') {
                    continue;
                }
                try {
                    DB::unprepared($sql);
                    $message = '创建[' . $dbPrefix . $matches[1] . ']数据完成!';
                } catch (QueryException $exception) {
                    $message = '创建[' . $dbPrefix . $matches[1] . ']数据失败!失败原因：' . $exception->getMessage();
                }
                return $this->success([
                    'n'     => $n,
                    'count' => $totalCount,
                    'msg'   => $message,
                    'time'  => date('Y-m-d H:i:s'),
                ]);
            }
            return $this->success([
                'n'     => $n,
                'count' => $totalCount,
                'msg'   => '',
                'time'  => date('Y-m-d H:i:s'),
            ]);
        } while ($i < $counts);
        return false;
    }

    private function createDefaultData()
    {
        if (get_env('INIT_DATA')) {
            $replace = get_env('DB_PREFIX');
            foreach (['defaultData', 'develop'] as $item) {
                DB::unprepared(prefix_correction(file_get_contents(public_path('install/' . $item . '.sql')), $replace));
            }
        }
        return false;
    }

    private function getCounts()
    {
        $initData            = file_get_contents(database_path('schema/mysql-schema.sql'));
        $initFormat          = $this->sql_split($initData, 'eb_');
        $configData          = file_get_contents(public_path('install/config.sql'));
        $configFormat        = $this->sql_split($configData, 'eb_');
        self::$countInitData = count($initFormat);
        return count($initFormat) + count($configFormat);
    }
}
