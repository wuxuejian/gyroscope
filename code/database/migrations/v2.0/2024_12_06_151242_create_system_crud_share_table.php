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
        if (! Schema::hasTable('system_crud_share')) {
            Schema::create('system_crud_share', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('crud_id')->default(0)->comment('crud的主键id');
                $table->unsignedInteger('user_id')->default(0)->comment('用户表的id');
                $table->tinyInteger('role_type')->default(0)->comment('0=查看，1=可查看，可编辑，2=可查看，可编辑，可删除');
                $table->unsignedInteger('operate_user_id')->default(0)->comment('操作人的id');

                $table->index(['crud_id', 'user_id']);
                $table->timestamps();
                $table->comment('数据共享记录');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('system_crud_share');
    }
};
