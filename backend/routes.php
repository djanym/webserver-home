<?php
/**
 * API route bootstrap for module-based routing.
 */

$router = new AltoRouter();

$router->map( 'GET', '/', static fn() => send_json_success( 'Welcome to the API! Please use the endpoints provided.' )
);

$moduleRouteFiles = glob( ABSPATH . '/modules/*/routes.php' ) ? : [];
sort( $moduleRouteFiles );

foreach ( $moduleRouteFiles as $moduleRouteFile ) {
    $registerModuleRoutes = include $moduleRouteFile;

    if ( ! is_callable( $registerModuleRoutes ) ) {
        throw new RuntimeException( "Module route file must return a callable: {$moduleRouteFile}" );
    }

    $registerModuleRoutes( $router );
}

$match = $router->match();

if ( is_array( $match ) && is_callable( $match['target'] ) ) {
    call_user_func_array( $match['target'], $match['params'] );
} else {
    send_json_error( 'No route was matched.', 404 );
}
