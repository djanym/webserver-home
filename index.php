<?php

$app_start_time = microtime(true);

const APP_VERSION = '0.1';
include __DIR__ . '/config/config.php';
include __DIR__ . '/app.php';

// End clock time in seconds.
$app_end_time = microtime(true);
// Calculate script execution time.
$execution_time = ($app_end_time - $app_start_time);
echo " Execution time of script = " . $execution_time . " sec";
