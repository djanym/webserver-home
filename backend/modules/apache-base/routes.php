<?php

declare(strict_types=1);

require_once __DIR__ . '/handlers.php';

return static function (AltoRouter $router): void {
    $router->map('POST', '/apache/restart', static fn() => wb_apache_base_restart_apache());
    $router->map('POST', '/apache/validate', static fn() => wb_apache_base_validate_config());
};

