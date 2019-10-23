<?php
service('status', function()
{
    header('Content-Type: text/plain; charset=utf-8');
    var_dump(RaftExample\RaftServer::getRaftServer());
    die();
});