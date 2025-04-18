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
        if (! Schema::hasTable('chat_models')) {
            Schema::create('chat_models', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('uid')->default(0)->comment('创建用户ID');
                $table->unsignedInteger('provider')->default(0)->comment('供应商类型');
                $table->string('name')->default(0)->comment('模型名称');
                $table->string('pic')->default('')->comment('模型图片');
                $table->string('models_type')->default('')->comment('模型类型');
                $table->string('is_model')->default('')->comment('基础模型');
                $table->string('url', 100)->default('')->comment('API URL');
                $table->string('key', 100)->default('')->comment('API KEY');
                $table->string('json', 500)->default('')->comment('供应商设置');
                $table->timestamps();
                $table->softDeletes();
                $table->index(['uid', 'name']);
                $table->comment('ai模型表');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('chat_models');
    }
};
