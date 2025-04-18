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

namespace App\Http\Service\Cloud;

use App\Constants\CloudEnum;
use App\Http\Dao\Cloud\CloudAuthDao;
use App\Http\Service\BaseService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 *  文件权限服务.
 */
class CloudAuthService extends BaseService
{
    public function __construct(CloudAuthDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 权限校验.
     * @return true|void
     * @throws BindingResolutionException
     */
    public function checkPermission(string $auth, int $uid, int $spaceId = 0, array $data = [])
    {
        if (! $this->dao->exists(['user_id' => $uid, 'folder_id' => $spaceId, $auth => 1])) {
            throw $this->exception('没有权限操作');
        }
        $ids = [];
        if (isset($data['id']) && $data['id'] && $data['id'] != $spaceId) {
            $ids = [$data['id']];
        } elseif (isset($data['ids']) && count($data['ids'])) {
            $ids = $data['ids'];
        }
        if ($ids) {
            if ($this->getFolderAuth($uid, $ids, $auth)) {
                return true;
            }
            if ($auth == CloudEnum::DELETE_AUTH) {
                $validIds = $this->validIds($ids, $uid);
                if (count($validIds) != count($ids)) {
                    throw $this->exception('没有权限操作');
                }
                return true;
            }
            throw $this->exception('没有权限操作');
        }
    }

    /**
     * 获取文件夹权限.
     * @param mixed $uid
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getFolderAuth($uid, array|int $fileId, string $auth = ''): array|bool
    {
        $ids     = is_array($fileId) ? $fileId : [$fileId];
        $folders = app()->get(CloudFileService::class)->column(['id' => $ids], ['id', 'path']);
        if (count($folders) != count($ids)) {
            return false;
        }
        if ($auth) {
            foreach ($folders as $folder) {
                $flag = $this->checkEntPathAuth($uid, $folder, $auth);
                if ($flag === false) {
                    return false;
                }
            }
            return true;
        }
        $arr = [];
        foreach ($folders as $folder) {
            $flag = $this->checkEntPathAuth($uid, $folder);
            if ($flag) {
                $arr[] = $flag;
            }
        }
        return $arr ? (is_array($fileId) ? $arr : $arr[0]) : $arr;
    }

    /**
     * 校验权限.
     * @param mixed $uid
     * @param mixed $folder
     * @param mixed $auth
     * @return null|bool|mixed
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    protected function checkEntPathAuth($uid, $folder, $auth = '')
    {
        $ids   = explode('/', trim($folder['path'], '/'));
        $ids[] = $folder['id'];
        $ids   = array_reverse($ids);
        $data  = $this->dao->column(['user_id' => $uid, 'folder_id' => $ids], ['user_id', 'folder_id', 'create', 'read', 'update', 'download', 'delete']);
        $auths = [];
        foreach ($data as $item) {
            $auths[$item['folder_id']] = $item;
        }
        foreach ($ids as $id) {
            if (isset($auths[$id])) {
                if ($auth) {
                    return (bool) $auths[$id][$auth];
                }
                return $auths[$id];
            }
        }
        return null;
    }
}
