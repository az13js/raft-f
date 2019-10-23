<?php
if_get('/test', function ()
{
    return db_query('SHOW DATABASES');
});

if_get('/service', function ()
{
    file_put_contents('debug.log', input('num', '0') . PHP_EOL, FILE_APPEND);
    return client_call('test_service', 'test@calc', [input('num', '0')]);
});
