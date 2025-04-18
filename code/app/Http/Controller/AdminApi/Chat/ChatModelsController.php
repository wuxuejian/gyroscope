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
use App\Http\Requests\enterprise\client\ChatMoldelsRequest;
use App\Http\Service\Chat\ChatModelsService;
use crmeb\interfaces\ResourceControllerInterface;
use crmeb\services\ai\BaidubceOption;
use crmeb\services\ai\DeepseekOption;
use crmeb\traits\ResourceControllerTrait;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Prefix;
use Spatie\RouteAttributes\Attributes\Resource;

/**
 * Chat模型
 * Class ClientBillController.
 */
#[Prefix('ent/chat/models')]
#[Resource('/', false, except: ['create', 'show'], names: [
    'index'   => '获取模型列表',
    'store'   => '保存模型接口',
    'edit'    => '获取模型接口',
    'update'  => '修改模型接口',
    'destroy' => '删除模型接口',
], parameters: ['' => 'id'])]
#[Middleware(['auth.admin', 'ent.auth', 'ent.log'])]
class ChatModelsController extends AuthController implements ResourceControllerInterface
{
    use ResourceControllerTrait;

    public function __construct(ChatModelsService $services)
    {
        parent::__construct();
        $this->service = $services;
    }

    #[Get('list', '获取模型下拉')]
    public function list()
    {
        $data = $this->service->list();
        return $this->success($data);
    }

    #[Get('options', '获取模型供应商')]
    public function options()
    {
        $data = [
            [
                'label' => 'Deepseek',
                'pic'   => 'deepseek.png',
                'value' => 0,
            ],
            [
                'label' => '百度千帆',
                'value' => 1,
                'pic'   => 'qianfan.png',
            ],
        ];
        return $this->success($data);
    }

    #[Get('select', '获取模型类型')]
    public function select()
    {
        $type = $this->request->get('type', 0);
        if ($type) {
            $options = BaidubceOption::MODEL_OPTIONS;
        } else {
            $options = DeepseekOption::MODEL_OPTIONS;
        }
        $data = array_keys($options);
        $arr  = array_map(function ($k) {
            return ['label' => $k, 'value' => $k];
        }, $data);
        return $this->success([['label' => 'LLM(大语言模型)', 'value' => 'LLM', 'children' => $arr]]);
    }

    /**
     * 搜索字段.
     * @return array|string[][]
     */
    protected function getSearchField(): array
    {
        return [
            ['provider', ''],
            ['uids', ''],
            ['name', '', 'name_like'],
        ];
    }

    /**
     * 设置.
     */
    protected function getRequestClassName(): string
    {
        return ChatMoldelsRequest::class;
    }

    /**
     * 提取字段.
     */
    protected function getRequestFields(): array
    {
        return [
            ['name', ''],
            ['models_type', ''],
            ['is_model', ''],
            ['pic', ''],
            ['url', ''],
            ['key', ''],
            ['uid', auth('admin')->id()],
            ['json'],
            ['provider', 0],
        ];
    }
}
