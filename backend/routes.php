<?php
/**
 * API Routes for Webserver Home Manager.
 */

use WebserverHome\ProjectManager;
use WebserverHome\ApacheManager;

// @todo: handle 404.

$router = new AltoRouter();

header('Content-Type: text/json; charset=utf-8');

// Load required classes.
require_once ABSPATH . '/inc/ProjectManager.php';
require_once ABSPATH . '/inc/ApacheManager.php';

// ============================================================================
// Root Route
// ============================================================================

$router->map(
    'GET',
    '/',
    static function () {
        send_json_success('Welcome to the API! Please use the endpoints provided.');
    }
);

// ============================================================================
// Project Routes
// ============================================================================

/**
 * GET /projects - Get all projects.
 */
$router->map(
    'GET',
    '/projects',
    static function () {
        $manager = new ProjectManager();
        $projects = $manager->getAllProjects();

        send_json_success([
            'projects' => $projects,
            'count' => count($projects),
        ]);
    }
);

/**
 * GET /projects/[i:id] - Get a single project.
 */
$router->map(
    'GET',
    '/projects/[i:id]',
    static function ($id) {
        $manager = new ProjectManager();
        $project = $manager->getProject($id);

        if (!$project) {
            send_json_error('Project not found.', 404);
        }

        send_json_success(['project' => $project]);
    }
);

/**
 * POST /projects - Create a new project.
 */
$router->map(
    'POST',
    '/projects',
    static function () {
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
);

/**
 * PUT /projects/[i:id] - Update a project.
 */
$router->map(
    'PUT',
    '/projects/[i:id]',
    static function ($id) {
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
);

/**
 * DELETE /projects/[i:id] - Delete a project.
 */
$router->map(
    'DELETE',
    '/projects/[i:id]',
    static function ($id) {
        $manager = new ProjectManager();

        if (!$manager->projectExists($id)) {
            send_json_error('Project not found.', 404);
        }

        $manager->deleteProject($id);

        send_json_success(['message' => 'Project deleted successfully.']);
    }
);

// ============================================================================
// Apache Routes
// ============================================================================

/**
 * POST /apache/restart - Restart Apache server.
 */
$router->map(
    'POST',
    '/apache/restart',
    static function () {
        $apache = new ApacheManager();
        $success = $apache->restartApache();

        if ($success) {
            send_json_success(['message' => 'Apache restarted successfully.']);
        } else {
            send_json_error('Failed to restart Apache.', 500);
        }
    }
);

/**
 * POST /apache/validate - Validate Apache configuration.
 */
$router->map(
    'POST',
    '/apache/validate',
    static function () {
        $apache = new ApacheManager();
        $valid = $apache->validateConfig();

        send_json_success(['valid' => $valid]);
    }
);

// ============================================================================
// Match and Execute
// ============================================================================

$match = $router->match();

if (is_array($match) && is_callable($match['target'])) {
    call_user_func_array($match['target'], $match['params']);
} else {
    send_json_error('No route was matched.', 404);
}
