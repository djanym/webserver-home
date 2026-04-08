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

    // Default project structure.
    'project_folders_structure' => [
        'docs' => 'docs',
        'www' => '{slug}',
        'db-dump' => 'db-dump',
    ],
];
