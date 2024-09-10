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

namespace App\Listeners\socket;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\JWTAuth;

/**
 * 企业后台事件
 * Class WebSocketEnt.
 */
class WebSocketEnt extends WebSocketChatBase
{
    /**
     * 企业用户登录.
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function login(array $data = [])
    {
        if (! isset($data['token'])) {
            return $this->fail('缺少token');
        }
        /** @var JWTAuth $jwtAuth */
        $jwtAuth = app()->get(JWTAuth::class);
        try {
            $user        = $jwtAuth->setToken($data['token'])->authenticate();
            $user->entid = 1;
        } catch (JWTException|TokenExpiredException|TokenInvalidException|UnauthorizedHttpException $e) {
            return $this->fail($e->getMessage());
        }
        if (! isset($user)) {
            return $this->fail('用户不存在');
        }
        if (! $user) {
            return $this->fail('无效用户');
        }
        return $this->success($user->makeHidden(['password', 'delete'])->toArray());
    }

    public function message(array $data = [])
    {
        return $this->success('你在说啥');
    }
}
