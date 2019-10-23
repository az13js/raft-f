<?php

if_get('/', function ()
{
    $demo = distributed_client('demo@create');

    return $demo->id;
});
