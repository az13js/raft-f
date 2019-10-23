<?php

ini_set('display_errors', 'off');
date_default_timezone_set('Asia/Shanghai');

define('ROOT_DIR', __DIR__);
define('FRAME_DIR', ROOT_DIR.'/frame');
define('DOMAIN_DIR', ROOT_DIR.'/domain');
define('CLIENT_DIR', ROOT_DIR.'/client');
define('COMMAND_DIR', ROOT_DIR.'/command');
define('DEP_DOMAIN_DIR', ROOT_DIR.'/dep_domain');
define('DEP_CLIENT_DIR', ROOT_DIR.'/dep_client');
define('DEP_QUEUE_JOB_DIR', ROOT_DIR.'/dep_queue_job');

include FRAME_DIR.'/function.php';
include FRAME_DIR.'/otherwise.php';
include FRAME_DIR.'/unitofwork.php';
include FRAME_DIR.'/log/file.php';

config_dir(ROOT_DIR.'/config');
unit_of_work_db_config_key('distributed');

include ROOT_DIR.'/util/load.php';
include DOMAIN_DIR.'/load.php';
include CLIENT_DIR.'/load.php';

include DEP_DOMAIN_DIR.'/load.php';
include DEP_CLIENT_DIR.'/load.php';
include DEP_QUEUE_JOB_DIR.'/load.php';
