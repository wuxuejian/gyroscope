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
        if (! Schema::hasTable('system_crud_questionnaire')) {
            Schema::create('system_crud_questionnaire', function (Blueprint $table) {
                $table->id();
                $table->string('url', 255)->default('')->comment('问卷调查地址');
                $table->string('unique', 100)->unique()->comment('唯一值');
                $table->unsignedInteger('crud_id')->default(0)->comment('实体的id');
                $table->unsignedInteger('user_id')->default(0)->comment('创建人的id');
                $table->tinyInteger('role_type')->default(0)->comment('0=仅企业员工可见，1=所有人');
                $table->dateTime('invalid_time')->comment('失效时间');
                $table->tinyInteger('status')->default(0)->comment('0=关闭；1=开启');

                $table->index('crud_id');
                $table->timestamps();
                $table->comment('问卷调查');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('system_crud_questionnaire');
    }
};
