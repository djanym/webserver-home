<?php

function load_config(): void
{
    global $app_config;
    $config_file = ABSPATH . '/config/config.php';
    if (file_exists($config_file)) {
        $app_config = include $config_file;
    } else {
        throw new RuntimeException("Configuration file not found: " . $config_file);
    }
}

function config($key, $default = null)
{
    global $app_config;
    return $app_config[$key] ?? $default;
}

function user_can($action): true
{
    // Placeholder for future role/permission logic
    // For now, always allow
    return true;
}

function get_listing_data($dir = false)
{
    $ignore_files = [
        '.htaccess',
        '.',
        '..',
        '.DS_Store',
    ];
    $files = $dirs = [];
    if (!$dir) {
        $current_path = config('home_path');
        $relative_path = null;
    } else {
        $current_path = config('home_path') . '/' . $dir;
        $relative_path = $dir;
    }

    $data = [];
    $directory = dir($current_path);
    while (false !== ($entry = $directory->read())) {
        // Skip ignored files.
        if (in_array($entry, $ignore_files, true)) {
            continue;
        }
        // Full path to file/dir.
        $entry_path = $current_path . '/' . $entry;

        // Add file/dir to array.
        if (is_file($entry_path)) {
            $files[$entry] = array(
                'relative_path' => trim($relative_path . '/' . $entry, '/'),
                'full_path' => $entry_path,
                'name' => $entry,
            );
        } elseif (is_dir($entry_path) && $entry !== '_old-projects_') {
            $dirs[$entry] = array(
                'relative_path' => trim($relative_path . '/' . $entry, '/'),
                'full_path' => $entry_path,
                'name' => $entry,
            );
        }
    }
    $directory->close();

    // Sort files and folders in ASC order
    asort($files);
    asort($dirs);
    $data['files'] = $files;
    $data['folders'] = $dirs;
    return $data;
}