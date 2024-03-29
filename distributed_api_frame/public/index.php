<?php

header('Access-Control-Allow-Origin: *');

// init
include __DIR__.'/../bootstrap.php';
include FRAME_DIR.'/http/php_fpm/application.php';

set_error_handler('http_err_action', E_ALL);
set_exception_handler('http_ex_action');
register_shutdown_function('http_fatel_err_action');

if_has_exception(function ($ex) {

    log_exception($ex);

    return json([
        'succ' => false,
        'msg' => $ex->getMessage(),
    ]);
});

if_verify(function ($action, $args) {

    $data = $action(...$args);

    header('Content-type: application/json');

    return json($data);
});

// init interceptor

// init 404 handler
if_not_found(function () {

    return json([
        'succ' => false,
        'msg' => '404 not found',
    ]);
});

// init controller
include CONTROLLER_DIR.'/index.php';
include CONTROLLER_DIR.'/test.php';

// fix
not_found();
