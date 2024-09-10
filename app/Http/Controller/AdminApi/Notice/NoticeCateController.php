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

namespace App\Http\Controller\AdminApi\Notice;

use App\Http\Controller\AdminApi\AuthController;
use App\Http\Service\Message\MessageService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * 消息控制器
 * Class Message.
 */
class NoticeCateController extends AuthController
{
    /**
     * Message constructor.
     */
    public function __construct(MessageService $services)
    {
        parent::__construct();
        $this->service = $services;
    }

    /**
     * 分类.
     * @return mixed
     * @throws BindingResolutionException
     * @throws InvalidArgumentException
     */
    public function cate()
    {
        return $this->success($this->service->getMessageCateList($this->entId));
    }

    public function syncMessage()
    {
        $this->service->syncMessage($this->entId);
        return $this->success('ok');
    }
}
