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
        if (! Schema::hasColumn('message_template', 'webhook_url')) {
            Schema::table('message_template', function (Blueprint $table) {
                $table->string('webhook_url', 255)->default('')->comment('bot webhook地址')->after('status');
            });
            Schema::table('message_template', function (Blueprint $table) {
                $table->integer('crud_event_id')->index()->default(0)->comment('实体内的触发器id，为0是系统的消息')->after('webhook_url');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        if (! Schema::hasColumn('message_template', 'webhook_url')) {
            Schema::table('message_template', function (Blueprint $table) {
                $table->dropColumn('webhook_url');
            });
        }
        if (! Schema::hasColumn('message_template', 'crud_event_id')) {
            Schema::table('message_template', function (Blueprint $table) {
                $table->dropColumn('crud_event_id');
            });
        }
    }
};
