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

namespace App\Http\Service\Chat;

use App\Http\Dao\Chat\ChatAppAuthDao;
use App\Http\Service\BaseService;
use crmeb\traits\service\ResourceServiceTrait;

/**
 *  chat应用管理者.
 */
class ChatAppAuthService extends BaseService
{
    use ResourceServiceTrait;

    public function __construct(ChatAppAuthDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 权限报错.
     */
    public function save(int $id, array $data)
    {
        if (! empty($data)) {
            $authData = array_map(function ($authId) use ($id) {
                return ['user_id' => $authId, 'app_id' => $id];
            }, $data);
            $this->dao->insert($authData);
        }
    }

    /**
     *  清除权限.
     */
    public function clear(int $appId)
    {
        $this->dao->search(['app_id' => $appId])->delete();
    }
}
