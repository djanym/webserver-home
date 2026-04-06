<?php

use WebserverHome\ProjectsManager;

function wb_projects_manager_get_all_projects() : void {
    $manager  = ProjectsManager::get_instance();
    $projects = $manager->getAllProjects();

    send_json_success( [
        'projects' => $projects,
        'count'    => count( $projects ),
    ] );
}

function wb_projects_manager_get_project( string $id ) : void {
    $manager = wb_projects_manager_get_manager();
    $project = $manager->getProject( $id );

    if ( ! $project ) {
        send_json_error( 'Project not found.', 404 );
    }

    send_json_success( [ 'project' => $project ] );
}

function wb_projects_manager_create_project() : void {
    $input = json_decode( file_get_contents( 'php://input' ), true );

    if ( ! $input ) {
        send_json_error( 'Invalid request data.', 400 );
    }

    $manager = wb_projects_manager_get_manager();
    $errors  = $manager->validateProjectData( $input );

    if ( ! empty( $errors ) ) {
        send_json_error( [ 'errors' => $errors ], 422 );
    }

    $project = $manager->createProject( $input );
    send_json_success( [ 'project' => $project ], 201 );
}

function wb_projects_manager_update_project( string $id ) : void {
    $manager = wb_projects_manager_get_manager();

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

function wb_projects_manager_delete_project( string $id ) : void {
    $manager = wb_projects_manager_get_manager();

    if ( ! $manager->projectExists( $id ) ) {
        send_json_error( 'Project not found.', 404 );
    }

    $manager->deleteProject( $id );
    send_json_success( 'Project deleted successfully.' );
}

