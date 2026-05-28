<?php

use WebserverHome\DbDumpManager;

function pmCreateDbDump( string $id ) : void {
    $manager = DbDumpManager::get_instance();
    $result = $manager->createDbDump( $id );

    if ( false === $result ) {
        $manager->sendErrorResponse( 'DB dump was not created. Review the errors.', 422 );

        return;
    }

    $manager->sendJsonResponse(
        [
            'project' => $result['project'],
            'dump'    => $result['dump'],
            'message' => 'DB dump placeholder was created successfully.',
        ],
        201,
        true
    );
}

function pmGetDbDumps( string $id ) : void {
    $manager = DbDumpManager::get_instance();
    $result = $manager->getProjectDbDumps( $id );

    if ( false === $result ) {
        $manager->sendErrorResponse( 'Project was not found.', 404 );

        return;
    }

    $manager->sendJsonResponse( $result, 200, true );
}

function pmDownloadDbDump( string $id, string $file_name ) : void {
    $manager = DbDumpManager::get_instance();

    if ( ! $manager->downloadDbDump( $id, $file_name ) ) {
        $manager->sendErrorResponse( 'DB dump file was not found.', 404 );
    }
}
