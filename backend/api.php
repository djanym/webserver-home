<?php
// TODO: modernize session
session_start();
$app_start_time = microtime(true);

const APP_VERSION = '0.1';
const ABSPATH = __DIR__;

include ABSPATH . '/vendor/autoload.php';
include ABSPATH . '/inc/functions.php';
load_config();
include ABSPATH . '/routes.php';

// End clock time in seconds.
$app_end_time = microtime(true);
// Calculate script execution time.
$execution_time = ($app_end_time - $app_start_time);
echo " Execution time of script = " . $execution_time . " sec";


//global $app_config;

include __DIR__ . '/config/config.php';
include __DIR__ . '/inc/functions.php';

// Define current path.
//if ($dir && is_dir(HOME_PATH . '/' . $dir)) {
//    define('CURRENT_PATH', rtrim(HOME_PATH . '/' . $dir, '/'));
//} else {
//    define('CURRENT_PATH', HOME_PATH);
//}

// Define relative path.
//if (CURRENT_PATH === HOME_PATH) {
//    define('RELATIVE_PATH', null);
//} else {
//    define('RELATIVE_PATH', $dir);
//}

$action = $_POST['action'] ?? ($_GET['action'] ?? null);

$response = [];

switch ($action) {
    case 'get_data':
        if( $_GET['type'] == 'listing' ) {
            $response = get_listing_data();
        }
        break;
    case 'create_project':
        create_project();
        break;
    default:
        break;
}

header('Content-Type: application/json');
echo json_encode($response);
die;
