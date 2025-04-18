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
use App\Http\Requests\ApiValidate;
use App\Http\Service\Notice\NoticeRecordService;
use crmeb\exceptions\ApiException;
use crmeb\exceptions\ApiRequestException;
use crmeb\services\ConfigService;
use crmeb\services\GroupDataService;
use crmeb\services\SmsService;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;
use Fastknife\Service\BlockPuzzleCaptchaService;
use Fastknife\Service\ClickWordCaptchaService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

if (! function_exists('sys_config')) {
    /**
     * 获取单个系统配置.
     * @param mixed $isCache
     * @param null|mixed $default
     * @return null|Application|(Application&ConfigService)|ConfigService|mixed|string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    function sys_config(?string $key = null, $default = null, bool $isSet = false, bool $isCache = false)
    {
        if ($key === null) {
            return app('config_crmeb');
        }

        return app('config_crmeb')->get($key, $default, $isSet, $isCache);
    }
}
if (! function_exists('sys_more')) {
    /**
     * 获取多个系统配置.
     * @return Application|(Application&ConfigService)|array|ConfigService|mixed
     * @throws BindingResolutionException
     */
    function sys_more(array $keys = [])
    {
        if ($keys === []) {
            return app('config_crmeb');
        }

        return app('config_crmeb')->more($keys);
    }
}

if (! function_exists('sys_data')) {
    /**
     * 获取组合数据配置.
     * @return Application|(Application&GroupDataService)|array|GroupDataService|mixed
     * @throws BindingResolutionException
     */
    function sys_data(?string $key = null, int $limit = 0)
    {
        if ($key === null) {
            return app('group_config');
        }

        return app('group_config')->getData($key, $limit);
    }
}

if (! function_exists('ent_data')) {
    /**
     * 获取组合数据配置.
     * @return Application|(Application&GroupDataService)|array|GroupDataService|mixed
     * @throws BindingResolutionException
     */
    function ent_data(int $entid = 0, ?string $key = null, int $limit = 0, int $page = 0)
    {
        if ($key === null) {
            return app('group_config');
        }

        return app('group_config')->getData($key, $limit, $entid, $page);
    }
}
if (! function_exists('uuid_to_uid')) {
    /**
     * 根据用户uuid获取企业用户ID.
     * @return array|int|string
     * @throws BindingResolutionException
     */
    function uuid_to_uid(string $uuid, int $entId = 1)
    {
        return app('enterprise_user')->uuidToUid($uuid, $entId);
    }
}
if (! function_exists('uuid_to_card_id')) {
    /**
     * 根据用户uuid获取企业用户名片ID.
     *
     * @return Application|array|GroupDataService|mixed
     * @throws BindingResolutionException
     */
    function uuid_to_card_id(string $uuid, int $entid = 1)
    {
        return app('enterprise_user')->uuidToCardid($uuid, $entid);
    }
}

if (! function_exists('uuid_to_card')) {
    /**
     * 根据用户uuid获取企业用户名片.
     *
     * @return Application|array|GroupDataService|mixed
     * @throws BindingResolutionException
     */
    function uuid_to_card(string $uuid, int $entid = 1, array|string $field = ['*'])
    {
        return app('enterprise_user')->uuidToCard($uuid, $entid, $field);
    }
}
if (! function_exists('card_to_uid')) {
    /**
     * 根据企业用户名片获取用户uid.
     *
     * @return Application|array|GroupDataService|mixed
     * @throws BindingResolutionException
     */
    function card_to_uid(int $cardId, int $entid = 1)
    {
        return app('enterprise_user')->cardToUid($cardId, $entid);
    }
}

if (! function_exists('uid_to_uuid')) {
    /**
     * 根据用户uuid获取企业用户ID.
     *
     * @return Application|array|GroupDataService|mixed
     * @throws BindingResolutionException
     */
    function uid_to_uuid(int $uid)
    {
        return app('enterprise_user')->uidToUuid($uid);
    }
}

if (! function_exists('is_win')) {
    /**
     * 是否是windows操作系统
     *
     * @return bool
     */
    function is_win()
    {
        if (strtoupper(substr(php_uname('s'), 0, 3)) === 'WIN') {
            return true;
        }
        return false;
    }
}

if (! function_exists('get_tree_children')) {
    /**
     * 获取tree型数据.
     *
     * @param array $data 数据
     * @param string $childrenname 子数据名
     * @param string $keyName 数据key名
     * @param string $pidName 数据上级key名
     *
     * @return array
     */
    function get_tree_children(array $data, string $childrenname = 'children', string $keyName = 'id', string $pidName = 'pid')
    {
        $list = [];
        foreach ($data as $value) {
            $list[$value[$keyName]] = $value;
        }
        $tree = []; // 格式化好的树
        foreach ($list as $item) {
            if (isset($list[$item[$pidName]]) && $item[$pidName]) {
                $list[$item[$pidName]][$childrenname][] = &$list[$item[$keyName]];
            } else {
                $tree[] = &$list[$item[$keyName]];
            }
        }

        return $tree;
    }
}

if (! function_exists('validate')) {
    /**
     * 生成验证对象
     *
     * @param mixed $validate
     * @return ApiValidate|MessageBag
     */
    function validate($validate, array $message = [], bool $failException = true)
    {
        if (is_array($validate)) {
            $validator = Validator::make(request()->all(), $validate, $message);

            if ($validator->fails() && $failException) {
                throw new ApiRequestException($validator->errors()->first(), 400, null, 200);
            }

            return $validator->errors();
        }
        if (strpos($validate, '.')) {
            // 支持场景
            [$validate, $scene] = explode('.', $validate);
        }

        /** @var ApiValidate $v */
        $v = new $validate();

        if (! empty($scene)) {
            $v->scene($scene);
        }

        return $v->setMessage($message)->failException($failException);
    }
}

if (! function_exists('get_roule_mobu')) {
    /**
     * 获取配置中的路由模块名.
     *
     * @return string
     */
    function get_roule_mobu(string $value = '', int $type = 0)
    {
        if ($type) {
            $typeName = 'ent';
        } else {
            $typeName = 'admin';
        }
        return config('app.' . $typeName . '.path', '/admin') . $value;
    }
}

if (! function_exists('get_image_frame_url')) {
    /**
     * 获取总后台图片frame URL.
     */
    function get_image_frame_url(array $query = [], int $type = 0, string $value = '/setting/uploadPicture')
    {
        $queryData = [];
        foreach ($query as $k => $v) {
            $queryData[] = $k . '=' . $v;
        }
        $roule    = get_roule_mobu($value, $type);
        $queryStr = implode('&', $queryData);

        return $roule . (! str_contains($roule, '?') ? '?' : '&') . $queryStr;
    }
}

if (! function_exists('verification_api_check')) {
    /**
     * 验证手机号验证码
     *
     * @return bool
     */
    function verification_api_check(string $verificationCode, string $phone)
    {
        if (! config('sms.verification')) {
            return true;
        }
        return app()->get(SmsService::class)->captchaVerify($phone, $verificationCode);
    }
}

if (! function_exists('link_file')) {
    /**
     * 文件路径.
     */
    function link_file(string $path): string
    {
        if (! str_contains($path, 'http')) {
            $path = sys_config('site_url', config('app.url')) . $path;
        }

        return $path;
    }
}

if (! function_exists('get_os')) {
    /**
     * 获取操作系统
     *
     * @return string
     */
    function get_os()
    {
        $user_agent = request()->header('user-agent');
        if (stripos($user_agent, 'windows nt 5.0')) {
            $user_os = 'Windows 2000';
        } elseif (stripos($user_agent, 'windows nt 9')) {
            $user_os = 'Windows 9X';
        } elseif (stripos($user_agent, 'windows nt 5.1')) {
            $user_os = 'Windows XP';
        } elseif (stripos($user_agent, 'windows nt 5.2')) {
            $user_os = 'Windows 2003';
        } elseif (stripos($user_agent, 'windows nt 6.0')) {
            $user_os = 'Windows Vista';
        } elseif (stripos($user_agent, 'windows nt 6.1')) {
            $user_os = 'Windows 7';
        } elseif (stripos($user_agent, 'windows nt 6.2')) {
            $user_os = 'Windows 8';
        } elseif (stripos($user_agent, 'windows nt 6.3')) {
            $user_os = 'Windows 8.1';
        } elseif (stripos($user_agent, 'windows nt 10')) {
            $user_os = 'Windows 10';
        } elseif (stripos($user_agent, 'windows phone')) {
            $user_os = 'Windows Phone';
        } elseif (stripos($user_agent, 'android')) {
            $user_os = 'Android';
        } elseif (stripos($user_agent, 'iphone')) {
            $user_os = 'iPhone';
        } elseif (stripos($user_agent, 'ipad')) {
            $user_os = 'iPad';
        } elseif (stripos($user_agent, 'mac')) {
            $user_os = 'Mac';
        } elseif (stripos($user_agent, 'sunos')) {
            $user_os = 'Sun OS';
        } elseif (stripos($user_agent, 'bsd')) {
            $user_os = 'BSD';
        } elseif (stripos($user_agent, 'ubuntu')) {
            $user_os = 'Ubuntu';
        } elseif (stripos($user_agent, 'linux')) {
            $user_os = 'Linux';
        } elseif (stripos($user_agent, 'unix')) {
            $user_os = 'Unix';
        } else {
            $user_os = 'Other';
        }
        return $user_os;
    }
}

if (! function_exists('get_download_url')) {
    /**
     * 获取下载地址
     *
     * @return string
     */
    function get_download_url(string $fileId, array $param = [], string $path = 'api/ent')
    {
        $param['fileId'] = $fileId;
        $signature       = Crypt::encryptString(json_encode($param));

        return sys_config('site_url', config('app.url')) . ($path ? '/' . $path : '') . '/common/download?signature=' . $signature;
    }
}

if (! function_exists('birthday_to_age')) {
    /**
     * 年份获取年龄.
     *
     * @return false|mixed|string
     */
    function birthday_to_age(string $birthday)
    {
        [$year, $month, $day] = explode('-', $birthday);
        $year_diff            = date('Y') - $year;
        $month_diff           = date('m') - $month;
        $day_diff             = date('d') - $day;
        if ($day_diff < 0 || $month_diff < 0) {
            --$year_diff;
        }

        return $year_diff;
    }
}

if (! function_exists('time_contrast_api_check')) {
    /**
     * 时间对比.
     *
     * @param string $entTime 结束时间
     * @param string $startTime 开始时间
     * @param bool $just 正比或反比
     */
    function time_contrast_api_check(string $entTime, string $startTime, bool $just = false): bool
    {
        if ($just) {
            return strtotime($startTime) < strtotime($entTime);
        }
        return strtotime($entTime) > strtotime($startTime);
    }
}

if (! function_exists('password_confirm_api_check')) {
    /**
     * 密码确认.
     *
     * @return bool
     */
    function password_confirm_api_check(string $password, string $passwordConfirm)
    {
        return $password === $passwordConfirm;
    }
}

if (! function_exists('message')) {
    /**
     * 消息发送
     *
     * @return NoticeRecordService
     * @throws BindingResolutionException
     */
    function message()
    {
        /* @var NoticeRecordService $service */
        return app()->get(NoticeRecordService::class);
    }
}

if (! function_exists('get_week_num')) {
    /**
     * 根据类型获取星期几.
     *
     * @return int
     */
    function get_week_num(string $type)
    {
        $week = 0;
        switch (strtolower($type)) {
            case 'monday':
                $week = 1;
                break;
            case 'tuesday':
                $week = 2;
                break;
            case 'wednesday':
                $week = 3;
                break;
            case 'thursday':
                $week = 4;
                break;
            case 'friday':
                $week = 5;
                break;
            case 'saturday':
                $week = 6;
                break;
            case 'sunday':
                $week = 7;
                break;
        }

        return $week;
    }
}

if (! function_exists('get_password_message')) {
    /**
     * 获取密码错误提示.
     *
     * @return mixed
     */
    function get_password_message(array $type = [])
    {
        $type = $type ?: sys_config('login_password_type');
        $type = is_array($type) ? $type : [0, 2];
        sort($type);
        $typeStr = implode('', $type);

        $message = [
            '0'    => '数字',
            '01'   => '数字+字母',
            '02'   => '数字+小写字母',
            '03'   => '数字+特殊字符',
            '12'   => '大写字母+小写字母',
            '13'   => '大写字母+特殊字符',
            '23'   => '小写字母+特殊字符',
            '012'  => '数字+小写字母+大写字母',
            '0123' => '数字+小写字母+大写字母+特殊字符',
            '013'  => '数字+大写字母+特殊字符',
            '023'  => '数字+小写字母+特殊字符',
            '123'  => '小写字母+大写字母+特殊字符',
        ];

        return $message[$typeStr];
    }
}
if (! function_exists('assoc_unique')) {
    /**
     * @param mixed $arr
     * @param mixed $key
     * @return mixed
     */
    function assoc_unique(&$arr, $key)
    {
        $tmpArr = [];
        foreach ($arr as $k => $v) {
            if (in_array($v[$key], $tmpArr)) {
                unset($arr[$k]);
            } else {
                $tmpArr[] = $v[$key];
            }
        }
        sort($arr); // sort函数对数组进行排序

        return $arr;
    }
}

if (! function_exists('str_content')) {
    function str_content($content)
    {
        $content_02 = htmlspecialchars_decode($content); // 把一些预定义的 HTML 实体转换为字符
        $content_03 = str_replace('&nbsp;', '', $content_02); // 将空格替换成空
        $contents   = strip_tags($content_03); // 函数剥去字符串中的 HTML、XML 以及 PHP 的标签,获取纯文本内容

        return mb_substr($contents, 0, 320, 'utf-8') . '...'; // 返回字符串中的前100字符串长度的字符
    }
}

if (! function_exists('get_start_and_end_time')) {
    /**
     * 获取当前时间的开始时间和结束时间.
     *
     * @return string[]
     */
    function get_start_and_end_time(int $period)
    {
        $now       = now();
        $startTime = $endTime = '';
        switch ($period) {
            case 1:
                $startTime = $now->startOfWeek()->format('y/m/d') . ' 00:00:00';
                $endTime   = $now->endOfWeek()->format('y/m/d') . ' 23:59:59';
                break;
            case 2:
                $startTime = $now->startOfMonth()->format('y/m/d') . ' 00:00:00';
                $endTime   = $now->endOfMonth()->format('y/m/d') . ' 23:59:59';
                break;
            case 3:
                $startTime = $now->startOfYear()->format('y/m/d') . ' 00:00:00';
                $endTime   = $now->endOfYear()->format('y/m/d') . ' 23:59:59';
                break;
            case 5:
                $startTime = $now->startOfQuarter()->format('y/m/d') . ' 00:00:00';
                $endTime   = $now->endOfQuarter()->format('y/m/d') . ' 23:59:59';
                break;
            case 4:
                if ($now->month >= 7) {
                    $startTime = $now->year . '/7/01 00:00:00';
                    $day       = $now->endOfYear()->endOfDay()->day;
                    $endTime   = $now->year . '/12/' . $day . ' 00:00:00';
                } else {
                    $startTime = $now->year . '/1/01 00:00:00';
                    $day       = $now->startOfYear()->endOfDay()->day;
                    $endTime   = $now->year . '/6/' . $day . ' 23:59:59';
                }
                break;
        }

        return [$startTime, $endTime];
    }
}

if (! function_exists('get_period_type_str')) {
    /**
     * 获取绩效类型.
     *
     * @return string
     */
    function get_period_type_str(int $period)
    {
        return match ($period) {
            1       => '周',
            2       => '月',
            3       => '年',
            4       => '季度',
            5       => '半年',
            default => ''
        };
    }
}

if (! function_exists('toArray')) {
    /**
     * 数据库资源转数组.
     * @param mixed $result
     */
    function toArray($result): array
    {
        if ($result instanceof Model || $result instanceof Collection) {
            return $result->toArray();
        }
        return is_array($result) ? $result : [];
    }
}

if (! function_exists('micro_time')) {
    /**
     * 获取时间.
     */
    function micro_time()
    {
        [$usec, $sec] = explode(' ', microtime());
        return $sec . substr($usec, 2, 3);
    }
}

if (! function_exists('crypto_encode')) {
    /**
     * 加密.
     * @param mixed $data
     */
    function crypto_encode($data): string
    {
        $key  = Key::loadFromAsciiSafeString(env('CRYPTO_KEY'));
        $data = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return Crypto::encrypt($data, $key);
    }
}

if (! function_exists('crypto_decode')) {
    /**
     * 解密.
     * @param mixed $data
     */
    function crypto_decode($data): mixed
    {
        $key = Key::loadFromAsciiSafeString(env('CRYPTO_KEY'));
        $str = Crypto::decrypt($data, $key);
        return json_decode($str, true);
    }
}

if (! function_exists('format_val')) {
    function format_val($value)
    {
        if (str_contains($value, ' ') || str_contains($value, '$')) {
            $value = "\"{$value}\"";
        }

        return $value;
    }
}

if (! function_exists('modify_env')) {
    function modify_env($data)
    {
        $contentArray = collect(file(base_path('.env'), FILE_IGNORE_NEW_LINES));

        $contentArray->transform(function ($item) use ($data) {
            foreach ($data as $key => $value) {
                $itemArr = explode('=', $item);
                if ($itemArr[0] == $key) {
                    return $key . '=' . format_val((string) $value);
                }
            }

            return $item;
        });

        File::put(base_path('.env'), implode(PHP_EOL, $contentArray->toArray()));
    }
}

if (! function_exists('sql_split')) {
    function sql_split($sql, $tablepre)
    {
        if ($tablepre != 'eb_') {
            $sql = str_replace('eb_', $tablepre, $sql);
        }

        $sql = preg_replace('/TYPE=(InnoDB|MyISAM|MEMORY)( DEFAULT CHARSET=[^; ]+)?/', 'ENGINE=\1 DEFAULT CHARSET=utf8', $sql);

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
}
if (! function_exists('getVersion')) {
    function getVersion(string $key = '')
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
if (! function_exists('getFieldData')) {
    /**
     * 按照键名过滤数据.
     */
    function getFieldData(array $data, array $field): array
    {
        return array_intersect_key($data, array_flip($field));
    }
}

if (! function_exists('getVersion')) {
    /**
     * 获取版本号.
     */
    function getVersion(string $key = ''): array|string
    {
        $version_arr    = [];
        $curent_version = @file(base_path('.version'));
        foreach ($curent_version as $val) {
            [$k, $v]         = explode('=', $val);
            $version_arr[$k] = $v;
        }
        return $key ? trim($version_arr[$key]) : $version_arr;
    }
}

if (! function_exists('getMimetype')) {
    /**
     * 获取文件类型.
     * @param mixed $name
     */
    function getMimetype($name)
    {
        $parts = explode('.', $name);
        if (count($parts) > 1) {
            $ext = strtolower(end($parts));
            if (isset(config('upload.mime_types')[$ext])) {
                return config('upload.mime_types')[$ext];
            }
        }

        return null;
    }
}

if (! function_exists('text_strlen_confirm_api_check')) {
    /**
     * 文本长度确认.
     *
     * @return bool
     */
    function text_strlen_confirm_api_check(string $value, string $len)
    {
        return mb_strlen(strip_tags(stripslashes(htmlspecialchars_decode($value)))) <= $len;
    }
}

if (! function_exists('get_env')) {
    /**
     * 根据key获取env的值.
     * @param mixed $key
     */
    function get_env($key): array|string
    {
        if ($key === '') {
            return $key;
        }
        $arr  = [];
        $str  = '';
        $data = collect(file(base_path('.env'), FILE_IGNORE_NEW_LINES))->toArray();
        foreach ($data as $item) {
            if (! $item) {
                continue;
            }
            $itemArr = explode('=', $item);
            if (! isset($itemArr[0]) || ! isset($itemArr[1])) {
                continue;
            }
            if (is_string($key)) {
                if ($key == $itemArr[0]) {
                    $str = trim(format_val($itemArr[1]), '"');
                    break;
                }
            }
            if (is_array($key)) {
                if ($key) {
                    if (in_array($itemArr[0], $key)) {
                        $arr[$itemArr[0]] = trim(format_val($itemArr[1]), '"');
                    }
                } else {
                    $arr[$itemArr[0]] = trim(format_val($itemArr[1]), '"');
                }
            }
        }
        return is_array($key) ? $arr : $str;
    }
}

if (! function_exists('sort_mode')) {
    /**
     * 获取排序.
     * @return mixed[]
     */
    function sort_mode(null|array|string $sort = null)
    {
        $orderField = request()->input('sort_field', '');
        $orderValue = request()->input('sort_value', '');
        if (! $orderField || ! $orderValue) {
            return $sort;
        }
        if (! $sort) {
            return [$orderField => $orderValue];
        }
        if (! is_array($sort)) {
            $sort = [$orderField => $orderValue, $sort];
        } else {
            $sort = array_merge([$orderField => $orderValue], $sort);
        }
        return $sort;
    }
}

if (! function_exists('is_dimensional_data')) {
    /**
     * 是否为多维数组.
     * @return bool
     */
    function is_dimensional_data(mixed $data = null)
    {
        if (! is_array($data) || empty($data)) {
            return false;
        }

        foreach ($data as $val) {
            return is_array($val);
        }
        return false;
    }
}

if (! function_exists('format_size')) {
    /**
     * 格式化大小.
     * @param mixed $b
     * @param mixed $times
     * @return bool
     */
    function format_size($b, $times = 0)
    {
        if ($b > 1024) {
            $temp = $b / 1024;
            ++$times;
            return format_size($temp, $times);
        }
        $unit = match ($times) {
            1       => 'KB',
            2       => 'MB',
            3       => 'GB',
            4       => 'TB',
            5       => 'PB',
            6       => 'EB',
            7       => 'ZB',
            default => 'B',
        };
        return sprintf('%.2f', $b) . $unit;
    }
}

if (! function_exists('prefix_correction')) {
    /**
     * 表前缀处理.
     * @return string
     */
    function prefix_correction(string $content, string $correct = '')
    {
        if (! $content) {
            return $content;
        }
        $defaultPrefix = 'eb_';
        if ($correct === '') {
            $correct = get_env('DB_PREFIX');
            if (! $correct) {
                $correct = $defaultPrefix;
            }
        }
        if ($correct !== $defaultPrefix) {
            $content = str_replace('`' . $defaultPrefix, '`' . $correct, $content);
        }
        return $content;
    }
}

if (! function_exists('aj_captcha_create')) {
    /**
     * 创建验证码
     * @return array
     */
    function aj_captcha_create(string $captchaType)
    {
        return aj_get_serevice($captchaType)->get();
    }
}

if (! function_exists('aj_get_serevice')) {
    /**
     * @return BlockPuzzleCaptchaService|ClickWordCaptchaService
     */
    function aj_get_serevice(string $captchaType)
    {
        $config                         = Config::get('ajcaptcha');
        $config['cache']['constructor'] = app('cache.store');
        switch ($captchaType) {
            case 'clickWord':
                $service = new ClickWordCaptchaService($config);
                break;
            case 'blockPuzzle':
                $service = new BlockPuzzleCaptchaService($config);
                break;
            default:
                throw new ApiException('captchaType参数不正确！');
        }
        return $service;
    }
}

if (! function_exists('aj_captcha_check_one')) {
    /**
     * 验证滑块1次验证
     * @return bool
     */
    function aj_captcha_check_one(string $captchaType, string $token, string $pointJson)
    {
        aj_get_serevice($captchaType)->check($token, $pointJson);
        return true;
    }
}

if (! function_exists('aj_captcha_check_two')) {
    /**
     * 验证滑块2次验证
     * @return bool
     */
    function aj_captcha_check_two(string $captchaType, string $captchaVerification)
    {
        aj_get_serevice($captchaType)->verificationByEncryptCode($captchaVerification);
        return true;
    }
}

if (! function_exists('array_find')) {
    /**
     * 获取数组中指定key的值
     * @param null|mixed $key
     * @return null|mixed
     */
    function array_find(array $data, $key = null)
    {
        if (! $key) {
            return $data;
        }

        $value = null;
        foreach ($data as $id => $item) {
            if (is_callable($key)) {
                if ($key($item)) {
                    $value = $item;
                    break;
                }
            } else {
                if ($key === $id) {
                    $value = $item;
                    break;
                }
            }
        }
        return $value;
    }
}

if (! function_exists('is_nested_array')) {
    /**
     * 是不是嵌套数组.
     * @param mixed $var
     * @return bool
     */
    function is_nested_array($var)
    {
        if (! is_array($var)) {
            return false;
        }
        foreach ($var as $value) {
            if (is_array($value)) {
                return true;
            }
            if (is_nested_array($value)) {
                return true;
            }
        }
        return false;
    }
}
