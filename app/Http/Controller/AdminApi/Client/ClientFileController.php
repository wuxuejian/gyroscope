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
use App\Http\Service\Attach\AttachService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Spatie\RouteAttributes\Attributes\Delete;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Prefix;
use Spatie\RouteAttributes\Attributes\Put;

/**
 * 客户文件
 * Class ClientFileController.
 */
#[Prefix('ent/client/file')]
#[Middleware(['auth.admin', 'ent.auth', 'ent.log'])]
class ClientFileController extends AuthController
{
    public function __construct(AttachService $services)
    {
        parent::__construct();
        $this->service = $services;
    }

    /**
     * 显示列表.
     * @return mixed
     * @throws \ReflectionException
     * @throws BindingResolutionException
     */
    #[Get('index', '客户文件列表')]
    public function index()
    {
        $where = $this->request->getMore([
            ['eid', ''],
            ['entid', 1],
        ]);
        $where['relation_type'] = [2, 3, 4, 5, 6];
        return $this->success($this->service->getRelationList($where, sort: 'id'));
    }

    /**
     * 删除指定资源.
     * @return mixed
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    #[Delete('delete/{id}', '删除客户文件')]
    public function delete($id)
    {
        if (! $id) {
            return $this->fail(__('common.empty.attrs'));
        }
        $this->service->delImg([$id], $this->entId);
        return $this->success('common.delete.succ');
    }

    /**
     * 上传.
     * @return mixed
     */
    #[Post('upload', '上传客户文件')]
    public function upload()
    {
        [$cid, $eid, $fid, $file] = $this->request->postMore([
            ['cid', 0],
            ['eid', 0],
            ['fid', 0],
            ['file', 'file'],
        ], true);
        $attach_type = 1;
        $res         = $this->service->upload((int) $cid, $eid, $fid, $file, $attach_type);
        return $this->success('common.upload.succ', $res);
    }

    /**
     * 重命名.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    #[Put('real_name/{id}', '客户文件重命名')]
    public function setRealName($id): mixed
    {
        if (! $id) {
            return $this->fail(__('common.empty.attrs'));
        }
        [$realName] = $this->request->postMore([
            ['real_name', ''],
        ], true);
        $this->service->setRealName((int) $id, $this->entId, $realName);
        return $this->success('common.operation.succ');
    }
}
