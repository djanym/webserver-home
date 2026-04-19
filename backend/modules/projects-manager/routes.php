<?php

return static function( AltoRouter $router ) : void {
    $router->addMatchTypes([
        'project_slug' => '(?!add$)[0-9A-Za-z]++'
    ]);

    $router->map(
        'GET',
        '/projects',
        static fn() => pmGetAllProjects()
    );
    $router->map(
        'POST',
        '/projects/add',
        static fn() => CreateProjectCb()
    );
    $router->map(
        'GET',
        '/projects/[project_slug:id]',
        static fn( string $id ) => pmGetProject( $id )
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
