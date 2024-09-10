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

use App\Http\Service\System\RolesService;
use crmeb\exceptions\AuthException;
use crmeb\interfaces\ApiMiddlewareInterface;
use crmeb\traits\MiddlewareTrait;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;

class AuthCrud extends BaseMiddleware implements ApiMiddlewareInterface
{
    use MiddlewareTrait;

    private array $actions = [
        'GET'    => 1, // read
        'POST'   => 2, // create
        'PUT'    => 3, // update
        'DELETE' => 4, // delete
    ];

    public function before(Request $request)
    {
        $crudName = $request->route('name', '');
        if (! $crudName) {
            throw new AuthException('缺少必要的实体参数');
        }
        $method = $request->method();
        if (! $request->hasMacro('uuId')) {
            throw new AuthException('您没有权限操作', 410005);
        }
        if (strtolower($method) === 'post' && $request->path() === ('api/ent/crud/module/' . $crudName . '/list')) {
            $method = 'GET';
        }
        $userId = uuid_to_uid($request->uuId());
        if (in_array($method, array_keys($this->actions))) {
            $request->merge([
                'system_user_id' => app()->get(RolesService::class)->getDataUids($userId, $crudName, $this->actions[$method]),
            ]);
        }
    }

    public function after($response)
    {
        // TODO: Implement after() method.
    }
}
