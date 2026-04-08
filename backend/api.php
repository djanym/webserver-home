<?php
$app_start_time = microtime(true);

const APP_VERSION = '0.1';
const ABSPATH = __DIR__;

include ABSPATH . '/vendor/autoload.php';
include ABSPATH . '/inc/functions.php';
load_config();
handle_cors();
include ABSPATH . '/inc/Validator.php';
include ABSPATH . '/inc/Generic.php';
include ABSPATH . '/inc/AppError.php';
include ABSPATH . '/inc/AppShared.php';
// Modules.
include ABSPATH . '/modules/projects-manager/projects-manager.php';
// Default routes.
include ABSPATH . '/routes.php';

// End clock time in seconds.
$app_end_time = microtime(true);
// Calculate script execution time.
$execution_time = ($app_end_time - $app_start_time);
//echo " Execution time of script = " . $execution_time . " sec";