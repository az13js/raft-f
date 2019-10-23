<?php

// init
include __DIR__.'/../bootstrap.php';
include FRAME_DIR.'/http/php_fpm/distributed_service.php';

set_error_handler('service_err_serialize', E_ALL);
set_exception_handler('service_ex_serialize');
register_shutdown_function('service_fatel_err_serialize');

// init service
include '../service/demo.php';
include '../service/cli.php';
include '../service/raft.php';
include '../service/test.php';

// fix
service_method_not_found();
