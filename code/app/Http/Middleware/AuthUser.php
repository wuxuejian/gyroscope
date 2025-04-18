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

namespace App\Http\Middleware;

use crmeb\exceptions\AuthException;
use crmeb\interfaces\ApiMiddlewareInterface;
use crmeb\traits\MiddlewareTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;

/**
 * 用户后台登录
 * Class AuthEnterprise.
 */
class AuthUser extends BaseMiddleware implements ApiMiddlewareInterface
{
    use MiddlewareTrait;

    /**
     * @return mixed|void
     */
    public function before(Request $request)
    {
        app('tymon.jwt.provider.auth')->setProvider(Auth::createUserProvider('user'));
        try {
            $userInfo = $this->auth->setToken($request->bearerToken())->authenticate();
        } catch (TokenExpiredException $e) {
            try {
                $this->other['token'] = $this->auth->refresh();
                $userInfo             = Auth::guard($this->getArgs(1, 'user'))->onceUsingId($this->auth->parseToken()->getClaim('sub'));
            } catch (JWTException) {
                throw new AuthException('登录信息已失效', 410001);
            }
        } catch (JWTException|TokenInvalidException|UnauthorizedHttpException $e) {
            throw new AuthException(__($e->getMessage()), 410003);
        }

        if (! isset($userInfo) || ! $userInfo) {
            throw new AuthException('用户信息不存在', 410002);
        }
        $request->macro('uuId', function () use ($userInfo) {
            return $userInfo->uid;
        });
        $request->macro('userInfo', function (?string $key = null) use ($userInfo) {
            $userInfo = $userInfo->toArray();
            if ($key) {
                return $userInfo[$key] ?? null;
            }
            return $userInfo;
        });
    }

    public function after($response)
    {
        if (isset($this->other['token'])) {
            $this->setAuthenticationHeader($response, $this->other['token']);
        }
    }
}
