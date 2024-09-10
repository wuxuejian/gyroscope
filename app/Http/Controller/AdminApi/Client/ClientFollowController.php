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
use App\Http\Requests\enterprise\client\ClientFollowRequest;
use App\Http\Service\Client\ClientFollowService;
use crmeb\interfaces\ResourceControllerInterface;
use crmeb\traits\ResourceControllerTrait;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Prefix;
use Spatie\RouteAttributes\Attributes\Resource;

/**
 * 客户跟进记录
 * Class ClientFollowController.
 */
#[Prefix('ent/client/follow')]
#[Resource('/', false, except: ['create', 'show', 'edit'], names: [
    'index'   => '获取客户跟进列表',
    'store'   => '保存客户跟进接口',
    'update'  => '修改客户跟进接口',
    'destroy' => '删除客户跟进接口',
], parameters: ['' => 'id'])]
#[Middleware(['auth.admin', 'ent.auth', 'ent.log'])]
class ClientFollowController extends AuthController implements ResourceControllerInterface
{
    use ResourceControllerTrait;

    public function __construct(ClientFollowService $services)
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
            ['eid', ''],
            ['status', 0],
        ];
    }

    /**
     * 设置.
     */
    protected function getRequestClassName(): string
    {
        return ClientFollowRequest::class;
    }

    /**
     * 提取字段.
     */
    protected function getRequestFields(): array
    {
        return [
            ['eid', ''],
            ['content', ''],
            ['attach_ids', []],
            ['types', 0],
            ['time', ''],
            ['follow_id', 0],
        ];
    }
}
