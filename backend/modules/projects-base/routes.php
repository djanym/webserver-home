<?php

declare(strict_types=1);

require_once __DIR__ . '/handlers.php';

return static function (AltoRouter $router): void {
    $router->map('GET', '/projects', static fn() => wb_projects_base_get_all_projects());
    $router->map('GET', '/projects/[a:id]', static fn(string $id) => wb_projects_base_get_project($id));
    $router->map('POST', '/projects', static fn() => wb_projects_base_create_project());
    $router->map('PUT', '/projects/[a:id]', static fn(string $id) => wb_projects_base_update_project($id));
    $router->map('DELETE', '/projects/[a:id]', static fn(string $id) => wb_projects_base_delete_project($id));
};

