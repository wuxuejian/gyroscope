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
        if (! Schema::hasColumn('system_crud_role', 'transfer')) {
            Schema::table('system_crud_role', function (Blueprint $table) {
                $table->unsignedInteger('transfer')->default(0)->comment('查看权限:4、全部.3、指定部门.2、当前部门.1、仅本人.0、不允许');
            });
        }
        if (! Schema::hasColumn('system_crud_role', 'transfer_frame')) {
            Schema::table('system_crud_role', function (Blueprint $table) {
                $table->string('transfer_frame', 256)->nullable()->comment('查看部门');
            });
        }

        if (! Schema::hasColumn('system_crud_role', 'share')) {
            Schema::table('system_crud_role', function (Blueprint $table) {
                $table->unsignedInteger('share')->default(0)->comment('查看权限:4、全部.3、指定部门.2、当前部门.1、仅本人.0、不允许');
            });
        }
        if (! Schema::hasColumn('system_crud_role', 'share_frame')) {
            Schema::table('system_crud_role', function (Blueprint $table) {
                $table->string('share_frame', 256)->nullable()->comment('查看部门');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        if (! Schema::hasColumn('system_crud_role', 'transfer')) {
            Schema::table('system_crud_role', function (Blueprint $table) {
                $table->dropColumn('transfer');
            });
        }
        if (! Schema::hasColumn('system_crud_role', 'transfer_frame')) {
            Schema::table('system_crud_role', function (Blueprint $table) {
                $table->dropColumn('transfer_frame');
            });
        }

        if (! Schema::hasColumn('system_crud_role', 'share')) {
            Schema::table('system_crud_role', function (Blueprint $table) {
                $table->dropColumn('share');
            });
        }
        if (! Schema::hasColumn('system_crud_role', 'share_frame')) {
            Schema::table('system_crud_role', function (Blueprint $table) {
                $table->dropColumn('share_frame');
            });
        }
    }
};
