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

namespace App\Http\Service\Crud;

use App\Http\Dao\Crud\SystemCrudCommentDao;
use App\Http\Model\BaseModel;
use App\Http\Model\Crud\SystemCrud;
use App\Http\Service\BaseService;
use Illuminate\Database\Eloquent\Model;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * 评论.
 */
class SystemCrudCommentService extends BaseService
{
    public function __construct(SystemCrudCommentDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 创建评论.
     * @return BaseModel|Model
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function createComment(SystemCrud $systemCrud, array $data, int $id, int $uid)
    {
        $data['data_id'] = $id;
        $data['uid']     = $uid;
        $data['crud_id'] = $systemCrud->id;
        return $this->dao->create($data);
    }

    /**
     * 获取评论列表.
     * @return array|mixed
     */
    public function getCommentList(SystemCrud $systemCrud, int $id)
    {
        return $this->getList(where: ['crud_id' => $systemCrud->id, 'data_id' => $id, 'pid' => 0], sort: 'id', with: [
            'user'  => fn ($q) => $q->select(['name', 'id', 'avatar']),
            'reply' => fn ($q) => $q->with(['user' => fn ($q) => $q->select(['name', 'id', 'avatar'])]),
        ]);
    }
}
