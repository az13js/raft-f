<?php

service('test@calc', function (int $num = 0)
{
    return $num * 2;
});
