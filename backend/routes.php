<?php

// @todo: handle 404.

$router = new AltoRouter();

header('Content-Type: text/json; charset=utf-8');

// Empty route.
$router->map(
    'GET',
    '/',
//    ['\App\Controllers\MaterialsController', 'coursesListPage']
    static function () {
        send_json_success('Welcome to the API! Please use the endpoints provided.');
    }
);

// Match current request url.
$match = $router->match();

// Call closure or response with 404 status.
if (is_array($match) && is_callable($match['target'])) {
    call_user_func_array($match['target'], $match['params']);
} else {
    // no route was matched
    send_json_error('No route was matched.', 404);
}
