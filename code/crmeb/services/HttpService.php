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

namespace crmeb\services;

use crmeb\exceptions\HttpServiceExceptions;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Class HttpService.
 * @method Collection getJSON($http, array $data = []) GET 请求
 * @method Collection postJSON($http, array $data = []) POST 请求
 * @method Collection putJSON($http, array $data = []) PUT 请求
 * @method Collection deleteJSON($http, array $data = []) DELETE 请求
 */
class HttpService
{
    public const METHOD_GET = 'get';

    public const METHOD_POST = 'post';

    public const METHOD_PUT = 'put';

    public const METHOD_DELTET = 'delete';

    /**
     * header头信息.
     * @var array
     */
    protected $header = [];

    /**
     * api接口地址
     * @var string
     */
    protected $apiHttp;

    /**
     * 超时秒数.
     * @var int
     */
    protected $timeout = 10;

    /**
     * 是否开启debug.
     * @var bool
     */
    protected $debug = true;

    /**
     * HttpService constructor.
     */
    public function __construct(array $config = [])
    {
        $this->apiHttp = $config['apiHttp'] ?? null;
        $this->timeout = $config['timeout'] ?? $this->timeout;
        $this->debug   = isset($config['debug']) ? $config['debug'] : env('APP_DEBUG', false);
        if (isset($config['header']) && is_array($config['header'])) {
            $this->header = $config['header'];
        }
    }

    /**
     * @param mixed $name
     * @param mixed $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        $method     = [self::METHOD_POST, self::METHOD_GET, self::METHOD_DELTET, self::METHOD_PUT];
        $actionName = strstr($name, 'JSON') !== false ? str_replace('JSON', '', $name) : $name;
        if (in_array($actionName, $method)) {
            return $this->parseJSON($arguments[0], $arguments[1] ?? [], $actionName);
        }
        throw new HttpServiceExceptions(__('The method does not exist :class->:medthod()', ['class' => __CLASS__, 'medthod' => $name]));
    }

    /**
     * 设置请求地址
     * @return string
     */
    public function setApiHttp(string $http)
    {
        $this->apiHttp = $http;
        return $this;
    }

    /**
     * 设置标题头.
     * @return $this
     */
    public function setHeader(array $header)
    {
        $this->header = $header;
        return $this;
    }

    /**
     * 发起请求
     * @param mixed $http
     * @return Collection
     */
    public function parseJSON($http, array $data = [], ?string $method = null)
    {
        if (is_array($http)) {
            $data = $http;
            $http = $this->apiHttp;
        }

        if (! $http) {
            throw new HttpServiceExceptions(__('The request address cannot be empty'));
        }

        [$httpCode, $content, $header] = $this->request($http, $method ?: self::METHOD_POST, $data);
        if (! $httpCode) {
            throw new HttpServiceExceptions(__('request was aborted'));
        }

        $respones = json_decode($content, true, 512, JSON_BIGINT_AS_STRING);

        //        $this->debugLog('API response decoded:', ['httpCode' => $httpCode, 'content' => $respones, 'header' => $header]);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new HttpServiceExceptions(__('Failed to parse JSON: :error', ['error' => json_last_error_msg()]));
        }
        if (empty($respones['data'])) {
            $respones['data'] = [];
        }
        if (! empty($header['Token'][0])) {
            $respones['token'] = $header['Token'][0];
        }
        return collect($respones);
    }

    /**
     * 发起请求
     * @param mixed $http
     * @param mixed $data
     * @return Collection
     */
    public function parseJsonCurl($http, $data = [], ?string $method = null, bool $isHeader = false, int $timeout = 15)
    {
        if (is_array($http)) {
            $data = $http;
            $http = $this->apiHttp;
        }

        if (! $http) {
            throw new HttpServiceExceptions(__('The request address cannot be empty'));
        }

        [$httpCode, $content, $header] = $this->requests($http, $method ?: self::METHOD_POST, $data, $isHeader, $timeout);

        if (! $httpCode) {
            throw new HttpServiceExceptions(__('request was aborted'));
        }

        $respones = json_decode($content, true, 512, JSON_BIGINT_AS_STRING);

        //        $this->debugLog('API response decoded:', ['httpCode' => $httpCode, 'content' => $respones, 'header' => $header]);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new HttpServiceExceptions(__('Failed to parse JSON: :error', ['error' => json_last_error_msg()]));
        }
        if (empty($respones['data'])) {
            $respones['data'] = [];
        }
        return collect($respones);
    }

    /**
     * curl 请求
     * @return array
     */
    public function request(string $url, string $method = self::METHOD_GET, array $data = [])
    {
        $response = Http::timeout($this->timeout)->withHeaders($this->header)->{$method}($url, $data)->throw(function ($e) {
            throw new HttpServiceExceptions($e->getMessage());
        });

        $this->reset();

        return [$response->status() == 200, $response->body(), $response->headers()];
    }

    /**
     * curl 请求
     * @param string $method
     * @param array $data
     * @param false $header
     * @param int $timeout
     * @param mixed $url
     * @return array|string
     */
    public function requests($url, $method = 'get', $data = [], $header = false, $timeout = 15, bool $code = false)
    {
        $curl   = curl_init($url);
        $method = strtoupper($method);
        // 请求方式
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        // post请求
        if ($method == 'POST' || $method == 'PUT') {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        // 超时时间
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
        // 设置header头
        if ($header !== false) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $this->header);
        }

        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        // 返回抓取数据
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        // 输出header头信息
        curl_setopt($curl, CURLOPT_HEADER, true);
        // TRUE 时追踪句柄的请求字符串，从 PHP 5.1.3 开始可用。这个很关键，就是允许你查看请求header
        curl_setopt($curl, CURLINFO_HEADER_OUT, true);
        // https请求
        if (strpos('$' . $url, 'https://') == 1) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }
        //        self::$curlError = curl_error($curl);

        [$content, $status] = [curl_exec($curl), curl_getinfo($curl), curl_close($curl)];
        if ($content === false) {
            $content = '';
        }
        //        self::$status    = $status;
        $header  = trim(substr($content, 0, $status['header_size']));
        $content = trim(substr($content, $status['header_size']));
        if ($code) {
            return $content;
        }
        //        return (intval($status["http_code"]) === 200) ? $content : false;
        return [$status['http_code'], $content, $header];
    }

    /**
     * 模拟流式请求
     * @param string $method
     * @param array $data
     * @param bool $header
     * @param mixed $url
     * @return false|mixed
     */
    public function stream($url, $method = 'get', $data = [], ?callable $stream = null, $header = false, ?string $uuid = null, ?string $tagName = null)
    {
        $curl   = curl_init($url);
        $method = strtoupper($method);
        // 请求方式
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        // post请求
        if ($method == 'POST' || $method == 'PUT') {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        // 设置header头
        if ($header !== false) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $this->header);
        }

        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        // 输出header头信息
        curl_setopt($curl, CURLOPT_HEADER, false);
        // TRUE 时追踪句柄的请求字符串，从 PHP 5.1.3 开始可用。这个很关键，就是允许你查看请求header
        curl_setopt($curl, CURLINFO_HEADER_OUT, true);
        // https请求
        if (strpos('$' . $url, 'https://') == 1) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }
        //        self::$curlError = curl_error($curl);

        // 设置为流式传输，不直接返回响应数据
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, false);
        // 设置回调函数，用于处理每次接收到的数据块
        $buffer = ''; // 用于缓冲可能不完整的数据块
        curl_setopt($curl, CURLOPT_WRITEFUNCTION, function ($ch, $data) use (&$buffer, $stream, $tagName, $uuid) {
            $length = strlen($data);

            $data = str_replace(": keep-alive\n\n", '', $data);

            $buffer .= $data;
            $this->runFun($stream, $data);

            if (Cache::tags([$tagName])->get($uuid) === 'stop') {
                $this->runFun($stream, "data: [DONE]\n\n");

                Cache::tags([$tagName])->delete($uuid);

                return 0;
            }

            return $length; // 必须返回数据长度，否则传输中断
        });

        if (curl_exec($curl) === false) {
            return false;
        }

        curl_close($curl);

        return $buffer;
    }

    public function runFun(callable $stream, string $data)
    {
        try {
            if (trim($data)) {
                $stream && $stream($data);
            }
        } catch (\Throwable $e) {
            Log::error('流式请求回调异常：' . $e->getMessage() . '|' . $e->getFile() . '|' . $e->getLine());
        }
    }

    /**
     * 记录日志.
     */
    protected function debugLog(string $message, array $contents = [])
    {
        $this->debug && Log::debug($message, $contents);
    }

    /**
     * 重置.
     */
    protected function reset()
    {
        $this->header  = [];
        $this->timeout = 10;
        $this->apiHttp = null;
        $this->debug   = true;
        return $this;
    }
}
