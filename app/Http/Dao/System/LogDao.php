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

namespace App\Http\Dao\System;

use App\Http\Dao\BaseDao;
use App\Http\Model\Admin\Admin;
use App\Http\Model\BaseModel;
use App\Http\Model\System\Log;
use crmeb\traits\dao\JoinSearchTrait;
use crmeb\traits\dao\ListSearchTrait;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Class LogDao.
 */
class LogDao extends BaseDao
{
    use ListSearchTrait;
    use JoinSearchTrait;

    public $table = 'enterprise_log';

    protected int $maxCount;

    /**
     * 设置模型.
     * @return BaseModel
     * @throws BindingResolutionException
     */
    public function getModel(bool $need = true)
    {
        $this->maxCount = (int) env('DB_SUBTABLE_COUNT', 1000000);
        $model          = parent::getModel();
        if ($need) {
            $model->setTable($this->getTableName());
            return $this->getJoinModel('uid', 'uid', model: $model);
        }
        return $model->setTable($this->getTableName());
    }

    /**
     * 查询列表.
     * @throws BindingResolutionException
     */
    public function getLogList(array $where, int $page = 0, int $limit = 0, array $with = []): array
    {
        [$count, $allIds, $maxNum] = $this->searchInfo($where, $page, $limit);
        $list                      = [];
        $prefix                    = env('DB_PREFIX', 'eb_');
        if ($count) {
            $ids   = array_column($allIds, 'id');
            $union = [];
            for ($i = 0; $i <= $maxNum; ++$i) {
                $tableName = $prefix . $this->table . '_' . $i;
                $field     = $tableName . '.id,' . $tableName . '.event_name,' . $tableName . '.uid,' . $tableName . '.terminal,' . $tableName . '.method,' . $tableName . '.last_ip,'
                    . $tableName . '.created_at,' . $tableName . '.path';
                $union[] = 'SELECT distinct ' . $field . ',' . $prefix . 'admin.name as user_name FROM ' . $tableName . ' inner join ' . $prefix . 'admin on ' . $tableName . '.uid = ' . $prefix . 'admin.uid WHERE ' . $tableName . '.id in (' . implode(',', $ids) . ') ORDER BY ' . $tableName . '.id DESC';
            }
            $sql  = implode(' UNION ', $union);
            $res  = DB::select('SELECT * FROM (' . $sql . ') as l');
            $list = $this->getModel(false)->hydrate($res)->toArray();
        }
        return compact('list', 'count');
    }

    /**
     * @throws BindingResolutionException
     */
    public function searchInfo($where, $page, $limit): array
    {
        $maxNum = DB::table('sub_table')->where('table_name', $this->table)->value('num');
        $allIds = [];
        $count  = 0;
        for ($i = $maxNum; $i >= 0; --$i) {
            if ($limit) {
                $res = $this->getModel(false)
                    ->setTable($this->table . '_' . $i)
                    ->when(isset($where['entid']), function ($query) use ($where) {
                        $query->where('entid', $where['entid']);
                    })
                    ->when(isset($where['event_name']) && $where['event_name'], function ($query) use ($where) {
                        $query->where('event_name', 'like', '%' . $where['event_name'] . '%');
                    })
                    ->when($page && $limit, function ($query) use ($page, $limit) {
                        $query->forPage($page, $limit);
                    })
                    ->orderByDesc('id')
                    ->time($where['time'] ?? '')
                    ->select(['id'])
                    ->get()->toArray();
                if ($limit >= count($allIds)) {
                    $allIds = array_merge($allIds, $res);
                    $limit  = $limit - count($allIds);
                }
            }
            $count += $this->getModel(false)
                ->setTable($this->table . '_' . $i)
                ->when(isset($where['entid']), function ($query) use ($where) {
                    $query->where('entid', $where['entid']);
                })->when(isset($where['event_name']) && $where['event_name'], function ($query) use ($where) {
                    $query->where('event_name', 'like', '%' . $where['event_name'] . '%');
                })->time($where['time'] ?? '')->count();
        }
        return [$count, $allIds, $maxNum];
    }

    /**
     * 设置模型.
     *
     * @return mixed|string
     */
    protected function setModel()
    {
        return Log::class;
    }

    protected function setModelB(): string
    {
        return Admin::class;
    }

    /**
     * 创建表.
     */
    protected function createTable(string $tableName, int $suffix = 0)
    {
        Schema::create($tableName, function (Blueprint $table) {
            $table->bigIncrements('id')->comment('id');
            $table->string('uid', 36)->default('')->index()->comment('用户ID');
            $table->string('user_name', 64)->default('')->index()->comment('管理员姓名');
            $table->string('path', 128)->default('')->comment('链接');
            $table->string('method', 20)->default('')->comment('访问方式');
            $table->string('event_name', 60)->default('')->comment('行为');
            $table->integer('entid')->index()->default(0)->comment('企业ID');
            $table->string('type', 32)->default('')->index()->comment('类型');
            $table->string('terminal', 100)->default('')->index()->comment('访问终端');
            $table->ipAddress('last_ip')->comment('访问ip');
            $table->timestamps();

            $table->index(['uid', 'entid'], 'entid_uid');

            $table->engine    = 'InnoDB';
            $table->charset   = 'utf8';
            $table->collation = 'utf8_unicode_ci';
        });
        if ($suffix) {
            $query = 'ALTER TABLE ' . $tableName . ' AUTO_INCREMENT=' . ($this->maxCount * $suffix);
            DB::statement($query);
        }
    }

    /**
     * 获取表名.
     * @return mixed|string
     */
    protected function getTableName()
    {
        return DB::transaction(function () {
            $res = DB::table('sub_table')->where('table_name', $this->table)->first(['sub_table_name', 'num', 'count']);
            if (! $res) {
                $tableName = $this->table . '_' . 0;
                $suffix    = $count = 0;
                DB::table('sub_table')->insert(['table_name' => $this->table, 'sub_table_name' => $tableName, 'num' => $suffix]);
            } else {
                $tableName = $res->sub_table_name;
                $suffix    = (int) $res->num;
                $count     = (int) $res->count;
            }
            if (! Schema::hasTable($tableName)) {
                $this->createTable($tableName, $suffix);
                DB::table('sub_table')->where('table_name', $this->table)->update(['count' => 0]);
            } elseif (! $count) {
                DB::table('sub_table')->where('table_name', $this->table)->update(['count' => DB::table($tableName)->count()]);
            }
            if ($count >= ((int) env('DB_SUBTABLE_COUNT', 1000000) - 1)) {
                $tableName = $this->table . '_' . ($suffix + 1);
                if (! Schema::hasTable($tableName)) {
                    $this->createTable($tableName, $suffix);
                    DB::table('sub_table')->where('table_name', $this->table)->update(['count' => 0]);
                }
            }
            return $tableName;
        });
    }
}
