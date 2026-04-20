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

    if ( false === $project_data ) {
        $manager->sendErrorResponse( 'Project was not created. Review the errors.', 422 );

        return;
    }

    $response_data = [ 'project' => $project_data ];

    if ( $manager->hasErrors() ) {
        $response_data['message'] = 'Project was created, but some errors occurred. Review the errors in project details.';
    } else {
        $response_data['message'] = 'Project was created successfully.';
    }

    $manager->sendJsonResponse( $response_data, 201, true );
}
