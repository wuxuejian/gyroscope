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

namespace App\Http\Service\Cloud;

use App\Http\Dao\Cloud\CloudShareDao;
use App\Http\Service\Admin\AdminService;
use App\Http\Service\BaseService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 *  文件分享.
 */
class CloudShareService extends BaseService
{
    public function __construct(CloudShareDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 分享列表.
     * @return array
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function shareUserLst($where)
    {
        $list  = $this->dao->select($where, with: ['auth', 'user'])?->toArray();
        $count = count($list);
        return $this->listData($list, $count);
    }

    /**
     * 云盘分享.
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \Throwable
     */
    public function spaceShare(mixed $folder, array $rule, $name = null)
    {
        $count = app()->get(AdminService::class)->count(['status' => 1, 'uid' => array_column($rule, 'uid')]);
        if ($count != count($rule)) {
            throw $this->exception('请选择有效的员工');
        }
        $validator = ['download' => '下载', 'update' => '更新', 'create' => '创建', 'delete' => '删除'];
        foreach ($rule as $k => $item) {
            if (! $item['uid']) {
                throw $this->exception('请选择员工');
            }

            foreach ($item as $key => $val) {
                if (in_array($key, array_keys($validator)) && ! in_array($val, [0, 1])) {
                    throw $this->exception($validator[$key] . '权限有误');
                }
            }
            unset($rule[$k]['read']);
        }
        $this->transaction(function () use ($rule, $name, $folder) {
            if (! is_null($name)) {
                $folder->name = $name;
                $folder->saveOrFail();
            }
            $this->createShare($folder, $rule);
        });
    }

    /**
     * 删除分享.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function rmShare(int $folderId)
    {
        $this->dao->search(['folder_id' => $folderId])->delete();
        app()->get(CloudAuthService::class)->delete(['folder_id' => $folderId]);
    }

    /**
     * 获取目录权限.
     * @return array|mixed[]
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function getRules(int $fileId, int $uid, int $spaceId)
    {
        app()->get(CloudFileService::class)->checkAuth($spaceId, $uid, $fileId);
        $list = $this->dao->select(['folder_id' => $spaceId], with: ['user'])?->toArray();

        $data  = app()->get(CloudAuthService::class)->select(['folder_id' => $fileId])?->toArray();
        $auths = [];
        foreach ($data as $item) {
            $auths[$item['user_id']] = $item;
        }
        foreach ($list as $k => $item) {
            $list[$k]['auth'] = $auths[$item['user_id']] ?? null;
        }
        return $list;
    }

    /**
     * 设置目录权限.
     * @return mixed
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function setRules(int $fileId, int $uid, int $spaceId, array $rule = [])
    {
        app()->get(CloudFileService::class)->checkAuth($spaceId, $uid, $fileId);
        $admin   = app()->get(AdminService::class);
        $userIds = $admin->column(['status' => 1, 'uid' => array_column($rule, 'uid')], 'uid');
        return $this->transaction(function () use ($fileId, $rule, $userIds, $admin) {
            $now = now()->toDateTimeString();
            $this->dao->delete(['folder_id' => $fileId]);
            $auths = [];
            $make  = app()->get(CloudAuthService::class);
            foreach ($rule as $item) {
                if (in_array($item['uid'], $userIds)) {
                    $auths[] = [
                        'user_id'    => $admin->value(['uid' => $item['uid']], 'id'),
                        'uid'        => $item['uid'],
                        'folder_id'  => $fileId,
                        'create'     => $item['create'] ?? 0,
                        'read'       => $item['read'] ?? 0,
                        'update'     => $item['update'] ?? 0,
                        'download'   => $item['download'] ?? 0,
                        'delete'     => $item['delete'] ?? 0,
                        'created_at' => $now,
                    ];
                }
            }
            return $auths && $make->dao->insert($auths);
        });
    }

    /**
     * 创建分享.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    protected function createShare($folder, $rule)
    {
        $this->transaction(function () use ($folder, $rule) {
            $shares = [];
            $make   = app()->get(CloudAuthService::class);
            $admin  = app()->get(AdminService::class);
            $now    = now()->toDateTimeString();
            $this->rmShare($folder->id);
            $this->search(['folder_id' => $folder->id], true)->delete();
            foreach ($rule as $item) {
                $auth = $make->create([
                    'user_id'    => $item['value'] ?: $admin->value(['uid' => $item['uid']], 'id'),
                    'uid'        => $item['uid'],
                    'folder_id'  => $folder->id,
                    'create'     => $item['create'] ?? 0,
                    'read'       => $item['read'] ?? 1,
                    'update'     => $item['update'] ?? 0,
                    'download'   => $item['download'] ?? 0,
                    'delete'     => $item['delete'] ?? 0,
                    'created_at' => $now,
                ]);
                $shares[] = [
                    'auth_id'    => $auth->id,
                    'user_id'    => $item['value'] ?: $admin->value(['uid' => $item['uid']], 'id'),
                    'to_uid'     => $item['uid'],
                    'entid'      => $folder->entid,
                    'folder_id'  => $folder->id,
                    'created_at' => $now,
                ];
            }
            $folder->is_share = 1;
            $folder->saveOrFail();
            $this->dao->insert($shares);
        });
    }
}
