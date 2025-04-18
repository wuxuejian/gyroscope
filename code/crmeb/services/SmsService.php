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

use crmeb\exceptions\ApiRequestException;
use Crmeb\Yihaotong\AccessToken;
use Crmeb\Yihaotong\Enum\InvoiceEnum;
use Crmeb\Yihaotong\Factory;
use Crmeb\Yihaotong\Option\InvoiceOption;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Log;
use Psr\SimpleCache\InvalidArgumentException;

class SmsService
{
    private string $accessKey;

    private string $secretKey;

    private $cache;

    private string $prefix = 'tl_captcha_';

    private string $verificationCode = '435250';

    private int $smsttl = 300;

    private int $length = 6;

    private Factory $factory;

    private array $invoiceKey = [
        'taxId', // 购方纳税人号码
        'accountName',
        'bankName',
        'bankAccount',
        'telephone',
        'companyAddress',
        'drawer',
        'email',
        'isEnterprise',
        'invoiceType', // 发票类型：81、数电发票（增值税专用发票）；82、数电发票（普通发票）；
    ];

    public function __construct(string $appid = '', string $secret = '', int $timeout = 60)
    {
        $this->accessKey   = $appid ?: sys_config('yihaotong_appid', '');
        $this->secretKey   = $secret ?: sys_config('yihaotong_appsecret', '');
        $this->cache       = app()->cache;
        $this->accessToken = new AccessToken([
            'access_key' => $this->accessKey,
            'secret_key' => $this->secretKey,
            'base_url'   => 'https://api.crmeb.com/api/v2',
            'timeout'    => $timeout,
        ], app('cache.store'));
        $this->factory = Factory::setAccessToken($this->accessToken);
    }

    /**
     * 对话.
     * @return mixed
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function dialog(array $messages, array $othen = [])
    {
        $data = [
            'messages' => $messages,
        ];
        foreach ($othen as $key => $value) {
            if (in_array($key, ['max_tokens', 'temperature', 'frequency_penalty', 'presence_penalty', 'model', 'stream', 'response_format'])) {
                $data[$key] = $value;
            }
        }

        return $this->accessToken->setBaseUri('https://ai.crmeb.com/api/v2')->request('/chat/nl_to_sql', 'post', $data);
    }

    /**
     * 获取验证码
     */
    public function captcha(string $phone): array
    {
        $data = [
            'code' => $this->getCode(),
            'time' => 3,
        ];
        $res = $this->send($phone, $this->verificationCode, $data);
        if ($res) {
            $this->cache->add($this->prefix . $phone, $data['code'], $this->smsttl);
        }
        return $res;
    }

    /**
     * 验证短信验证吗.
     * @param mixed $phone
     * @param mixed $code
     */
    public function captchaVerify($phone, $code): bool
    {
        if ($this->cache->has($this->prefix . $phone)) {
            if ($this->cache->get($this->prefix . $phone) == $code) {
                $this->cache->delete($this->prefix . $phone);
                return true;
            }
            return false;
        }
        return false;
    }

    /**
     * 短信发送
     * @throws GuzzleException
     * @throws BindingResolutionException
     */
    public function send(string $phone, string $tempId, array $data = []): array
    {
        try {
            return $this->factory->sms()->send($phone, $tempId, $data);
        } catch (\Exception $e) {
            throw new ApiRequestException($e->getMessage());
        }
    }

    /**
     * 获取开票Url.
     * @return mixed
     * @throws GuzzleException
     */
    public function invoiceUrl(string $unique, array $InvoiceInfo, array $goodsData, string $invoiceType = InvoiceEnum::INVOKE_TYPE_82)
    {
        try {
            $option = new InvoiceOption($unique);
            $option->setDataToGoods($goodsData);
            $option->invoiceType = $invoiceType;
            foreach ($InvoiceInfo as $key => $Invoice) {
                if (in_array($key, $this->invoiceKey)) {
                    $option->{$key} = $Invoice;
                }
            }
            return $this->factory->invoice()->getInvoiceIssuanceUrl($option);
        } catch (\Exception $e) {
            Log::error('获取开票Url错误:' . $e->getMessage(), [
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw new ApiRequestException($e->getMessage());
        }
    }

    /**
     * 发票作废
     * @return mixed
     * @throws GuzzleException
     */
    public function invoiceCancel(string $invoiceNum, string $applyType = '01')
    {
        try {
            return $this->factory->invoice()->redInvoiceIssuance($invoiceNum, $applyType);
        } catch (\Exception $e) {
            throw new ApiRequestException($e->getMessage());
        }
    }

    /**
     * 开票回调.
     * @return array|bool
     */
    public function invoiceCallBack(callable $callable)
    {
        $type = request()->post('type', '');
        $data = request()->post('data', '');
        if (! $data) {
            return false;
        }
        $data = $this->decrypt($data, $this->secretKey);
        if (! $data) {
            throw new ApiRequestException('解密失败');
        }
        $data = json_decode($data, true);
        return $callable($type, $data);
    }

    /**
     * 发票Base64.
     * @param mixed $invoiceNum
     * @return mixed
     */
    public function invoiceDownload($invoiceNum)
    {
        try {
            $result = $this->factory->invoice()->downloadInvoice($invoiceNum);
            if ($result['status'] === 200 && isset($result['data']['downloadBase64'])) {
                return $result['data']['downloadBase64']['pdfUrl'];
            }
        } catch (\Exception $e) {
            throw new ApiRequestException($e->getMessage());
        }
    }

    /**
     * 检测手机号是否发送过短信
     */
    public function hasCode(string $phone): mixed
    {
        return app()->cache->has($this->prefix . $phone);
    }

    /**
     * 获取验证码
     */
    private function getCode(): string
    {
        $number = [0, 1, 2, 4, 5, 6, 7, 8, 9];
        $code   = [];
        for ($i = 0; $i < $this->length; ++$i) {
            mt_srand();
            $code[] = $number[mt_rand(0, 8)];
        }
        return implode('', $code);
    }

    private function decrypt(string $encryptedData, string $key)
    {
        $key         = substr($key, 0, 32);
        $decodedData = base64_decode($encryptedData);
        $iv          = substr($decodedData, 0, 16);
        $encrypted   = substr($decodedData, 16);
        return openssl_decrypt($encrypted, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
    }
}
