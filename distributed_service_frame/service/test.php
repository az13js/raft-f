<?php

service('test@calc', function (int $num = 0)
{
    return raft_get_server_type();
});
