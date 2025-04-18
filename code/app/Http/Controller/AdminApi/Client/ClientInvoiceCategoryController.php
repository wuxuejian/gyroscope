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
use App\Http\Requests\enterprise\client\ClientInvoiceCategoryRequest;
use App\Http\Service\Client\ClientInvoiceCategoryService;
use crmeb\traits\ResourceControllerTrait;

/**
 * 客户合同发票类目
 * Class ClientInvoiceCategoryController.
 */
class ClientInvoiceCategoryController extends AuthController
{
    use ResourceControllerTrait;

    public function __construct(ClientInvoiceCategoryService $services)
    {
        parent::__construct();
        $this->service = $services;
    }

    /**
     * 搜索字段.
     * @return \string[][]
     */
    protected function getSearchField(): array
    {
        return [
            ['name', '', 'name_like'],
        ];
    }

    protected function getRequestClassName(): string
    {
        return ClientInvoiceCategoryRequest::class;
    }

    /**
     * 搜索字段.
     */
    protected function getRequestFields(): array
    {
        return [
            ['name', ''],
            ['sort', 0],
            ['entid', 1],
        ];
    }
}
