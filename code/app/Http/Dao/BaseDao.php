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

namespace App\Http\Dao;

use App\Http\Model\BaseModel;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Concerns\BuildsQueries;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * 基础Dao
 * Class BaseDao.
 */
abstract class BaseDao
{
    /**
     * 表名.
     * @var string
     */
    protected $table;

    /**
     * 局部作用域没有自动进入where搜索.
     * @var bool
     */
    protected $authWhere = true;

    /**
     * 默认条件.
     * @var array
     */
    protected $defaultWhere = [];

    /**
     * 时间作用域字段.
     * @var string
     */
    protected $timeField;

    /**
     * 是否查询软删除.
     * @var bool
     */
    protected $trashed = false;

    /**
     * 仅查询软删除.
     * @var bool
     */
    protected $onlyTrashed = false;

    /**
     * 默认排序.
     * @var string
     */
    protected $defaultSort = '';

    /**
     * 设置默认查询条件.
     * @return $this
     */
    public function setDefaultWhere(array $where)
    {
        $this->defaultWhere = $where;
        return $this;
    }

    /**
     * 设置默认排序.
     * @param array|string $sort
     * @return $this
     */
    public function setDefaultSort($sort)
    {
        $this->defaultSort = $sort;
        return $this;
    }

    /**
     * 设置查询软删除.
     * @return $this
     */
    public function setTrashed()
    {
        $this->trashed = true;
        return $this;
    }

    /**
     * 设置查询软删除.
     * @return $this
     */
    public function setOnlyTrashed()
    {
        $this->onlyTrashed = true;
        return $this;
    }

    /**
     * 设置自动进入where搜索开关.
     * @return $this
     */
    public function authWhere(bool $authWhere)
    {
        $this->authWhere = $authWhere;
        return $this;
    }

    /**
     * 时间段搜索字段.
     * @return $this
     */
    public function setTimeField(string $timeField)
    {
        $this->timeField = $timeField;
        return $this;
    }

    /**
     * 获取模型.
     * @return BaseModel
     * @throws BindingResolutionException
     */
    public function getModel(bool $need = true)
    {
        /** @var BaseModel $model */
        $model = app()->get($this->setModel());
        // 时间字段
        if ($this->timeField) {
            $model->setTimeField($this->timeField);
        }
        // 默认条件
        if ($this->defaultWhere && $need) {
            $model = $model->where($this->getDefaultWhereValue());
        }

        return $model;
    }

    /**
     * 获取表名.
     * @return string
     * @throws BindingResolutionException
     */
    public function getTable()
    {
        return $this->getModel(false)->getTable();
    }

    /**
     * 搜索.
     * @param array|int|string $where where条件
     * @param bool $authWhere 不再局部作用域之内的自动进入where条件
     * @return BaseModel
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function search($where, ?bool $authWhere = null)
    {
        if (is_array($where)) {
            $where = array_filter($where, function ($v) {
                return ! (is_null($v) || $v === '');
            });
        }
        $where = $this->handleWhere($where);

        [$with, $newWhere] = $this->getScope($where, $this->setModel());

        $where = collect($where)->only($with)->all();

        $model = $this->withSearch($this->getModel(false), $with, $where);

        if ($authWhere === null) {
            $authWhere = $this->authWhere;
        }

        if ($authWhere && $newWhere) {
            $model = $model->where($newWhere);
        }

        if ($this->defaultWhere) {
            $model = $model->where($this->getDefaultWhereValue());
        }
        if ($this->trashed) {
            $model         = $model->withTrashed();
            $this->trashed = false;
        }
        if ($this->onlyTrashed) {
            $model             = $model->onlyTrashed();
            $this->onlyTrashed = false;
        }
        return $model;
    }

    /**
     * 获取一条数据.
     * @param null|mixed $sort
     * @param mixed $where
     * @return null|BaseModel|BuildsQueries|mixed|Model|object
     * @throws \ReflectionException
     * @throws BindingResolutionException
     */
    public function get($where, array $field = [], array $with = [], $sort = null)
    {
        return $this->search($where)->when(count($with), function ($query) use ($with) {
            $query->with($with);
        })->when($sort, function ($query) use ($sort) {
            if (is_array($sort)) {
                foreach ($sort as $k => $v) {
                    if (is_numeric($k)) {
                        $query->orderByDesc($v);
                    } else {
                        $query->orderBy($k, $v);
                    }
                }
            } else {
                $query->orderByDesc($sort);
            }
        })->select($field ?: '*')->first();
    }

    /**
     * 获取多条数据.
     * @param mixed $where
     * @return Collection
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function select($where = [], array $field = [], array $with = [], int $page = 0, int $limit = 0)
    {
        return $this->search($where)->when(count($with), function ($query) use ($with) {
            $query->with($with);
        })->when($this->defaultSort, function ($query) {
            if (is_array($this->defaultSort)) {
                foreach ($this->defaultSort as $k => $v) {
                    if (is_numeric($k)) {
                        $query->orderByDesc($v);
                    } else {
                        $query->orderBy($k, $v);
                    }
                }
            } else {
                $query->orderByDesc($this->defaultSort);
            }
            $this->defaultSort = '';
        })->when($page && $limit, function ($query) use ($page, $limit) {
            $query->forPage($page, $limit);
        })->select($field ?: '*')->get();
    }

    /**
     * 获取条数.
     * @param array $where
     * @return int
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function count($where = [], array $with = [])
    {
        return $this->search($where)->when(count($with), function ($query) use ($with) {
            $query->with($with);
        })->count();
    }

    /**
     * 创建数据.
     * @return BaseModel|Model
     * @throws BindingResolutionException
     */
    public function create(array $data)
    {
        return $this->getModel(false)->create($data);
    }

    /**
     * 更新.
     * @param mixed $where
     * @return int
     * @throws BindingResolutionException
     */
    public function update($where, array $data)
    {
        $model = $this->getModel(false);

        foreach ($data as $key => $value) {
            $model->setAttribute($key, $value);
        }

        $data = $model->getAttributes();

        return $this->search($where)->update($data);
    }

    /**
     * 无记录则新增.
     * @return BaseModel|bool|Model
     * @throws BindingResolutionException
     */
    public function firstOrCreate(array $where, array $data)
    {
        return $this->getModel(false)->firstOrCreate($where, $data);
    }

    /**
     * 更新或新增.
     * @return bool
     * @throws BindingResolutionException
     */
    public function updateOrCreate(array $where, array $data)
    {
        return $this->getModel(false)->updateOrInsert($where, $data);
    }

    /**
     * 获取集合.
     * @param null|string $key
     * @param null|array|string $field
     * @return array
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function column(array $where, $field = null, string $key = '')
    {
        $select = [];
        $pk     = $this->getPk();
        if (! $key && ! $field) {
            $select[] = $pk;
        }

        if ($key) {
            $select[] = $key;
        }

        if ($field) {
            if (is_string($field)) {
                if (strstr($field, ',')) {
                    $field = explode(',', $field);
                    foreach ($field as $k) {
                        $select[] = $k;
                    }
                } else {
                    $select[] = $field;
                }
            } elseif (is_array($field)) {
                $select = array_merge($select, $field);
            }
        }

        $data = $this->search($where)->when($this->defaultSort, function ($query) {
            if (is_array($this->defaultSort)) {
                foreach ($this->defaultSort as $k => $v) {
                    if (is_numeric($k)) {
                        $query->orderByDesc($v);
                    } else {
                        $query->orderBy($k, $v);
                    }
                }
            } elseif (is_object($this->defaultSort)) {
                $query->orderBy($this->defaultSort);
            } else {
                $query->orderByDesc($this->defaultSort);
            }
            $this->defaultSort = '';
        })->select($select)->get();

        if ($data->count()) {
            $data = $data->mapWithKeys(function ($item, $index) use ($key, $field, $pk) {
                if (is_array($field)) {
                    $items = [];
                    foreach ($field as $k) {
                        if (isset($item[$k])) {
                            $items[$k] = $item[$k];
                        }
                    }
                    return [$key ? $item[$key] : $index => $items];
                }
                return [$key ? $item[$key] : $index => $item[$field ?: $pk]];
            });

            if ((! $key && ! $field) || (! $key && $field)) {
                return $data->values()->all();
            }
            if ($key && ! $field) {
                return $data->keys()->all();
            }
            return $data->all();
        }
        return [];
    }

    /**
     * 某个字段减少数量.
     * @param mixed $where
     * @return int
     * @throws BindingResolutionException
     */
    public function dec($where, int $num, string $val)
    {
        return $this->getModel()->where($this->handleWhere($where))->decrement($val, $num);
    }

    /**
     * 某个字段增加数量.
     * @param mixed $where
     * @return int
     * @throws BindingResolutionException
     */
    public function inc($where, int $num, string $key)
    {
        return $this->getModel()->where($this->handleWhere($where))->increment($key, $num);
    }

    /**
     * 获取一个value值
     * @param mixed $where
     * @return null|mixed
     * @throws BindingResolutionException
     */
    public function value($where, string $key)
    {
        return $this->search($where)->orderByDesc($this->getPk())->value($key);
    }

    /**
     * 查询是否存在.
     * @param mixed $where
     * @return bool
     * @throws BindingResolutionException
     */
    public function exists($where)
    {
        return $this->search($where)->exists();
    }

    /**
     * 添加返回主键ID.
     * @return int
     * @throws BindingResolutionException
     */
    public function getIncId(array $data)
    {
        return $this->getModel(false)->insertGetId($data);
    }

    /**
     * 删除.
     * @param mixed $id
     * @return int
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function delete($id, ?string $key = null)
    {
        return $this->search($key ? [$key => $id] : $id)->delete();
    }

    /**
     * 恢复软删除数据.
     * @param mixed $where
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function restore(array|int|string $where)
    {
        return $this->setOnlyTrashed()->search($where)->restore();
    }

    /**
     * 彻底删除数据.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function forceDelete(array|int|string $where): bool|int
    {
        return $this->search($where)->forceDelete();
    }

    /**
     * 设置模型.
     * @return mixed
     */
    abstract protected function setModel();

    /**
     * 获取默认查询条件.
     * @return array
     */
    protected function getDefaultWhereValue()
    {
        $defaultWhere = [];
        foreach ($this->defaultWhere as $key => $value) {
            if ($value instanceof \Closure) {
                $defaultWhere[$key] = $value();
            } else {
                $defaultWhere[$key] = $value;
            }
        }
        return $defaultWhere;
    }

    /**
     * 搜索器.
     * @param BaseModel $model
     * @param array|string $fields
     * @param array $data
     * @return BaseModel
     */
    protected function withSearch($model, $fields, $data = [])
    {
        if (is_string($fields)) {
            $fields = explode(',', $fields);
        }
        foreach ($fields as $key => $field) {
            if ($field instanceof \Closure) {
                $model = $field($model, $data[$key] ?? null, $data);
            } else {
                $fieldName = is_numeric($key) ? $field : $key;
                $method    = Str::studly($fieldName);
                $model     = $model->{$method}($data[$field] ?? null, $data);
            }
        }
        return $model;
    }

    /**
     * 获取主键.
     * @return string
     * @throws BindingResolutionException
     */
    protected function getPk()
    {
        return $this->getModel(false)->getKeyName();
    }

    /**
     * 处理where条件.
     * @param mixed $where
     * @return array
     * @throws BindingResolutionException
     */
    protected function handleWhere($where)
    {
        if (! is_array($where)) {
            $where = [$this->getPk() => $where];
        }
        return $where;
    }

    /**
     * 获取局部作用域
     * @return array[]
     * @throws \ReflectionException
     */
    protected function getScope(array $withSearch, string $model)
    {
        $with     = [];
        $whereKey = [];
        $respones = new \ReflectionClass($model);
        foreach ($withSearch as $key => $value) {
            $method = 'scope' . Str::studly($key);
            if ($respones->hasMethod($method)) {
                $with[] = $key;
            } elseif ($value !== '') {
                $whereKey[$key] = $value;
            }
        }
        return [$with, $whereKey];
    }
}
