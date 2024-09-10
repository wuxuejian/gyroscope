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

namespace App\Http\Controller\AdminApi\Assess;

use App\Http\Controller\AdminApi\AuthController;
use App\Http\Service\Config\AssessConfigService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Prefix;

/**
 * 绩效配置.
 */
#[Prefix('ent/assess')]
#[Middleware(['auth.admin', 'ent.auth', 'ent.log'])]
class ConfigController extends AuthController
{
    public function __construct(AssessConfigService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    /**
     * 获取积分配置及说明.
     * @return mixed
     * @throws BindingResolutionException
     */
    #[Get('score', '获取积分配置及说明')]
    public function getScore()
    {
        return $this->success($this->service->getScoreConfig());
    }

    /**
     * 保存积分配置及说明.
     * @return mixed
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    #[Post('score', '保存积分配置及说明')]
    public function saveScore()
    {
        $data = $this->request->postMore([
            ['score_mark', ''],
            ['compute_mode', 0],
            ['score_list', []],
        ]);
        $this->service->saveScoreConfig($data);
        return $this->success('保存成功');
    }

    /**
     * 获取审核配置及人员.
     * @return mixed
     * @throws BindingResolutionException
     */
    #[Get('verify', '获取审核配置及人员')]
    public function getVerify()
    {
        return $this->success($this->service->getVerifyConfig());
    }
}
