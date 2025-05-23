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

namespace crmeb\services\upload\extend\cos;

/**
 *  Class 生成签名.
 */
class Signature
{
    /**
     * @var string
     */
    private $accessKey;

    /**
     * @var string
     */
    private $secretKey;

    /**
     * @var array
     */
    private $options;

    /**
     * Signature constructor.
     */
    public function __construct(string $accessKey, string $secretKey, array $options = [], string $token = '')
    {
        $this->accessKey  = $accessKey;
        $this->secretKey  = $secretKey;
        $this->options    = $options;
        $this->token      = $token;
        $this->signHeader = [
            'cache-control',
            'content-disposition',
            'content-encoding',
            'content-length',
            'content-md5',
            'content-type',
            'expect',
            'expires',
            'host',
            'if-match',
            'if-modified-since',
            'if-none-match',
            'if-unmodified-since',
            'origin',
            'range',
            'response-cache-control',
            'response-content-disposition',
            'response-content-encoding',
            'response-content-language',
            'response-content-type',
            'response-expires',
            'transfer-encoding',
            'versionid',
        ];
        date_default_timezone_set('PRC');
    }

    public function needCheckHeader($header)
    {
        if ($this->startWith($header, 'x-cos-')) {
            return true;
        }
        if (in_array($header, $this->signHeader)) {
            return true;
        }
        return false;
    }

    /**
     * @return string[]
     * @email 136327134@qq.com
     * @date 2022/9/26
     */
    public function signRequest(string $method, string $urlPath, array $querys = [], array $headers = [])
    {
        $authorization = $this->createAuthorization($method, $urlPath, $querys, $headers);
        return ['Authorization' => $authorization];
    }

    /**
     * @param string $expires
     * @return string
     * @email 136327134@qq.com
     * @date 2022/9/26
     */
    public function createAuthorization(string $method, string $urlPath, array $querys = [], array $headers = [], $expires = '+30 minutes')
    {
        if (is_null($expires) || ! strtotime($expires)) {
            $expires = '+30 minutes';
        }
        $signTime          = time() - 60 . ';' . strtotime($expires);
        $urlParamListArray = [];
        foreach ($querys as $query) {
            if (! empty($query)) {
                $tmpquery = explode('=', $query);
                // 为了保证CI的key中有=号的情况也能正常通过，ci在这层之前已经encode了，这里需要拆开重新encode，防止上方explode拆错
                $key = strtolower(rawurlencode(urldecode($tmpquery[0])));
                if (count($tmpquery) >= 2) {
                    $value = $tmpquery[1];
                } else {
                    $value = '';
                }
                // host开关
                if (! $this->options['signHost'] && $key == 'host') {
                    continue;
                }
                $urlParamListArray[$key] = $key . '=' . $value;
            }
        }
        ksort($urlParamListArray);
        $urlParamList   = join(';', array_keys($urlParamListArray));
        $httpParameters = join('&', array_values($urlParamListArray));

        $headerListArray = [];
        foreach ($headers as $key => $value) {
            $key = strtolower(urlencode($key));
            if (! $value) {
                continue;
            }
            $value = rawurlencode((string) $value);
            if (! $this->options['signHost'] && $key == 'host') {
                continue;
            }
            if ($this->needCheckHeader($key)) {
                $headerListArray[$key] = $key . '=' . $value;
            }
        }
        ksort($headerListArray);
        $headerList  = join(';', array_keys($headerListArray));
        $httpHeaders = join('&', array_values($headerListArray));
        $httpString  = strtolower($method) . "\n" . urldecode($urlPath) . "\n" . $httpParameters .
            "\n" . $httpHeaders . "\n";

        $sha1edHttpString = sha1($httpString);
        $stringToSign     = "sha1\n{$signTime}\n{$sha1edHttpString}\n";
        $signKey          = hash_hmac('sha1', $signTime, trim($this->secretKey));
        $signature        = hash_hmac('sha1', $stringToSign, $signKey);
        return 'q-sign-algorithm=sha1&q-ak=' . trim($this->accessKey) .
            "&q-sign-time={$signTime}&q-key-time={$signTime}&q-header-list={$headerList}&q-url-param-list={$urlParamList}&" .
            "q-signature={$signature}";
    }

    /**
     * @return string[]
     * @email 136327134@qq.com
     * @date 2022/9/26
     */
    public function createPresignedUrl(string $url, string $method, string $urlPath, array $querys = [], array $headers = [], string $expires = '+30 minutes')
    {
        $authorization = $this->createAuthorization($method, $urlPath, $querys, $headers, $expires);
        $uri           = $url;
        $query         = 'sign=' . urlencode($authorization) . '&' . implode('&', $querys);
        if ($this->token != null) {
            $query = $query . '&x-cos-security-token=' . $this->token;
        }
        return [$uri, $query];
    }

    /**
     * @email 136327134@qq.com
     * @date 2022/9/29
     * @return bool
     */
    protected function startWith($haystack, $needle)
    {
        $length = strlen($needle);
        if ($length == 0) {
            return true;
        }
        return substr($haystack, 0, $length) === $needle;
    }
}
