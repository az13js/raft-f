<?php

include_once FRAME_DIR.'/entity.php';
include_once FRAME_DIR.'/database/mysql.php';
include_once FRAME_DIR.'/storage/mongodb.php';
include_once FRAME_DIR.'/cache/redis.php';

config_dir(__DIR__.'/config');

include __DIR__.'/autoload.php';
require_once __DIR__ . '/knowledge/RaftExample/autoload.php';
