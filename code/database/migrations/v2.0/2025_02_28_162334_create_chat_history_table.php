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
        if (! Schema::hasTable('chat_history')) {
            Schema::create('chat_history', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('user_id')->index()->default(0)->comment('用户id');
                $table->unsignedInteger('chat_application_id')->index()->default(0)->comment('应用id');
                $table->string('title', 255)->default('')->comment('标题');
                $table->timestamp('top_up')->comment('置顶');
                $table->tinyInteger('is_show')->default(1)->comment('展示隐藏');

                $table->timestamps();
                $table->softDeletes();
                $table->comment('记录对话历史');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('chat_history');
    }
};
