<?php

declare(strict_types=1);

use WebserverHome\ProjectManager;

function wb_projects_base_get_all_projects(): void
{
    $manager = new ProjectManager();
    $projects = $manager->getAllProjects();

    send_json_success([
        'projects' => $projects,
        'count' => count($projects),
    ]);
}

function wb_projects_base_get_project(string $id): void
{
    $manager = new ProjectManager();
    $project = $manager->getProject($id);

    if (!$project) {
        send_json_error('Project not found.', 404);
    }

    send_json_success(['project' => $project]);
}

function wb_projects_base_create_project(): void
{
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        send_json_error('Invalid request data.', 400);
    }

    $manager = new ProjectManager();
    $errors = $manager->validateProjectData($input);

    if (!empty($errors)) {
        send_json_error(['errors' => $errors], 422);
    }

    $project = $manager->createProject($input);
    send_json_success(['project' => $project], 201);
}

function wb_projects_base_update_project(string $id): void
{
    $manager = new ProjectManager();

    if (!$manager->projectExists($id)) {
        send_json_error('Project not found.', 404);
    }

    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        send_json_error('Invalid request data.', 400);
    }

    $project = $manager->updateProject($id, $input);
    send_json_success(['project' => $project]);
}

function wb_projects_base_delete_project(string $id): void
{
    $manager = new ProjectManager();

    if (!$manager->projectExists($id)) {
        send_json_error('Project not found.', 404);
    }

    $manager->deleteProject($id);
    send_json_success('Project deleted successfully.');
}

