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
        if (! Schema::hasTable('system_crud_comment')) {
            Schema::create('system_crud_comment', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('uid')->default(0)->comment('创建用户ID');
                $table->unsignedInteger('crud_id')->default(0)->comment('crud的主键id');
                $table->unsignedInteger('data_id')->default(0)->comment('crud的表的自增id');
                $table->unsignedInteger('pid')->default(0)->comment('评论父级id');
                $table->text('comment')->comment('评论内容');
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
        Schema::dropIfExists('system_crud_comment');
    }
};
