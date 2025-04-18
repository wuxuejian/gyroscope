<?php

return [

    /*
    |--------------------------------------------------------------------------
    | 第三方服务
    |--------------------------------------------------------------------------
    |
    | 此文件用于存储第三方服务（如Mailgun、Postmark、AWS等）的凭据。
    | 此文件提供了此类信息的实际位置，允许包使用常规文件来定位各种服务凭据。
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

];
