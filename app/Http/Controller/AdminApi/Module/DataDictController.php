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

namespace App\Http\Controller\AdminApi\Module;

use App\Http\Controller\AdminApi\AuthController;
use App\Http\Service\Crud\DataDictService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Prefix;

/**
 * Class DataDictController.
 * @email 136327134@qq.com
 * @date 2024/3/6
 */
#[Prefix('ent/crud')]
#[Middleware(['auth.admin', 'ent.auth', 'ent.log'])]
class DataDictController extends AuthController
{
    /**
     * DataDict constructor.
     */
    public function __construct(DataDictService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    /**
     * 获取字典列表.
     * @return mixed
     * @throws BindingResolutionException
     * @throws \ReflectionException
     * @email 136327134@qq.com
     * @date 2024/2/29
     */
    #[Get('data/list', '数据字典列表')]
    public function getList()
    {
        $where = $this->request->getMore([
            ['level', ''],
            ['name', ''],
        ]);

        return $this->success($this->service->getDataDicList($where));
    }
}
