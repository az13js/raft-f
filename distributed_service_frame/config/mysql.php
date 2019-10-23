<?php

return [
    'midwares' => [
        'distributed' => 'local',
    ],

    'resources' => [
        'local' => [
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'database' => 'test_db1',
            'username' => 'root',
            'password' => '',

            'write' => [
                '127.0.0.1' => '3306',
            ],
            'schema' => [
                '127.0.0.1' => '3306',
            ],
            'options' => [
                PDO::ATTR_CASE => PDO::CASE_NATURAL,
                PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_STRINGIFY_FETCHES => false,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => false,
            ],
        ],
    ],
];
