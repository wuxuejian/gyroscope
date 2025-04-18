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

namespace App\Http\Middleware;

use App\Http\Service\Crud\SystemCrudQuestionnaireService;
use crmeb\exceptions\AdminException;
use crmeb\interfaces\ApiMiddlewareInterface;
use crmeb\traits\MiddlewareTrait;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;

/**
 * 后台授权登录
 * Class AuthAdmin.
 */
class AuthAdmin implements ApiMiddlewareInterface
{
    use MiddlewareTrait;

    /**
     * @var null|\Closure|mixed|object
     */
    private mixed $token = '';

    /**
     * 前置事件.
     */
    public function before(Request $request)
    {
        try {
            $admin = auth('admin')->userOrFail();
            if ((int) bcsub((string) auth('admin')->getPayload()->get('exp'), (string) time()) < 21600) {
                $this->token = auth('admin')->refresh();
            }
        } catch (TokenExpiredException) {
            $this->token = auth('admin')->refresh();
        } catch (\Exception) {
            $userInfo = app()->make(SystemCrudQuestionnaireService::class)->checkUniqueisLogin($request);
            if (is_bool($userInfo)) {
                throw new AdminException('Login expired, please login again', 410003);
            }
            $admin = $userInfo;
        }

        if (isset($admin)) {
            $request->macro('admin', $admin);
            $request->macro('userInfo', function (?string $key = null) use ($admin) {
                $admin = $admin->toArray();
                if ($key) {
                    return $admin[$key] ?? null;
                }
                return $admin;
            });
            $request->macro('uuId', function () use ($admin) {
                return $admin->uid;
            });
        }
    }

    /**
     * 后置事件.
     * @param mixed $response
     * @return mixed|void
     */
    public function after($response)
    {
        $this->request = null;
        if ($this->token) {
            $response->headers->set('Authorization', 'Bearer ' . $this->token);
            return $response;
        }
    }
}
