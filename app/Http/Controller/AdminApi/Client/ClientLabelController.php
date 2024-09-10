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
use App\Http\Requests\enterprise\client\ClientLabelRequest;
use App\Http\Service\Client\ClientLabelService;
use crmeb\interfaces\ResourceControllerInterface;
use crmeb\traits\ResourceControllerTrait;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Prefix;
use Spatie\RouteAttributes\Attributes\Resource;

/**
 * 客户标签
 * Class ClientLabelController.
 */
#[Prefix('ent/client/labels')]
#[Resource('/', false, except: ['show', 'create', 'edit'], names: [
    'index'   => '获取客户标签列表接口',
    'store'   => '保存客户标签接口',
    'update'  => '修改客户标签接口',
    'destroy' => '删除客户标签接口',
], parameters: ['' => 'id'])]
#[Middleware(['auth.admin', 'ent.auth', 'ent.log'])]
class ClientLabelController extends AuthController implements ResourceControllerInterface
{
    use ResourceControllerTrait;

    public function __construct(ClientLabelService $services)
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
            ['name', '', 'name_like'],
            ['pid', 0],
        ];
    }

    protected function getRequestClassName(): string
    {
        return ClientLabelRequest::class;
    }

    /**
     * 提取字段.
     */
    protected function getRequestFields(): array
    {
        return [
            ['name', ''],
            ['pid', 0],
            ['sort', 0],
            ['entid', 1],
        ];
    }
}
