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
        if (! Schema::hasTable('chat_applications')) {
            Schema::create('chat_applications', function (Blueprint $table) {
                $table->id();
                $table->string('name')->default('')->comment('名称');
                $table->string('pic')->default('')->comment('图片');
                $table->text('info')->default('')->comment('简介');
                $table->string('edit')->default('')->comment('编辑权限');
                $table->unsignedInteger('uid')->default(0)->comment('创建用户ID');
                $table->unsignedInteger('status')->default(0)->comment('状态');
                $table->tinyInteger('is_table')->default(0)->comment('是否数据库');
                $table->string('auth_ids')->default('')->comment('成员ID');
                $table->unsignedInteger('use_limit')->default(0)->comment('使用频次');
                $table->unsignedInteger('sort')->default(0)->comment('排序');
                $table->unsignedInteger('models_id')->default(0)->comment('模型ID');
                $table->unsignedInteger('count_number')->default(0)->comment('对话轮数');
                $table->longText('tables')->default('')->comment('数据库表名');
                $table->longText('content')->default('')->comment('数据库内容');
                $table->longText('keyword')->default('')->comment('关键词');
                $table->longText('embedding')->comment('数据库内容向量');
                $table->text('tooltip_text')->default('')->comment('提示词');
                $table->text('prologue_text')->default('')->comment('开场白');
                $table->text('prologue_list')->default('')->comment('开场白问题');
                $table->longText('json')->default('')->comment('高级设置');
                $table->longText('data_arrange_text')->default('')->comment('整理数据规格');
                $table->timestamps();
                $table->softDeletes();
                $table->index(['uid', 'name']);
                $table->comment('ai应用表');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('chat_applications');
    }
};
