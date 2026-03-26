<?php

return [
    'commands' => [
        'apache' => [
            'mac' => [
                'restart_command' => 'brew services restart httpd',
            ],
        ],
    ],

    'ignore_files' => [
        '.htaccess',
        '.',
        '..',
        '.DS_Store',
    ],

    // Project folder naming pattern (supports variables like %project-slug%)
    'project_folder_name' => '%project-slug%',

    // Default project structure
    'project_folders' => [
        'docs' => [
            'description' => 'Documentation files',
        ],
        'src' => [
            'description' => 'Source code files',
        ],
        'db-dump' => [
            'description' => 'DB dumps',
        ],
    ],
];
