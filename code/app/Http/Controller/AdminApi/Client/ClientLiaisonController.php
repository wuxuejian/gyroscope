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

namespace App\Http\Controller\AdminApi\Client;

use App\Http\Controller\AdminApi\AuthController;
use App\Http\Requests\enterprise\client\ClientLiaisonRequest;
use App\Http\Service\Client\ClientLiaisonService;
use crmeb\traits\ResourceControllerTrait;

/**
 * 联系人
 * Class ClientLiaisonController.
 */
class ClientLiaisonController extends AuthController
{
    use ResourceControllerTrait;

    /**
     * ClientLiaisonController constructor.
     */
    public function __construct(ClientLiaisonService $services)
    {
        parent::__construct();
        $this->service = $services;
    }

    /**
     * 搜索字段.
     * @return array|\string[][]
     */
    protected function getSearchField(): array
    {
        return [
            ['eid', 0],
            ['name', ''],
            ['job', ''],
            ['gender', ''],
            ['uid', $this->uuid],
        ];
    }

    /**
     * 设置.
     */
    protected function getRequestClassName(): string
    {
        return ClientLiaisonRequest::class;
    }

    /**
     * 提取字段.
     */
    protected function getRequestFields(): array
    {
        return [
            ['eid', 0],
            ['name', ''],
            ['job', ''],
            ['gender', ''],
            ['tel', ''],
            ['mail', ''],
            ['mark', ''],
            ['uid', $this->uuid],
            ['wechat', ''],
        ];
    }
}
