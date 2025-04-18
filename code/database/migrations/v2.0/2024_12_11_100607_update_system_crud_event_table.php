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
        if (! Schema::hasColumn('system_crud_event', 'sms_template_id')) {
            Schema::table('system_crud_event', function (Blueprint $table) {
                $table->string('sms_template_id', 50)->default('')->comment('短信模板id')->after('aggregate_field_rule');
                $table->tinyInteger('sms_status')->default('0')->comment('短信状态')->after('sms_template_id');
                $table->tinyInteger('system_status')->default('0')->comment('系统消息状态')->after('sms_status');
            });
        }
        if (! Schema::hasColumn('system_crud_event', 'work_webhook_url')) {
            Schema::table('system_crud_event', function (Blueprint $table) {
                $table->string('work_webhook_url', 255)->default('')->comment('企业微信bot webhook地址')->after('sms_template_id');
                $table->tinyInteger('work_webhook_status')->default('0')->comment('企业微信bot webhook状态')->after('work_webhook_url');
            });
        }
        if (! Schema::hasColumn('system_crud_event', 'ding_webhook_url')) {
            Schema::table('system_crud_event', function (Blueprint $table) {
                $table->string('ding_webhook_url', 255)->default('')->comment('钉钉机器人webhook地址')->after('work_webhook_url');
                $table->tinyInteger('ding_webhook_status')->default('0')->comment('钉钉机器人webhook状态')->after('ding_webhook_url');
            });
        }
        if (! Schema::hasColumn('system_crud_event', 'other_webhook_url')) {
            Schema::table('system_crud_event', function (Blueprint $table) {
                $table->string('other_webhook_url', 255)->default('')->comment('其他bot webhook地址')->after('ding_webhook_url');
                $table->tinyInteger('other_webhook_status')->default('0')->comment('其他bot 状态')->after('other_webhook_url');
            });
        }
        if (! Schema::hasColumn('system_crud_event', 'update_field_options')) {
            Schema::table('system_crud_event', function (Blueprint $table) {
                $table->text('update_field_options')->comment('更新字段相关数据')->after('other_webhook_url');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        if (! Schema::hasColumn('system_crud_event', 'sms_template_id')) {
            Schema::table('system_crud_event', function (Blueprint $table) {
                $table->dropColumn('sms_template_id');
                $table->dropColumn('sms_status');
                $table->dropColumn('system_status');
            });
        }
        if (! Schema::hasColumn('system_crud_event', 'work_webhook_url')) {
            Schema::table('system_crud_event', function (Blueprint $table) {
                $table->dropColumn('work_webhook_url');
                $table->dropColumn('work_webhook_status');
            });
        }
        if (! Schema::hasColumn('system_crud_event', 'ding_webhook_url')) {
            Schema::table('system_crud_event', function (Blueprint $table) {
                $table->dropColumn('ding_webhook_url');
                $table->dropColumn('ding_webhook_status');
            });
        }
        if (! Schema::hasColumn('system_crud_event', 'other_webhook_url')) {
            Schema::table('system_crud_event', function (Blueprint $table) {
                $table->dropColumn('other_webhook_url');
                $table->dropColumn('other_webhook_status');
            });
        }
        if (! Schema::hasColumn('system_crud_event', 'update_field_options')) {
            Schema::table('system_crud_event', function (Blueprint $table) {
                $table->dropColumn('update_field_options');
            });
        }
    }
};
