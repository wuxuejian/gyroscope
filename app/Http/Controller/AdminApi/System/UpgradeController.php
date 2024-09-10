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

namespace App\Http\Controller\AdminApi\System;

use App\Http\Controller\AdminApi\AuthController;
use App\Http\Service\System\UpgradeService;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Prefix;

/**
 * 在线升级
 * Class UpgradeController.
 */
#[Prefix('ent/system/upgrade')]
#[Middleware(['auth.admin', 'ent.auth', 'ent.log'])]
class UpgradeController extends AuthController
{
    public function __construct(UpgradeService $services)
    {
        parent::__construct();
        $this->service = $services;
    }

    /**
     * 获取升级状态
     */
    #[Get('status', '获取升级状态')]
    public function status(): mixed
    {
        $data = $this->service->getUpgradeStatus();
        return $this->success($data);
    }

    /**
     * 升级协议.
     */
    #[Get('agreement', '获取升级协议')]
    public function agreement(): mixed
    {
        return $this->success($this->service->getAgreement());
    }

    /**
     * 可升级列表.
     */
    #[Get('list', '获取可升级列表')]
    public function list(): mixed
    {
        return $this->success($this->service->getUpgradeList());
    }

    /**
     * 升级包数据.
     */
    #[Get('key', '获取可升级数据')]
    public function enableData(): mixed
    {
        $data = $this->service->getEnableData();
        if (! $data) {
            return $this->fail('暂无升级数据');
        }
        return $this->success($data);
    }

    /**
     * 升级.
     * @throws \Exception
     */
    #[Post('start/{package_key}', '开始升级')]
    public function start($packageKey): mixed
    {
        if (! $packageKey) {
            return $this->fail('参数错误');
        }

        $this->service->startUpgrade($packageKey);
        return $this->success();
    }

    /**
     * 升级进度.
     */
    #[Get('progress', '获取升级进度')]
    public function progress(): mixed
    {
        return $this->success($this->service->getProgress());
    }

    /**
     * 升级日志.
     */
    #[Get('log_list', '获取升级日志')]
    public function logList()
    {
        return $this->success($this->service->getLogList());
    }
}
