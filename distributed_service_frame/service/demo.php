<?php

service('demo@create', function ($column1 = 1, $column2 = 2)
{
    return demo::create($column1, $column2);
});
