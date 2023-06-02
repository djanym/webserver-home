<?php

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
        if (in_array($entry, $ignore_files)) {
            continue;
        }
        $entry_path = $current_path . '/' . $entry;
        // Add file to files array.
        if (is_file($entry_path)) {
            $files[$entry] = array(
                'relative_path' => trim($relative_path . '/' . $entry, '/'),
                'full_path' => $entry_path,
                'name' => $entry,
            );
        }
        // Add folder to folders array.
        if (is_dir($entry_path) && $entry !== '_old-projects_') {
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