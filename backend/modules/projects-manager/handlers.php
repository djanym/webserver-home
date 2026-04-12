<?php

use WebserverHome\ProjectsManager;

function pmGetAllProjects() : void {
    $manager  = ProjectsManager::get_instance();
    $projects = $manager->getAllProjects();

    send_json_success( [
        'projects' => $projects,
        'count'    => count( $projects ),
    ] );
}

function pmGetProject( string $id ) : void {
    $manager = ProjectsManager::get_instance();
    $project = $manager->getProject( $id );

    if ( ! $project ) {
        send_json_error( 'Project not found.', 404 );
    }

    send_json_success( [ 'project' => $project ] );
}

/**
 * @throws JsonException
 */
function CreateProjectCb() : void {
    $input = json_decode( file_get_contents( 'php://input' ), true, 512, JSON_THROW_ON_ERROR );

    if ( ! $input ) {
        send_json_error( 'Invalid request data.', 400 );
    }

    $manager      = ProjectsManager::get_instance();
    $project_data = $manager->tryCreateProject( $input );

    if ( is_app_error( $project_data ) ) {
        $status_code = 422;
    } else {
        $status_code = 201;
    }

//    send_json_success( [ 'project' => $project ], $status_code );
    $manager->sendJsonResponse( [ 'project' => $project_data ], $status_code );
}

function pmUpdateProject( string $id ) : void {
    $manager = ProjectsManager::get_instance();

    if ( ! $manager->projectExists( $id ) ) {
        send_json_error( 'Project not found.', 404 );
    }

    $input = json_decode( file_get_contents( 'php://input' ), true );

    if ( ! $input ) {
        send_json_error( 'Invalid request data.', 400 );
    }

    $project = $manager->updateProject( $id, $input );
    send_json_success( [ 'project' => $project ] );
}

function pmDeleteProject( string $id ) : void {
    $manager = ProjectsManager::get_instance();

    if ( ! $manager->projectExists( $id ) ) {
        send_json_error( 'Project not found.', 404 );
    }

    $manager->deleteProject( $id );
    send_json_success( 'Project deleted successfully.' );
}