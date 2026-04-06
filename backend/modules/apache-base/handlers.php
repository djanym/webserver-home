<?php

declare(strict_types=1);

use WebserverHome\ApacheManager;

function wb_apache_base_restart_apache(): void
{
    $apache = new ApacheManager();
    $success = $apache->restartApache();

    if ($success) {
        send_json_success('Apache restarted successfully.');
        return;
    }

    send_json_error('Failed to restart Apache.', 500);
}

function wb_apache_base_validate_config(): void
{
    $apache = new ApacheManager();
    $valid = $apache->validateConfig();

    send_json_success(['valid' => $valid]);
}

