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

namespace crmeb\traits\service;

use crmeb\exceptions\ServicesException;
use crmeb\services\FormService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Services IDE辅助
 * Trait ServicesTrait.
 * @method null|array|Model get($where, array $field = [], array $with = [],$sort=null) 获取一条数据
 * @method null|array|Model select($where, array $field = [], array $with = []) 获取多条数据
 * @method float sum(array $where, string $field, bool $search = false) 求和
 * @method mixed value(array|int|string $where, string $field) 获取指定条件下的数据
 * @method int count(array $where = []) 读取数据条数
 * @method array column(array $where, $field = null, string $key = '') 获取某个字段数组
 * @method mixed updateOrCreate(array $where, array $data) 更新或新增
 * @method mixed exists($where) 查询是否存在
 * @method mixed dec($where, int $num, string $key) 某个字段减少数量 (不走作用域)
 * @method mixed inc($where, int $num, string $key) 某个字段增加数量 (不走作用域)
 * @method Model create(array $data) 保存数据
 * @method mixed update($id, array $data) 修改数据
 * @method mixed delete($id, ?string $key = null) 删除数据
 */
trait ServicesTrait
{
    protected $databaseListen = [];

    /**
     * 获取列表.
     * @param array|string[] $field
     * @param null|string $sort
     * @return mixed
     */
    public function getList(array $where, array $field = ['*'], $sort = null, array $with = []): array
    {
        $this->runListen(__FUNCTION__);
        [$page, $limit] = $this->getPageValue();
        $list           = $this->dao->getList($where, $field, $page, $limit, $sort, $with);
        $count          = $this->dao->count($where);
        return $this->listData($list, $count);
    }

    /**
     * 显示隐藏修改.
     * @return mixed
     */
    public function showUpdate($id, array $data)
    {
        $this->runListen(__FUNCTION__);
        return $this->dao->update($id, $data);
    }

    /**
     * 控制台打印sql.
     */
    public function dumpSql()
    {
        if (env('APP_DEBUG')) {
            DB::listen(function ($QueryExecuted) {
                $bindings = $QueryExecuted->connection->prepareBindings($QueryExecuted->bindings);
                $sql      = str_replace('?', "'%s'", $QueryExecuted->sql);
                dump(vsprintf($sql, $bindings));
            });
        }
    }

    /**
     * 投放事件.
     */
    protected function listen(string $name, \Closure $callback)
    {
        $this->databaseListen[$name] = $callback;
    }

    /**
     * 监听sql.
     * @param callable|string $name
     */
    protected function runListen($name)
    {
        if (env('APP_DEBUG')) {
            if (is_string($name) && isset($this->databaseListen[$name])) {
                DB::listen($this->databaseListen[$name]);
            } elseif ($name instanceof \Closure) {
                DB::listen($name);
            }
        }
    }

    /**
     * 创建Element ui 表单
     * @param string $title
     * @param $rule
     * @param string $url
     * @param string $method
     * @param array $confing
     * @return array
     * @throws FormBuilderException
     * @throws BindingResolutionException
     */
    public function elForm(string $title, $rule, string $url, string $method = 'POST', array $confing = []): array
    {
        if (!in_array(strtoupper($method), ['POST', 'PUT', 'GET', 'DELETE'])) {
            throw new ServicesException(__('request.error'));
        }
        /** @var FormService $formService */
        $formService = app()->make(FormService::class);

        if ($rule instanceof \Closure) {
            $field = $rule($formService);
        } else if (is_array($rule)) {
            $field = $rule;
        } else {
            throw new ServicesException(__('data.typeError'));
        }

        $form   = $formService->createForm($url)->setMethod($method)->setTitle($title)->setRule($field);
        $rule   = $form->formRule();
        $action = $form->getAction();
        return compact('rule', 'title', 'action', 'method', 'confing');
    }
}
