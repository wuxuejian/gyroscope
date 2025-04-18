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
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        if (! Schema::hasTable('system_crud_data_share')) {
            Schema::create('system_crud_data_share', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('share_id')->index()->default(0)->comment('数据共享ID');
                $table->unsignedInteger('crud_id')->index()->default(0)->comment('crud的主键id');
                $table->unsignedInteger('data_id')->default(0)->comment('crud的表的自增id');
                $table->unsignedInteger('user_id')->default(0)->comment('用户表的id');
                $table->tinyInteger('is_show')->default(0)->comment('可查看');
                $table->tinyInteger('is_update')->default(0)->comment('可修改');
                $table->tinyInteger('is_delete')->default(0)->comment('可删除');

                $table->index(['crud_id', 'data_id', 'user_id']);
                $table->index(['crud_id', 'user_id']);
                $table->comment('数据共享权限和记录');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('system_crud_data_share');
    }
};
