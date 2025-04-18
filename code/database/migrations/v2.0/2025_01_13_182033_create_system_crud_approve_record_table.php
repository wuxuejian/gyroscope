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
        Schema::create('system_crud_approve_record', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('approve_id')->index()->default(0)->comment('审批申请表的主键id');
            $table->unsignedInteger('crud_id')->index()->default(0)->comment('crud的主键id');
            $table->unsignedInteger('data_id')->index()->default(0)->comment('实体表主键id');
            $table->string('event', 32)->index()->default('')->comment('触发动作：create、update、delete');
            $table->string('approve_event', 32)->index()->default('')->comment('审批动作：revoke、撤销，reject、驳回;');
            $table->string('table_name', 32)->default('')->comment('crud的表名');
            $table->text('data')->default('')->comment('实体表数据');
            $table->text('schedule_data')->default('')->comment('实体附表数据');
            $table->text('original_data')->default('')->comment('原来实体表数据');
            $table->text('original_schedule_data')->default('')->comment('原来实体附表数据');
            $table->timestamps();
            $table->comment('低代码审批记录表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('system_crud_approve_record');
    }
};
