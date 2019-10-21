<?php

return [
    // 超时时间
    'timeout' => 5.0,
    'default' => [
        // 网关调用策略, 默认: 顺序调用
        'strategy' => \Overtrue\EasySms\Strategies\OrderStrategy::class,

        'gateways' => [
            'yuntongxun',
        ],
    ],

    'gateways' => [
        'errorlog' => [
            'file' => '/tmp/easy-sms.log',
        ],

        'yuntongxun' => [
            'app_id'         => env('YUNTONGXUN_APP_ID', null),
            'account_sid'    => env('YUNTONGXUN_ACCOUNT_SID', null),
            'account_token'  => env('YUNTONGXUN_AUTH_TOKEN', null),
            'is_sub_account' => false,
        ],
    ],
];
