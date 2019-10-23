<?php
return [
    'midwares' => [
        'default' => 'resource_default',
    ],
    'resources' => [
        'resource_default' => [
            'database' => 'test_db1',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'options' => [
                PDO::ATTR_CASE => PDO::CASE_NATURAL,
                PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_STRINGIFY_FETCHES => false,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => false,
            ],
            'read' => ['127.0.0.1' => '3306'],
            'write' => ['127.0.0.1' => '3306'],
        ],
    ],
];