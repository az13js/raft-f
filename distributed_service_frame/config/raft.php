<?php
return [
    'id' => 1,
    // 等待时间，随机范围是[wait_min, wait_max]，单位为秒
    'wait_min' => 2,
    'wait_max' => 7,
    'servers' => [
        [
            'id' => 1,
            'address' => '127.0.0.1:8083',
            'host' => 'service1.share3nd.com',
        ],
        [
            'id' => 2,
            'address' => '127.0.0.1:8084',
            'host' => 'service2.share3nd.com',
        ],
        [
            'id' => 3,
            'address' => '127.0.0.1:8085',
            'host' => 'service3.share3nd.com',
        ],
        [
            'id' => 4,
            'address' => '127.0.0.1:8086',
            'host' => 'service4.share3nd.com',
        ],
        [
            'id' => 5,
            'address' => '127.0.0.1:8087',
            'host' => 'service5.share3nd.com',
        ],
    ],
];