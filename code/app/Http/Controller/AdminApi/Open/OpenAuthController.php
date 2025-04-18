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

namespace App\Http\Controller\AdminApi\Open;

use App\Http\Controller\AdminApi\AuthController;
use App\Http\Service\Open\OpenApiKeyService;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Prefix;

/**
 * 对外接口授权
 */
#[Prefix('open/auth')]
class OpenAuthController extends AuthController
{
    public function __construct(OpenApiKeyService $service)
    {
        parent::__construct();
        $this->service = $service;
    }
    #[Post('login', '接口授权登录')]
    protected function login(): mixed
    {
        [$accessKey,$secretKey] = $this->request->postMore([
            ['access_key',''],
            ['secret_key',''],
        ], true);
        return $this->success($this->service->login($accessKey, $secretKey));
    }

}
