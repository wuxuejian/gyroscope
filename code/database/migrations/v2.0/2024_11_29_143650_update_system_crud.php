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
        if (! Schema::hasColumn('system_crud', 'show_comment')) {
            Schema::table('system_crud', function (Blueprint $table) {
                $table->tinyInteger('show_comment')->default(0)->comment('是否展示评论')->after('is_update_table');
            });
        }
        if (! Schema::hasColumn('system_crud', 'show_log')) {
            Schema::table('system_crud', function (Blueprint $table) {
                $table->tinyInteger('show_log')->default(0)->comment('是否展示日志')->after('is_update_table');
            });
        }
        if (! Schema::hasColumn('system_crud', 'comment_title')) {
            Schema::table('system_crud', function (Blueprint $table) {
                $table->string('comment_title', 50)->default('')->comment('评论标题')->after('show_log');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        if (Schema::hasColumn('system_crud', 'show_comment')) {
            Schema::table('system_crud', function (Blueprint $table) {
                $table->dropColumn('show_comment');
            });
        }
        if (Schema::hasColumn('system_crud', 'show_log')) {
            Schema::table('system_crud', function (Blueprint $table) {
                $table->dropColumn('show_log');
            });
        }
        if (Schema::hasColumn('system_crud', 'comment_title')) {
            Schema::table('system_crud', function (Blueprint $table) {
                $table->dropColumn('comment_title');
            });
        }
    }
};
