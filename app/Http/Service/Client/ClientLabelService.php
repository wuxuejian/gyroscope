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

namespace App\Http\Service\Client;

use App\Constants\CacheEnum;
use App\Http\Dao\Client\ClientLabelDao;
use App\Http\Model\BaseModel;
use App\Http\Service\BaseService;
use crmeb\traits\service\ResourceServiceTrait;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * 客户标签
 * Class ClientLabelService.
 */
class ClientLabelService extends BaseService
{
    use ResourceServiceTrait;

    public $dao;

    public function __construct(ClientLabelDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取列表.
     * @param array|string[] $field
     * @param string $sort
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function getList(array $where, array $field = ['id', 'name', 'sort', 'pid'], $sort = 'sort', array $with = []): array
    {
        [$page, $limit] = $this->getPageValue();
        return Cache::tags([CacheEnum::TAG_DICT])->remember(md5(json_encode($where) . $page . $limit), 360, function () use ($where, $field, $page, $limit, $sort, $with) {
            $list = $this->dao->getList($where, $field, $page, $limit, $sort, $with + [
                'children' => function ($query) {
                    $query->select(['id', 'pid', 'name', 'sort']);
                },
            ]);
            $count = $this->dao->count($where);
            return $this->listData($list, $count);
        });
    }

    /**
     * 保存数据.
     * @return BaseModel|mixed|Model
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function resourceSave(array $data)
    {
        if (! $data['pid']) {
            $data['pid'] = 0;
            if ($this->dao->exists(['name' => $data['name'], 'entid' => $data['entid'], 'pid' => 0])) {
                throw $this->exception('已存在相同数据，请勿重复添加');
            }
        } else {
            if ($this->dao->exists(['name' => $data['name'], 'entid' => $data['entid'], 'not_pid' => 0])) {
                throw $this->exception('已存在相同数据，请勿重复添加');
            }
        }
        $res = $this->dao->create($data);
        $res && Cache::tags([CacheEnum::TAG_DICT])->flush();
        return $res;
    }

    /**
     * 修改数据.
     * @param int $id
     * @return true
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function resourceUpdate($id, array $data)
    {
        if (! $data['pid']) {
            $data['pid'] = 0;
        }
        $this->dao->update($id, $data) && Cache::tags([CacheEnum::TAG_DICT])->flush();
        return true;
    }

    /**
     * 删除客户标签.
     * @param mixed $id
     * @return int|mixed
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function resourceDelete($id, ?string $key = null)
    {
        $childId = $this->dao->column(['pid' => $id]) ?? [];
        $res     = $this->transaction(function () use ($id, $childId, $key) {
            if ($childId) {
                $this->dao->delete(['id' => $childId], $key);
            }
            return $this->dao->delete($id, $key);
        });
        Cache::tags([CacheEnum::TAG_DICT])->flush();
        return $res;
    }

    /**
     * 根据名称顺序获取id.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getIdsByName(array $name): array
    {
        $ids  = [];
        $list = $this->dao->column(['name_eq' => $name], 'id', 'name');
        foreach ($name as $tmpValue) {
            if (isset($list[$tmpValue])) {
                $ids[] = $list[$tmpValue];
            }
        }
        return $ids;
    }
}
