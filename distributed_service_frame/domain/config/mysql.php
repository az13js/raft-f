<?php

return [
    'distributed' => [
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'database' => 'default',
        'username' => 'root',
        'password' => 'password',

        'read' => [
            'distributed_service_frame' => 3306,
        ],
    ],

    'options' => [
        PDO::ATTR_CASE => PDO::CASE_NATURAL,
        PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_STRINGIFY_FETCHES => false,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_PERSISTENT => false,
    ],
];
