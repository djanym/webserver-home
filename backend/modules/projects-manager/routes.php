<?php

return static function( AltoRouter $router ) : void {
    $router->map( 'GET', '/projects', static fn() => wb_projects_manager_get_all_projects() );
    $router->map( 'GET', '/projects/[a:id]', static fn( string $id ) => wb_projects_manager_get_project( $id ) );
    $router->map( 'POST', '/projects', static fn() => wb_projects_manager_create_project() );
    $router->map( 'PUT', '/projects/[a:id]', static fn( string $id ) => wb_projects_manager_update_project( $id ) );
    $router->map( 'DELETE', '/projects/[a:id]', static fn( string $id ) => wb_projects_manager_delete_project( $id ) );
};
