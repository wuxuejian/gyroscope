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
use App\Http\Requests\enterprise\client\ClientContractCategoryRequest;
use App\Http\Service\Client\ClientContractCategoryService;
use crmeb\traits\ResourceControllerTrait;
use Illuminate\Contracts\Container\BindingResolutionException;

/**
 * 客户合同分类
 * Class ClientContractCategoryController.
 */
class ClientContractCategoryController extends AuthController
{
    use ResourceControllerTrait;

    /**
     * ClientContractCategoryController constructor.
     */
    public function __construct(ClientContractCategoryService $services)
    {
        parent::__construct();
        $this->service = $services;
    }

    /**
     * 创建表单.
     */
    public function create()
    {
        return $this->success($this->service->resourceCreate(['pid' => $this->request->get('pid', 0)]));
    }

    /**
     * 展示数据.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function index(): mixed
    {
        $where = $this->request->getMore($this->getSearchField());
        return $this->success($this->service->getList($where, with: ['billCategory']));
    }

    /**
     * 下拉数据.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function select(): mixed
    {
        return $this->success($this->service->getSelectList());
    }

    protected function getRequestClassName(): string
    {
        return ClientContractCategoryRequest::class;
    }

    /**
     * 搜索字段.
     * @return array|\string[][]
     */
    protected function getSearchField(): array
    {
        return [
            ['name', '', 'name_like'],
            ['bill_cate_id', 0],
            ['bill_cate_name', ''],
        ];
    }

    /**
     * 提取字段.
     */
    protected function getRequestFields(): array
    {
        return [
            ['name', ''],
            ['bill_cate_path', []],
            ['sort', 0],
            ['entid', 1],
            ['path', []],
        ];
    }
}
