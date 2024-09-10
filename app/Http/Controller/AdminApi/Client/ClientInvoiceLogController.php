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
use App\Http\Service\Client\ClientInvoiceLogService;
use crmeb\traits\ResourceControllerTrait;
use Illuminate\Contracts\Container\BindingResolutionException;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Prefix;

/**
 * 客户合同发票操作记录
 * Class ClientInvoiceLogController.
 */
#[Prefix('ent/client/invoice')]
#[Middleware(['auth.admin', 'ent.auth', 'ent.log'])]
class ClientInvoiceLogController extends AuthController
{
    use ResourceControllerTrait;

    public function __construct(ClientInvoiceLogService $services)
    {
        parent::__construct();
        $this->service = $services;
    }

    /**
     * 展示数据.
     * @throws BindingResolutionException
     */
    #[Get('record/{id}', '开票操作记录')]
    public function list($invoiceId): mixed
    {
        $invoiceId = (int) $invoiceId;
        if (! $invoiceId) {
            return $this->fail('common.empty.attrs');
        }
        $where = [
            'invoice_id' => $invoiceId,
            'entid'      => $this->entId,
        ];
        return $this->success($this->service->getList($where));
    }

    /**
     * 搜索字段.
     * @return array|\string[][]
     */
    protected function getSearchField(): array
    {
        return [];
    }

    protected function getRequestClassName(): string
    {
        // TODO: Implement getRequestClassName() method.
    }

    protected function getRequestFields(): array
    {
        return [];
    }
}
