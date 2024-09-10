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

use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\JWTAuth;

/**
 * 后台事件处理
 * Class WebSocketAdmin.
 */
class WebSocketAdmin extends WebSocketChatBase
{
    /**
     * 登录.
     * @return mixed|string
     */
    public function login(array $data = [])
    {
        if (! isset($data['token']) || ! ($token = $data['token'])) {
            return $this->fail('缺少token');
        }
        /** @var JWTAuth $jwtAuth */
        $jwtAuth = app()->get(JWTAuth::class);
        app('tymon.jwt.provider.auth')->setProvider(Auth::createUserProvider('admins'));
        try {
            $adminInfo = $jwtAuth->setToken($token)->authenticate();
        } catch (\Throwable $e) {
            return $this->fail($e->getMessage());
        }

        if (! isset($adminInfo)) {
            return $this->fail('管理员不存在');
        }
        if (! $adminInfo) {
            return $this->fail('无效管理员');
        }

        return $this->success($adminInfo->makeHidden(['password', 'remember_token', 'is_del'])->toArray());
    }
}
