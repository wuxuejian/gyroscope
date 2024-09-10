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
namespace App\Http\Service\User;

use App\Constants\CacheEnum;
use App\Constants\CommonEnum;
use App\Constants\UserEnum;
use App\Http\Contract\System\MenusInterface;
use App\Http\Contract\User\UserInterface;
use App\Http\Dao\User\UserDao;
use App\Http\Service\Assess\AssessScoreService;
use App\Http\Service\BaseService;
use App\Http\Service\Company\CompanyService;
use App\Http\Service\Company\CompanyUserService;
use App\Http\Service\Frame\FrameAssistService;
use App\Http\Service\Position\PositionJobService;
use Carbon\Carbon;
use crmeb\utils\Regex;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\JWT;

/**
 * 基础用户.
 */
class UserService extends BaseService implements UserInterface
{
    public function __construct(UserDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取用户信息.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getInfo(int $uid, array $field = ['*'], array $with = []): array
    {
        return toArray($this->dao->get($uid, $field, $with));
    }

    /**
     * 用户登录.
     * @param mixed $origin
     * @param mixed $isSingle
     * @throws BindingResolutionException
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function login(string $account, string $password = null, string $client = '', string $mac = '', string $clientId = '', $origin = CommonEnum::ORIGIN_WEB, $isSingle = UserEnum::MULTIPORT_LOGIN): array
    {
        // 设置token过期时间
        Config::set('jwt.ttl', (int) sys_config('login_time_out', 12) * 3600);
        /** @var JWT $jwt */
        $jwt         = app()->get(JWT::class);
        $companyUser = app()->get(CompanyUserService::class);
        $userName    = $companyUser->value(['phone' => $account], 'name');
        // 账号密码登录
        if ($password) {
            $key = 'login_error_count_' . $account;
            if (Cache::tags([CacheEnum::TAG_OTHER])->has($key) && Cache::tags([CacheEnum::TAG_OTHER])->get($key) >= (int) sys_config('login_time_out', 5)) {
                throw $this->exception('账号或密码错误次数已达上限请稍后再试!');
            }
            $flag     = preg_match(Regex::PHONE_NUMBER, $account);
            $userInfo = $this->dao->get([$flag ? 'phone' : 'account' => $account]);
            if ($flag && ! $userInfo) {
                $userInfo = $this->dao->create([
                    'real_name' => $userName ?: substr($account, 0, 3) . '****' . substr($account, 7, 4),
                    'phone'     => $account,
                    'status'    => 1,
                    'avatar'    => 'https://shmily-album.oss-cn-shenzhen.aliyuncs.com/admin_face/face' . rand(1, 10) . '.png',
                ]);
            }
            if (! $userInfo) {
                throw $this->exception('用户不存在');
            }
            if (! password_verify($password, $userInfo->password)) {
                $this->lookLogin($key);
                if (Cache::tags([CacheEnum::TAG_OTHER])->has($key)) {
                    Cache::increment($key);
                } else {
                    Cache::add($key, 1, (int) sys_config('login_time_out', 1) * 60 * 60);
                }
                throw $this->exception('账号或密码不正确');
            }
            $token = $jwt->fromSubject($userInfo);
        } else {
            // 手机验证码登录
            $userInfo = $this->dao->get(['phone' => $account]);
            if (! $userInfo) {
                $userInfo = $this->dao->create([
                    'real_name' => $userName ?: substr($account, 0, 3) . '****' . substr($account, 7, 4),
                    'phone'     => $account,
                    'status'    => 1,
                    'avatar'    => 'https://shmily-album.oss-cn-shenzhen.aliyuncs.com/admin_face/face' . rand(1, 10) . '.png',
                ]);
            }
            if (! $userInfo) {
                throw $this->exception('手机号用户不存在或创建手机号用户失败');
            }
            $token = $jwt->fromSubject($userInfo);
        }
        if (! $token) {
            $this->lookLogin($key);
            throw $this->exception('token创建失败');
        }
        if (! $userInfo->status == UserEnum::USER_STATUS_NORMAL) {
            throw $this->exception('您已被锁定,无法登录!');
        }
        $userInfo->last_ip = app('request')->ip();
        //记录unipush通道ID
        if ($clientId) {
            $userInfo->client_id  = $clientId;
            $userInfo->uni_online = 1;
        }
        ++$userInfo->login_count;
        $companyService = app()->get(CompanyService::class);
        $entid          = $userInfo->entid;
        if (! $entid) {
            $userInfo->entid = $entid = $companyUser->value(['uid' => $userInfo->uid, 'verify' => 1, 'status' => 1], 'entid') ?: 0;
        }
        $userInfo->save();
        if ($entid) {
            $enterprise = toArray($companyService->get(['id' => $entid], ['title', 'enterprise_name', 'enterprise_name_en', 'id as entid', 'logo', 'uniqued']));
            if ($enterprise) {// 企业考核相关配置信息
                $enterprise['maxScore'] = app()->get(AssessScoreService::class)->max(['entid' => $entid], 'max') ?: 0;
                $enterprise['culture']  = sys_config('enterprise_culture');
            }
        }
        $userInfo = $userInfo->only(['account', 'phone', 'avatar', 'real_name', 'uid', 'entid', 'is_init']);
        if ($isSingle) {
            $this->remeberToken($userInfo['uid'], $token, $client, $mac, app('request')->ip());
        }
        $uuId               = $userInfo['uid'];
        $company            = app()->get(CompanyUserService::class);
        $userId             = uuid_to_uid($userInfo['uid'], $entid);
        $jobId              = $company->userInfo($userId, 'job');
        $userInfo['job_id'] = $jobId;
        $userInfo['job']    = toArray(app()->get(PositionJobService::class)->get($jobId));
        $userInfo['frames'] = app()->get(FrameAssistService::class)->getUserFrames($userInfo['uid'], $entid);
        $userInfo['userId'] = $userId;
        $userInfo['card']   = uuid_to_card($userInfo['uid'], $entid, ['id', 'name', 'avatar', 'phone']);
        if ($userInfo['card']) {
            $userInfo['real_name'] = $userInfo['card']['name'];
        }

        $menuService = app()->get(MenusInterface::class);
        return match ($origin) {
            CommonEnum::ORIGIN_WEB => [
                'token'      => $token,
                'userInfo'   => $userInfo,
                'enterprise' => $enterprise ?? (object) [],
            ],
            CommonEnum::ORIGIN_UNI => [
                'token'      => $token,
                'userInfo'   => $userInfo,
                'menus'      => $menuService->getMenusForUni($uuId, $entid),
                'enterprise' => $enterprise ?? (object) [],
            ],
            default => [],
        };
    }

    /**
     * 用户注册.
     * @param $phone
     * @param $password
     * @throws BindingResolutionException
     */
    public function register($phone, $password): array
    {
        if ($this->dao->count(['phone' => $phone])) {
            throw $this->exception('手机号码已注册,请直接登录');
        }

        return $this->dao->create([
            'phone'     => $phone,
            'real_name' => substr($phone, 0, 3) . '****' . substr($phone, 7, 4),
            'password'  => password_hash($password, PASSWORD_BCRYPT),
            'avatar'    => 'https://shmily-album.oss-cn-shenzhen.aliyuncs.com/admin_face/face' . rand(1, 10) . '.png',
        ])->toArray();
    }

    /**
     * 用户修改密码
     * @throws BindingResolutionException
     */
    public function password(string $phone, string $password): int
    {
        if (! $this->dao->count(['phone' => $phone])) {
            throw $this->exception('您的手机号码尚未注册');
        }

        return $this->dao->update(['phone' => $phone], ['password' => password_hash($password, PASSWORD_BCRYPT), 'is_init' => 0]);
    }

    /**
     * 系统修改用户密码
     * @throws BindingResolutionException
     */
    public function updatePasswordFromUid(int $uid, string $password): int
    {
        return $this->dao->update(uid_to_uuid($uid), ['password' => password_hash($password, PASSWORD_BCRYPT), 'is_init' => 0]);
    }

    /**
     * 退出登录.
     */
    public function logout(): void
    {
        auth('user')->logout();
    }

    /**
     * 获取扫码登录参数.
     */
    public function scanCodeKey(): array
    {
        $key = password_hash(uniqid('scan'), PASSWORD_BCRYPT);
        Cache::add('scan.key.' . $key, 0, 180);
        $expire_time = sys_config('verify_expire_time', 3);
        $expire_time = Carbon::now()->addMinutes($expire_time)->toDateTimeString();
        return compact('key', 'expire_time');
    }

    /**
     * 扫码参数状态
     * @param $key
     * @throws BindingResolutionException
     */
    public function keyStatus($key): array
    {
        return Cache::remember(md5('scan_key' . $key), 180, function () use ($key) {
            if (($phone = $this->dao->value(['scan_key' => $key], 'phone')) && $key) {
                Cache::delete('scan.key.' . $key);
                $this->dao->update(['phone' => $phone], ['scan_key' => '']);
                return $this->login($phone);
            }
            if (Cache::tags([CacheEnum::TAG_OTHER])->has('scan.key.' . $key)) {
                if (Cache::tags([CacheEnum::TAG_OTHER])->get('scan.key.' . $key)) {
                    return [
                        'status' => 1,
                        'msg'    => '已扫码',
                    ];
                }
                return [
                    'status' => 0,
                    'msg'    => '未扫码',
                ];
            }
            return [
                'status' => -1,
                'msg'    => '参数已失效,请重新获取',
            ];
        });
    }

    /**
     * 用户绑定扫码参数.
     */
    public function scanWithUser(string $key, string $uuid): bool
    {
        if (Cache::tags([CacheEnum::TAG_OTHER])->has('scan.key.' . $key)) {
            Cache::put('scan.key.' . $key, $uuid, 180);
            Cache::delete(md5('scan_key' . $key));
            return true;
        }
        return false;
    }

    /**
     * 确认扫码登录.
     * @throws BindingResolutionException
     */
    public function scanLogin(string $uuid, string $key, int|string $status): void
    {
        if (Cache::tags([CacheEnum::TAG_OTHER])->has('scan.key.' . $key) && Cache::tags([CacheEnum::TAG_OTHER])->get('scan.key.' . $key) == $uuid) {
            if ($status) {
                $this->dao->update($uuid, ['scan_key' => $key]);
            } else {
                Cache::delete('scan.key.' . $key);
            }
            Cache::delete(md5('scan_key' . $key));
        }
    }

    /**
     * 修改用户信息.
     *
     * @param $uid
     * @param mixed $params
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @throws \Illuminate\Validation\ValidationException
     * @return mixed
     */
    public function updateInfo($uid, $params)
    {
        [$email, $phone, $password, $passwordConfirm, $sex, $status, $remark] = array_values($this->filterParams($params, ['email', 'phone', 'password', 'password_confirm', 'sex', 'status', 'remark']));
        $data                                                                 = [];
        if ($email) {
            $data['email'] = $email;
        }
        if ($phone) {
            $data['phone'] = $phone;
            if ($this->dao->exists(['phone' => $phone, 'not_uid' => $uid])) {
                throw $this->exception('手机号重复');
            }
        }
        if ($password && $passwordConfirm) {
            if ($password != $passwordConfirm) {
                throw $this->exception('两次输入的密码不一致');
            }
            $data['password'] = $password;
        }
        if ($sex !== '') {
            $data['sex'] = $sex;
        }
        if ($status !== '') {
            $data['status'] = $status;
        }
        if ($remark) {
            $data['remark'] = $remark;
        }
        if ($data) {
            $this->dao->update($uid, $data);
        }

        return true;
    }

    /**
     * 添加锁定登录.
     */
    public function lookLogin(string $key)
    {
        if (Cache::tags([CacheEnum::TAG_OTHER])->has($key)) {
            Cache::increment($key);
        } else {
            Cache::add($key, 1, (int) sys_config('login_time_out', 1) * 60 * 60);
        }
    }

    /**
     * 记录/失效token.
     * @param $uid
     * @param $token
     * @param $client
     * @param $mac
     * @param $ip
     * @throws BindingResolutionException
     */
    private function remeberToken($uid, $token, $client, $mac, $ip): void
    {
        /** @var UserTokenService $tokenServices */
        $tokenServices = app()->get(UserTokenService::class);
        if ($tokenServices->exists(['uid' => $uid, 'client' => $client])) {
            $info = toArray($tokenServices->get(['uid' => $uid, 'client' => $client]));
            /** @var JWT $jwt */
            $jwt = app()->get(JWT::class);
            if ($info['last_token']) {
                try {
                    $jwt->setToken($info['last_token'])->invalidate(true);
                } catch (TokenExpiredException|TokenInvalidException $e) {
                    // 因为让一个过期的token再失效，会抛出异常，所以我们捕捉异常，不需要做任何处理
                }
            }
            $tokenServices->update($info['id'], [
                'last_token'     => $info['remember_token'],
                'remember_token' => $token,
                'client'         => $client,
                'last_ip'        => $ip,
                'mac'            => $mac,
            ]);
        } else {
            $tokenServices->create([
                'uid'            => $uid,
                'client'         => $client,
                'last_ip'        => $ip,
                'mac'            => $mac,
                'remember_token' => $token,
            ]);
        }
    }
}
