<?php

function get_listing_data($dir)
{
    $data = [];
    $d = dir($dir);
    while (false !== ($entry = $d->read())) {
        if ($entry !== '.' && $entry !== '..') {
            // Add file to files array.
            if (is_file($entry)) {
                $fs[$entry] = array(
                    'relative_path' => trim(RELATIVE_PATH . '/' . $entry, '/'),
                    'full_path' => CURRENT_PATH . '/' . $entry,
                    'name' => $entry,
                );
            }
            // Add folder to folders array.
            if (is_dir($entry) && $entry !== '_old-projects_') {
                $ds[$entry] = array(
                    'relative_path' => trim(RELATIVE_PATH . '/' . $entry, '/'),
                    'full_path' => CURRENT_PATH . '/' . $entry,
                    'name' => $entry,
                );
            }
        }
    }
    $d->close();

    // Sort files and folders in ASC order
    asort($fs);
    asort($ds);
    $data['files'] = $fs;
    $data['folders'] = $ds;
    return $data;
}