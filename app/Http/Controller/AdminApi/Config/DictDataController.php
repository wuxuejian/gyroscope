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

namespace App\Http\Controller\AdminApi\Config;

use App\Http\Controller\AdminApi\AuthController;
use App\Http\Requests\system\DIctDataRequest;
use App\Http\Service\Config\DictDataService;
use crmeb\interfaces\ResourceControllerInterface;
use crmeb\traits\ResourceControllerTrait;
use Illuminate\Contracts\Container\BindingResolutionException;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Prefix;
use Spatie\RouteAttributes\Attributes\Resource;

/**
 * 数据字典-数据.
 */
#[Prefix('ent/config/dict_data')]
#[Resource('/', false, names: [
    'index'   => '获取字典数据列表接口',
    'create'  => '获取添加字典数据接口',
    'store'   => '添加字典保存数据接口',
    'show'    => '显示隐藏字典数据接口',
    'edit'    => '获取修改字典数据接口',
    'update'  => '修改字典数据保存接口',
    'destroy' => '删除字典数据接口',
], parameters: ['' => 'id'])]
#[Middleware(['auth.admin', 'ent.auth', 'ent.log'])]
class DictDataController extends AuthController implements ResourceControllerInterface
{
    use ResourceControllerTrait;

    public function __construct(DictDataService $service)
    {
        $this->service = $service;
        parent::__construct();
    }

    /**
     * @return mixed
     * @throws \ReflectionException
     * @throws BindingResolutionException
     */
    #[Post('tree', '获取字典数据树形结构')]
    public function tree()
    {
        $where = $this->request->postMore($this->getSearchField());
        return $this->success($this->service->getTreeData($where));
    }

    protected function getRequestClassName(): string
    {
        return DIctDataRequest::class;
    }

    protected function getSearchField(): array
    {
        return [
            ['name', '', 'name_like'],
            ['types', '', 'type_name'],
            ['type_id', ''],
            ['level', ''],
            ['pid', ''],
            ['status', ''],
            ['isCityShow', ''],
        ];
    }

    protected function getRequestFields(): array
    {
        return [
            ['name', ''],
            ['value', ''],
            ['type_id', 0],
            ['pid', ''],
            ['sort', 0],
            ['status', 1],
            ['mark', ''],
        ];
    }
}
