<?php

declare(strict_types=1);
/**
 *  +----------------------------------------------------------------------
 *  | 陀螺匠 [ 赋能开发者，助力企业发展 ]
 *  +----------------------------------------------------------------------
 *  | Copyright (c) 2016~2024 https://www.tuoluojiang.com All rights reserved.
 *  +----------------------------------------------------------------------
 *  | Licensed 陀螺匠并不是自由软件，未经许可不能去掉陀螺匠相关版权
 *  +----------------------------------------------------------------------
 *  | Author: 陀螺匠 Team <admin@tuoluojiang.com>
 *  +----------------------------------------------------------------------
 */
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * 默认实体模型数据
 */
class SystemCrudSeeder extends Seeder
{
    public function run()
    {
        //读取数据文件
        $sqldata = file_get_contents(public_path('install/crud.sql'));
        $sql     = str_replace('`eb_', '`' . env('DB_PREFIX', 'eb_'), $sqldata);
        DB::unprepared($sql);
    }
}
