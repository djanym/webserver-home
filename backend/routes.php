<?php
/**
 * API route bootstrap for module-based routing.
 */

$router = new AltoRouter();

$router->map( 'GET', '/', static fn() => send_json_success( 'Welcome to the API! Please use the endpoints provided.' )
);
$router->map( 'GET', '/config', static fn() => AppShared::getPublicBackendConfig() );

// Checks all modules for routes.php files and registers them.
$moduleRouteFiles = glob( ABSPATH . '/modules/*/routes.php' ) ? : [];
sort( $moduleRouteFiles );

foreach ( $moduleRouteFiles as $moduleRouteFile ) {
    // routes.php returns a callable function with route registration.
    $registerModuleRoutes = include $moduleRouteFile;

    if ( ! is_callable( $registerModuleRoutes ) ) {
        throw new RuntimeException( "Module route file must return a callable: {$moduleRouteFile}" );
    }

    // Call the function to register the module's routes.
    $registerModuleRoutes( $router );
}

$match = $router->match();

if ( is_array( $match ) && is_callable( $match['target'] ) ) {
    call_user_func_array( $match['target'], $match['params'] );
} else {
    send_json_error( 'No route was matched.', 404 );
}
