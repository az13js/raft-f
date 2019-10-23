<?php

function distributed_client(...$args)
{
    $args = array_merge(['distributed'], $args);

    return client_call(...$args);
}
