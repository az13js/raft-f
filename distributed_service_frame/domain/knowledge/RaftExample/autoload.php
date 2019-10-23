<?php
spl_autoload_register(function($class) {
    $ds = DIRECTORY_SEPARATOR;
    $path = explode('\\', $class . '.php');
    if (isset($path[1]) && 'RaftExample' === $path[0]) {
        unset($path[0]);
        include __DIR__ . $ds . implode($ds, $path);
    }
});
