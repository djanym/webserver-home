<?php

$app_start_time = microtime(true);

const APP_VERSION = '0.1';
const ABSPATH = __DIR__;

$app_config = yaml_parse_file(ABSPATH . '/config.yml');
include ABSPATH . '/config.php';
include ABSPATH . '/app.php';

// End clock time in seconds.
$app_end_time = microtime(true);
// Calculate script execution time.
$execution_time = ($app_end_time - $app_start_time);
echo " Execution time of script = " . $execution_time . " sec";
