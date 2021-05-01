<?php

$app_start_time = microtime(true);

define('APP_VERSION', '1.0.1');
define('ABSPATH', __DIR__);

include ABSPATH . '/app.php';

// End clock time in seconds.
$app_end_time = microtime(true);
// Calculate script execution time.
$execution_time = ($app_end_time - $app_start_time);
echo " Execution time of script = " . $execution_time . " sec";
