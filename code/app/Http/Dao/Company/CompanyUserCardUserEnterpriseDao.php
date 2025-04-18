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

namespace App\Http\Dao\Company;

use App\Http\Dao\BaseDao;
use App\Http\Model\BaseModel;
use App\Http\Model\Company\UserCard;
use App\Http\Model\User\UserEnterprise;
use crmeb\traits\dao\JoinSearchTrait;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Concerns\BuildsQueries;

/**
 * Class CompanyUserCardUserEnterpriseDao.
 */
class CompanyUserCardUserEnterpriseDao extends BaseDao
{
    use JoinSearchTrait;

    /**
     * 允许搜索的字段名单.
     *
     * @var string[]
     */
    protected $whiteSearchField = ['name', 'phone', 'id'];

    /**
     * 设置模型.
     *
     * @return BaseModel
     * @throws BindingResolutionException
     */
    public function getModel(bool $need = true)
    {
        if ($need) {
            return $this->getJoinModel('id', 'card_id', '=', 'left')->where([
                $this->getFiled('verify', $this->aliasB) => 1,
            ]);
        }
        return parent::getModel($need);
    }

    /**
     * 搜索.
     *
     * @param array|int|string $where
     *
     * @return BaseModel|BuildsQueries|mixed
     * @throws BindingResolutionException
     */
    public function search($where, ?bool $authWhere = null)
    {
        if ($authWhere === null) {
            $authWhere = $this->authWhere;
        }

        if (! empty($where['types']) && in_array(4, $where['types'])) {
            $fieldKey = 'quit_time';
        } else {
            $fieldKey = 'work_time';
        }
        return $this->setTimeField($this->getFiled($fieldKey, 'enterprise_user_card'))->getModel()->when($authWhere, function ($query) {
            $query->where($this->getDefaultWhereValue());
        })->when(isset($where['entid']), function ($query) use ($where) {
            $query->where($this->getFiled('entid', $this->aliasB), $where['entid'])
                ->where($this->getFiled('entid'), $where['entid']);
        })->when(isset($where['frame_id']) && $where['frame_id'], function ($query) use ($where) {
            $query->where(function ($query) use ($where) {
                $query->whereIn($this->getFiled('id', $this->aliasB), function ($q) use ($where) {
                    $q->from('frame_assist')->whereIn('frame_id', function ($query) use ($where) {
                        $query->from('frame')->when(isset($where['entid']), function ($query) use ($where) {
                            $query->where('entid', $where['entid']);
                        })->where('path', 'like', '%/' . $where['frame_id'] . '/%')->select('id');
                    })->select('user_id');
                })->orWhereIn($this->getFiled('id', $this->aliasB), function ($query) use ($where) {
                    $query->from('frame_assist')->where('frame_id', $where['frame_id'])->select('user_id');
                });
            });
        })->when(isset($where['search']) && $where['search'], function ($query) use ($where) {
            if (isset($where['field']) && $where['field'] && in_array($where['field'], $this->whiteSearchField)) {
                $query->where($this->getFiled($where['field']), $where['search']);
            } else {
                $query->where(function ($query) use ($where) {
                    $query->where($this->getFiled('name'), 'like', '%' . $where['search'] . '%')
                        ->orWhere($this->getFiled('phone'), 'like', '%' . $where['search'] . '%')
                        ->orWhere($this->getFiled('id'), 'like', '%' . $where['search'] . '%');
                });
            }
        })->when(isset($where['sex']) && $where['sex'] !== '', function ($query) use ($where) {
            $query->where($this->getFiled('sex'), $where['sex']);
        })->when(isset($where['status']) && $where['status'] !== '', function ($query) use ($where) {
            $query->where($this->getFiled('status'), $where['status']);
        })->when(isset($where['statu']) && $where['statu'] !== '', function ($query) use ($where) {
            $query->whereIn($this->getFiled('status'), $where['statu']);
        })->when(isset($where['type']) && $where['type'] !== '', function ($query) use ($where) {
            $query->where($this->getFiled('type'), $where['type']);
        })->when(isset($where['is_part']) && $where['is_part'] !== '', function ($query) use ($where) {
            $query->where($this->getFiled('is_part'), $where['is_part']);
        })->when(! empty($where['types']), function ($query) use ($where) {
            $query->whereIn($this->getFiled('type'), $where['types']);
        })->when(! empty($where['education']) && $where['education'] !== '', function ($query) use ($where) {
            $query->where($this->getFiled('education'), $where['education']);
        })->when(! empty($where['uid']) && $where['uid'] !== '', function ($query) use ($where) {
            if (is_bool($where['uid'])) {
                return $where['uid'] ? $query->where($this->getFiled('uid'), '!=', '') : $query->where($this->getFiled('uid'), '');
            }
        })->when(isset($where['uid_not_empty']) && $where['uid_not_empty'], function ($query) {
            $query->where($this->getFiled('uid'), '<>', '');
        })->when(isset($where['job']) && $where['job'] !== '', function ($query) use ($where) {
            $query->where($this->getFiled('position'), $where['job']);
        })->time(isset($where['time']) ? $where['time'] : '');
    }

    /**
     * 查询数据.
     *
     * @return array
     * @throws BindingResolutionException
     */
    public function getList(array $where, int $page = 0, int $limit = 0, array $with = [], array $sort = ['enterprise_user_card.sort', 'enterprise_user_card.id'])
    {
        $joinTable = 'frame_assist';
        $field     = ['enterprise_user_card.*', 'user_enterprise.id as user_id'];

        return $this->search($where)->when(in_array('frame_assist.is_admin', $sort), function ($query) use ($joinTable, $where) {
            $query->leftJoin($joinTable, function ($query) use ($joinTable, $where) {
                $query->on($joinTable . '.user_id', '=', 'user_enterprise.id')
                    ->where($joinTable . '.frame_id', $where['frame_id'] ?? 0)->where($joinTable . '.is_admin', 1);
            });
        })->select($field)->when($page && $limit, function ($query) use ($page, $limit) {
            $query->forPage($page, $limit);
        })->when($limit, function ($query) use ($limit) {
            $query->limit($limit);
        })->when($sort, function ($query) use ($sort) {
            foreach ($sort as $item) {
                $query->orderByDesc($item);
            }
        })->with($with)->get()->toArray();
    }

    /**
     * @return mixed|string
     */
    protected function setModel()
    {
        return UserCard::class;
    }

    protected function setModelB(): string
    {
        return UserEnterprise::class;
    }
}
