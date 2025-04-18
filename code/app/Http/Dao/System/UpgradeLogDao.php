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

namespace App\Http\Dao\System;

use App\Http\Dao\BaseDao;
use App\Http\Model\BaseModel;
use App\Http\Model\System\UpgradeLog;
use crmeb\traits\dao\ListSearchTrait;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 升级记录
 * Class UpgradeLogDao.
 */
class UpgradeLogDao extends BaseDao
{
    use ListSearchTrait;

    /**
     * 获取模型.
     * @throws BindingResolutionException
     */
    public function getModel(bool $need = true): BaseModel
    {
        $model = parent::getModel($need);
        $this->checkTable($model->getTable());
        return $model;
    }

    protected function setModel(): string
    {
        return UpgradeLog::class;
    }

    /**
     * 创建表.
     */
    protected function checkTable(string $tableName)
    {
        ! Schema::hasTable($tableName) && Schema::create($tableName, function (Blueprint $table) {
            $table->increments('id');
            $table->string('title', 120)->default('')->comment('标题说明');
            $table->text('content')->default('')->comment('更新内容');
            $table->string('first_version', 3)->default('')->comment('版本号第一位');
            $table->string('second_version', 3)->default('')->comment('版本号第二位');
            $table->string('third_version', 3)->default('')->comment('版本号第三位');
            $table->string('fourth_version', 3)->default('')->comment('版本号第四位');
            $table->text('error_data')->default('')->comment('错误内容');
            $table->string('upgrade_time', 30)->default('')->comment('升级时间');
            $table->string('package_link', 256)->default('')->comment('备份地址');
            $table->string('data_link', 256)->default('')->comment('数据库备份地址');
            $table->engine    = 'InnoDB';
            $table->charset   = 'utf8';
            $table->collation = 'utf8_unicode_ci';
        });
    }
}
