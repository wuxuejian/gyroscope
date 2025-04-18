<?php

return [

    /*
    |--------------------------------------------------------------------------
    | 默认Hash驱动
    |--------------------------------------------------------------------------
    |
    | 此选项控制用于哈希应用程序密码的默认哈希驱动程序。
    | 默认情况下，使用bcrypt算法；但是，如果愿意，您可以自由修改此选项
    |
    | Supported: "bcrypt", "argon", "argon2id"
    |
    */

    'driver' => 'bcrypt',

    /*
    |--------------------------------------------------------------------------
    | Bcrypt Options
    |--------------------------------------------------------------------------
    |
    | 在这里，您可以指定使用Bcrypt算法对密码进行哈希运算时应使用的配置选项。
    | 这将允许您控制哈希给定密码所需的时间
    |
    */

    'bcrypt' => [
        'rounds' => env('BCRYPT_ROUNDS', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | Argon Options
    |--------------------------------------------------------------------------
    |
    | Here you may specify the configuration options that should be used when
    | passwords are hashed using the Argon algorithm. These will allow you
    | to control the amount of time it takes to hash the given password.
    |
    */

    'argon' => [
        'memory' => 1024,
        'threads' => 2,
        'time' => 2,
    ],

];
