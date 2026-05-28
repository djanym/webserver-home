<?php

declare(strict_types=1);

require_once __DIR__ . '/handlers.php';

return static function( AltoRouter $router ) : void {
    $router->addMatchTypes([
        'project_slug' => '(?!create$)[0-9A-Za-z]++',
        'db_dump_file' => '[0-9A-Za-z._-]++',
    ]);

    $router->map(
        'POST',
        '/projects/[project_slug:id]/db-dump/create',
        static fn( string $id ) => pmCreateDbDump( $id )
    );
    $router->map(
        'GET',
        '/projects/[project_slug:id]/db-dump',
        static fn( string $id ) => pmGetDbDumps( $id )
    );
    $router->map(
        'GET',
        '/projects/[project_slug:id]/db-dump/[db_dump_file:file_name]/download',
        static fn( string $id, string $file_name ) => pmDownloadDbDump( $id, $file_name )
    );
};
