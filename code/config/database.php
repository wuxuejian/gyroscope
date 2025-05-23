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
use Illuminate\Database\DBAL\TimestampType;
use Illuminate\Support\Str;

return [
    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for all database work. Of course
    | you may use many connections at once using the Database library.
    |
    */

    'default' => env('DB_CONNECTION', 'mysql'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the database connections setup for your application.
    | Of course, examples of configuring each database platform that is
    | supported by Laravel is shown below to make development simple.
    |
    |
    | All database work in Laravel is done through the PHP PDO facilities
    | so make sure you have the driver for your particular database of
    | choice installed on your machine before you begin development.
    |
    */

    'connections' => [
        'sqlite' => [
            'driver'                  => 'sqlite',
            'url'                     => env('DATABASE_URL'),
            'database'                => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix'                  => env('DB_PREFIX', 'eb_'),
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
        ],
        'mysql' => [
            'driver'         => 'mysql',
            'url'            => env('DATABASE_URL'),
            'host'           => env('DB_HOST', '127.0.0.1'),
            'port'           => env('DB_PORT', '3306'),
            'database'       => env('DB_DATABASE', 'forge'),
            'username'       => env('DB_USERNAME', 'forge'),
            'password'       => env('DB_PASSWORD', ''),
            'unix_socket'    => env('DB_SOCKET', ''),
            'charset'        => 'utf8mb4',
            'collation'      => 'utf8mb4_unicode_ci',
            'prefix'         => env('DB_PREFIX', 'eb_'),
            'prefix_indexes' => true,
            'strict'         => false,
            'engine'         => null,
            'options'        => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
                // 开启持久连接
                PDO::ATTR_PERSISTENT => true,
            ]) : [],
        ],
        'wiki' => [
            'driver'         => 'mysql',
            'url'            => env('DATABASE_URL'),
            'host'           => 'mysql-internet-cn-north-1-9947e4096ae9497a.rds.jdcloud.com',
            'port'           => env('MANAGE_DB_PORT', '3306'),
            'database'       => 'doccrmeb',
            'username'       => 'doccrmebcom',
            'password'       => 'Honor+32-7677',
            'unix_socket'    => env('DB_SOCKET', ''),
            'charset'        => 'utf8mb4',
            'collation'      => 'utf8mb4_general_ci',
            'prefix'         => env('MANAGE_DB_PREFIX', 'eb_'),
            'prefix_indexes' => true,
            'strict'         => false,
            'engine'         => null,
            'options'        => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],
        'pgsql' => [
            'driver'         => 'pgsql',
            'url'            => env('DATABASE_URL'),
            'host'           => env('DB_HOST', '127.0.0.1'),
            'port'           => env('DB_PORT', '5432'),
            'database'       => env('DB_DATABASE', 'forge'),
            'username'       => env('DB_USERNAME', 'forge'),
            'password'       => env('DB_PASSWORD', ''),
            'charset'        => 'utf8',
            'prefix'         => '',
            'prefix_indexes' => true,
            'schema'         => 'public',
            'sslmode'        => 'prefer',
        ],
        'sqlsrv' => [
            'driver'         => 'sqlsrv',
            'url'            => env('DATABASE_URL'),
            'host'           => env('DB_HOST', 'localhost'),
            'port'           => env('DB_PORT', '1433'),
            'database'       => env('DB_DATABASE', 'forge'),
            'username'       => env('DB_USERNAME', 'forge'),
            'password'       => env('DB_PASSWORD', ''),
            'charset'        => 'utf8',
            'prefix'         => '',
            'prefix_indexes' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run in the database.
    |
    */

    'migrations' => 'migrations',

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer body of commands than a typical key-value system
    | such as APC or Memcached. Laravel makes it easy to dig right in.
    |
    */

    'redis' => [
        'client'  => env('REDIS_CLIENT', 'predis'),
        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix'  => env('REDIS_PREFIX', env('APP_NAME', 'laravel') . ':'),
            //                Str::slug(env('APP_NAME', 'laravel'), '_') . '_database_'),
        ],
        'default' => [
            'url'                => env('REDIS_URL'),
            'host'               => env('REDIS_HOST', '127.0.0.1'),
            'password'           => env('REDIS_PASSWORD', null),
            'port'               => env('REDIS_PORT', '6379'),
            'database'           => env('REDIS_DB', '0'),
            'read_write_timeout' => 0,
        ],
        'cache' => [
            'url'                => env('REDIS_URL'),
            'host'               => env('REDIS_HOST', '127.0.0.1'),
            'password'           => env('REDIS_PASSWORD', null),
            'port'               => env('REDIS_PORT', '6379'),
            'database'           => env('REDIS_CACHE_DB', '1'),
            'read_write_timeout' => 0,
        ],
    ],
    // 数据分页配置
    'page' => [
        // 页码key
        'pageKey' => 'page',
        // 每页截取key
        'limitKey' => 'limit',
        // 每页截取最大值
        'limitMax' => 50,
        // 默认条数
        'defaultLimit' => 10,
    ],
    'dbal' => [
        'types' => [
            'timestamp' => TimestampType::class,
        ],
    ],
];
