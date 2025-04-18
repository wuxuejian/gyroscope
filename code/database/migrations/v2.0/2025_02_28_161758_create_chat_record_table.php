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
        if (! Schema::hasTable('chat_record')) {
            Schema::create('chat_record', function (Blueprint $table) {
                $table->id();
                $table->uuid('chat_record_uuid')->index()->comment('uuid');
                $table->unsignedInteger('chat_history_id')->index()->default(0)->comment('记录对话历史主键id');
                $table->unsignedInteger('uid')->index()->default(0)->comment('uid');
                $table->unsignedInteger('chat_applications_id')->index()->default(0)->comment('chat_applications_id');
                $table->unsignedInteger('vote_status')->index()->default(0)->comment('赞扬状态');
                $table->longText('problem_text')->comment('提问内容');
                $table->longText('answer_text')->comment('回答内容');
                $table->text('sql_text')->comment('sql内容');
                $table->integer('prompt_tokens')->default(0)->comment('问题tokens数');
                $table->integer('completion_tokens')->default(0)->comment('回答tokens数');
                $table->integer('tokens')->default(0)->comment('总tokens数');
                $table->longText('details')->comment('详情');
                $table->integer('run_time')->default(0)->comment('运行时间记录');
                $table->tinyInteger('is_show')->index()->default(0)->comment('是否展示聊天记录内容');
                $table->timestamps();
                $table->softDeletes();
                $table->comment('记录对话的内容');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('chat_record');
    }
};
