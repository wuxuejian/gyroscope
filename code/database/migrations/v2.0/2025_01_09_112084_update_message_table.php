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
        if (! Schema::hasColumn('message', 'crud_id')) {
            Schema::table('message', function (Blueprint $table) {
                $table->integer('crud_id')->index()->default(0)->comment('实体id')->after('remind_time');
            });
        }
        if (! Schema::hasColumn('message', 'event_id')) {
            Schema::table('message', function (Blueprint $table) {
                $table->integer('event_id')->index()->default(0)->comment('实体的触发器id')->after('crud_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        if (! Schema::hasColumn('message', 'crud_id')) {
            Schema::table('message', function (Blueprint $table) {
                $table->dropColumn('crud_id');
            });
        }
        if (! Schema::hasColumn('message', 'event_id')) {
            Schema::table('message', function (Blueprint $table) {
                $table->dropColumn('event_id');
            });
        }
    }
};
