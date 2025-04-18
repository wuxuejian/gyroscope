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
        if (! Schema::hasTable('system_crud_log')) {
            Schema::create('system_crud_log', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('uid')->default(0)->comment('创建用户ID');
                $table->unsignedInteger('crud_id')->default(0)->comment('crud的主键id');
                $table->unsignedInteger('data_crud_id')->default(0)->comment('数据的crud的主键id');
                $table->unsignedInteger('data_id')->default(0)->comment('crud的表的自增id');
                $table->enum('log_type', ['create', 'update', 'delete', 'share_create', 'share_delete', 'share_update', 'transfer', 'approve'])->default('create')->comment('状态：create=创建；update=更新；');
                $table->string('change_field_name_en', 100)->default('')->comment('修改的字段名称，可以为空');
                $table->text('before_value')->comment('修改之前的值');
                $table->text('after_value')->comment('修改之后的值');
                $table->timestamps();

                $table->index(['crud_id', 'data_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('system_crud_log');
    }
};
