<?php

return static function( AltoRouter $router ) : void {
    $router->map(
        'GET',
        '/projects',
        static fn() => pmGetAllProjects()
    );
    $router->map(
        'GET',
        '/projects/[a:id]',
        static fn( string $id ) => pmGetProject( $id )
    );
    $router->map(
        'POST',
        '/projects/add',
        static fn() => CreateProjectCb()
    );
    $router->map(
        'PUT',
        '/projects/[a:id]',
        static fn( string $id ) => pmUpdateProject( $id )
    );
    $router->map(
        'DELETE', '/projects/[a:id]', static fn( string $id ) => pmDeleteProject( $id )
    );
};
