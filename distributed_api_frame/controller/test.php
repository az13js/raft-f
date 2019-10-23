<?php
if_get('/test', function ()
{
    return db_query('SHOW DATABASES');
});

if_get('/service', function ()
{
    return client_call('test_service', 'test@calc', [42]);
});