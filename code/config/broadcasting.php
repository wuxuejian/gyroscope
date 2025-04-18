<?php

return [

    /*
    |--------------------------------------------------------------------------
    | 默认广播者
    |--------------------------------------------------------------------------
    |
    | 此选项控制需要广播事件时框架将使用的默认广播者。
    | 您可以将其设置为下面的“连接”数组中定义的任何连接。
    |
    | 支持: "pusher", "redis", "log", "null"
    |
    */

    'default' => env('BROADCAST_DRIVER', 'null'),

    /*
    |--------------------------------------------------------------------------
    | Broadcast Connections
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the broadcast connections that will be used
    | to broadcast events to other systems or over websockets. Samples of
    | each available type of connection are provided inside this array.
    |
    */

    'connections' => [

        'pusher' => [
            'driver' => 'pusher',
            'key' => env('PUSHER_APP_KEY'),
            'secret' => env('PUSHER_APP_SECRET'),
            'app_id' => env('PUSHER_APP_ID'),
            'options' => [
                'cluster' => env('PUSHER_APP_CLUSTER'),
                'useTLS' => true,
            ],
        ],
        'socketio' => [
            'driver' => 'socketio',
            'host' => env('SOCKETIO_HOST', 'http://0.0.0.0'),
            'port' => env('SOCKETIO_PORT', '3000'),
            'client' => 'redis',
            'redis' => [
                'scheme' => 'tcp',
                'host' => env('REDIS_HOST', '127.0.0.1'),
                'password' => env('REDIS_PASSWORD', null),
                'port' => env('REDIS_PORT', '6379'),
                'database' => 5,
            ],
        ],
        'ably' => [
            'driver' => 'ably',
            'key' => env('ABLY_KEY'),
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => 'default',
        ],

        'log' => [
            'driver' => 'log',
        ],

        'null' => [
            'driver' => 'null',
        ],

    ],

];
