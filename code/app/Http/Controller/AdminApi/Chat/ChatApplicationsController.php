<?php

declare(strict_types=1);
/**
 *  +----------------------------------------------------------------------
 *  | 陀螺匠 [ 赋能开发者，助力企业发展 ]
 *  +----------------------------------------------------------------------
 *  | Copyright (c) 2016~2025 https://www.tuoluojiang.com All rights reserved.
 *  +----------------------------------------------------------------------
 *  | Licensed 陀螺匠并不是自由软件，未经许可不能去掉陀螺匠相关版权
 *  +----------------------------------------------------------------------
 *  | Author: 陀螺匠 Team <admin@tuoluojiang.com>
 *  +----------------------------------------------------------------------
 */

namespace App\Http\Controller\AdminApi\Chat;

use App\Http\Controller\AdminApi\AuthController;
use App\Http\Requests\enterprise\client\ChatApplicationsRequest;
use App\Http\Service\Chat\ChatApplicationsService;
use App\Http\Service\Crud\SystemCrudService;
use crmeb\interfaces\ResourceControllerInterface;
use crmeb\traits\ResourceControllerTrait;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Prefix;
use Spatie\RouteAttributes\Attributes\Resource;

/**
 * chat应用管理
 * Class ChatApplicationsController.
 */
#[Prefix('ent/chat/applications')]
#[Resource('/', false, except: ['create'], names: [
    'index'   => '获取模型列表',
    'store'   => '保存模型接口',
    'show'    => '修改状态接口',
    'edit'    => '获取模型接口',
    'update'  => '修改模型接口',
    'destroy' => '删除模型接口',
], parameters: ['' => 'id'])]
#[Middleware(['auth.admin', 'ent.auth', 'ent.log'])]
class ChatApplicationsController extends AuthController implements ResourceControllerInterface
{
    use ResourceControllerTrait;

    public function __construct(ChatApplicationsService $services)
    {
        parent::__construct();
        $this->service = $services;
    }

    #[Get('databes/list', '数据库列表')]
    public function databes(SystemCrudService $service)
    {
        $where = $this->request->getMore([['keyword', '']]);
        $list  = $service->getCrudTableFieldList($where);
        return $this->success($list);
    }

    /**
     * 数据库提示文本.
     * @return mixed
     */
    #[Post('database/tooltip', '数据库提示文本')]
    public function databaseTooltipText()
    {
        $tables = $this->request->post('tables', []);
        if (! $tables) {
            return $this->fail('请选择表');
        }

        return $this->success(['tooltip_text' => $this->service->getDatabaseTooltipText((array) $tables)]);
    }

    /**
     * 搜索字段.
     * @return array|string[][]
     */
    protected function getSearchField(): array
    {
        return [
            ['id', ''],
            ['name', ''],
            ['status', ''],
            ['uids', ''],
        ];
    }

    /**
     * 设置.
     */
    protected function getRequestClassName(): string
    {
        return ChatApplicationsRequest::class;
    }

    /**
     * 提取字段.
     */
    protected function getRequestFields(): array
    {
        return [
            ['name', ''],
            ['info', ''],
            ['pic', ''],
            ['edit', []],
            ['status', 1],
            ['auth_ids', []],
            ['use_limit', 0],
            ['models_id', 0],
            ['sort', 0],
            ['tables', []],
            ['is_table', 0],
            ['count_number', 0],
            ['content', ''],
            ['tooltip_text', ''],
            ['prologue_text', ''],
            ['prologue_list', ''],
            ['data_arrange_text', ''],
            ['json', ''],
            ['keyword', ''],
            ['uid', auth('admin')->id()],
        ];
    }
}
