<?php
/**
 * Project Manager class for handling project operations.
 * This class provides methods for creating, reading, updating, and deleting projects.
 */

namespace WebserverHome;

use WebserverHome\Generic;

/**
 * Class ProjectManager
 *
 * @package WebserverHome
 */
class ProjectsManager extends Generic {
    /**
     * Singleton instance.
     *
     * @var self|null
     */
    private static ?self $instance = null;

    /**
     * Path to the server configuration file.
     *
     * @var string
     */
//    private string $configPath;

    /**
     * Path to the server root directory.
     *
     * @var string
     */
//    private string $serverRoot;

    private array $createProjectFields = [
        'title'               => [
            'always_required',
            'max'           => 60,
            'allowed_chars' => [ 'name_digits' ],
        ],
        'slug'                => [
            'always_required',
            'max'           => 60,
            'allowed_chars' => [ 'slug' ],
        ],
        'domain'              => [
            'always_required',
            'is_domain',
        ],
        'client_name'         => [
            'always_required',
            'max'           => 60,
            'allowed_chars' => [ 'name_digits' ],
        ],
        'custom_path_enabled' => [
            'bool',
        ],
        'path_type'           => [
            'options' => [ 'relative', 'absolute' ],
        ],
        'relative_path'       => [
//            'always_required',
'max' => 200,
'is_path',
        ],
        'absolute_path'       => [
            'when' => [
                'another_field'          => 'path_type',
                'another_field_value_is' => 'absolute',
            ],
            'always_required',
            'max'  => 200,
            'is_path',
        ],
    ];

    /**
     * Get the singleton instance.
     */
    public static function get_instance() : ?self {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Create a new project.
     *
     * @param array $input_data Project data from the form input.
     *
     * @return array|false Created project data.
     * @throws \JsonException
     */
    public function tryCreateProject( array $input_data ) : array|false {
        echo '<pre>';
        $input_data['custom_path_enabled'] = 1;
        $input_data['path_type']           = 'absolute';
        $input_data['path_type']           = 'relative';
        $input_data['relative_path']       = '';
        unset( $input_data['relative_path'] );
        unset( $input_data['absolute_path'] );
        print_r( $input_data );
        // Test.

        // Sanitize and validate provided fields data.
        $validated_data = $this->filterValidateAll( $input_data, $this->createProjectFields );

        // Check for errors after validation.
        if ( $this->error->hasErrors() ) {
            return false;
        }

        // @todo: add default value for the validation logic.
//        $validated_data['path_type'] = $validated_data['path_type'] ?? 'relative';

        $validated_data['project_root_path'] = $this->prepareProjectRootPath( $validated_data );

        echo 'Project root path: ' . $validated_data['project_root_path'] . "\n\r";

        // Run specific checks for the data.
        $validated_data = $this->filterValidateSpecific( $validated_data );

        // Check for errors after validation.
        if ( $this->error->hasErrors() ) {
            return false;
        }

        echo 123;
        die;

        return $this->createProject( $validated_data );
    }

    private function createProject( array $data ) : array|false {
        $project_root_path = $this->resolveProjectRootPath( $data );
        if ( false === $project_root_path ) {
            return false;
        }

        if ( file_exists( $project_root_path ) ) {
            $this->error->add( 'slug', 'Project path already exists.' );

            return false;
        }

        if ( ! mkdir( $project_root_path, 0755, true ) ) {
            $this->error->add( 'path_type', 'Failed to create project root folder.' );

            return false;
        }

        $folders = $this->getProjectFoldersStructure( $data['slug'] );
        foreach ( $folders as $folder_key => $folder_relative_path ) {
            $folder_absolute_path = $this->normalizePath( $project_root_path . '/' . $folder_relative_path );
            if ( ! is_dir( $folder_absolute_path ) && ! mkdir( $folder_absolute_path, 0755, true ) ) {
                $this->error->add( $folder_key, 'Failed to create project folder.' );
                $this->deleteDirectory( $project_root_path );

                return false;
            }
        }

        $document_root = $project_root_path;
        if ( ! empty( $folders['www'] ) ) {
            $document_root = $this->normalizePath( $project_root_path . '/' . $folders['www'] );
        }

        $created_at = gmdate( 'c' );
        $project    = [
            'id'                  => $data['slug'],
            'title'               => $data['title'],
            'slug'                => $data['slug'],
            'domain'              => $data['domain'],
            'client_name'         => $data['client_name'],
            'custom_path_enabled' => isTruthy( $data['custom_path_enabled'] ),
            'path_type'           => $data['path_type'] ?? 'relative',
            'relative_path'       => $data['relative_path'] ?? '',
            'absolute_path'       => $data['absolute_path'] ?? '',
            'project_root_path'   => $project_root_path,
            'document_root'       => $document_root,
            'vhost_file'          => $this->normalizePath( config( 'path_to_apache_vhosts_dir', '' ) . '/' . $data['slug'] . '.conf' ),
            'folders_structure'   => $folders,
            'created_at'          => $created_at,
            'updated_at'          => $created_at,
        ];

        if ( ! $this->writeProjectInfoFile( $project_root_path, $project ) ) {
            $this->deleteDirectory( $project_root_path );

            return false;
        }

        if ( ! $this->createApacheVhostFile( $project ) ) {
            $this->deleteDirectory( $project_root_path );

            return false;
        }

        if ( ! $this->addProjectPathToRegistry( $project_root_path ) ) {
            @unlink( $project['vhost_file'] );
            $this->deleteDirectory( $project_root_path );

            return false;
        }

        return $project;
    }

    /**
     * Update an existing project.
     *
     * @param string $projectId Project ID.
     * @param array  $data      Updated project data.
     *
     * @return array Updated project data.
     */
    public function updateProject( string $projectId, array $data ) : array {
        // @todo: Implement project update logic.
        return [];
    }

    /**
     * Delete a project.
     *
     * @param string $projectId Project ID.
     *
     * @return bool True if deleted successfully.
     */
    public function deleteProject( string $projectId ) : bool {
        // @todo: Implement project deletion logic.
        return false;
    }

    /**
     * Check if a project exists.
     *
     * @param string $projectId Project ID.
     *
     * @return bool True if project exists.
     */
    public function projectExists( string $projectId ) : bool {
        return null !== $this->getProject( $projectId );
    }

    /**
     * Specific validation for specific fields.
     *
     * @param array $fields_data Project data to validate.
     *
     * @return array Array of validation errors, empty if valid.
     * @throws \JsonException
     */
    public function filterValidateSpecific( array $fields_data ) : array {
        $existing_projects = $this->getAllProjects();

        foreach ( $existing_projects as $existing_project ) {
            if ( ! empty( $existing_project['title'] ) && strcasecmp( $existing_project['title'], $fields_data['title'] ) === 0 ) {
                $this->error->add( 'title', 'Project title already exists.' );
            }

            if ( ! empty( $existing_project['slug'] ) && strcasecmp( $existing_project['slug'], $fields_data['slug'] ) === 0 ) {
                $this->error->add( 'slug', 'Project slug already exists.' );
            }

            if ( ! empty( $existing_project['domain'] ) && strcasecmp( $existing_project['domain'], $fields_data['domain'] ) === 0 ) {
                $this->error->add( 'domain', 'Domain already exists.' );
            }
        }

        $path_type = $fields_data['path_type'] ?? '';
        if ( isTruthy( $fields_data['custom_path_enabled'] ) ) {

//            if ( $path_type === 'relative' && empty( $fields_data['relative_path'] ) ) {
//                $this->error->add( 'relative_path', 'Relative path is required.' );
//            }

//            if ( $path_type === 'absolute' && empty( $fields_data['absolute_path'] ) ) {
//                $this->error->add( 'absolute_path', 'Absolute path is required.' );
//            }

            // If custom path enabled, and set to relative or absolute, then clear unwanted errors.
//            if ( $path_type === 'relative' ) {
//                $this->error->remove( 'absolute_path' );
//            } elseif ( $path_type === 'absolute' ) {
//                $this->error->remove( 'relative_path' );
//            }
        }

        // If custom path disabled, clear relative and absolute paths errors.
//        if ( ! isTruthy( $fields_data['custom_path_enabled'] ) ) {
//            $this->error->remove( 'relative_path' );
//            $this->error->remove( 'absolute_path' );
//        }

        if ( $this->error->hasErrors() ) {
            return $fields_data;
        }

        // Check if project root path can be determined.
        if ( empty( $fields_data['project_root_path'] ) ) {
            $this->error->add( 'project_root_path', 'Project root path could not be determined.' );

            return $fields_data;
        }

        // Check if this path is already in use.
        $registry = $this->readProjectsRegistry();
        foreach ( $registry['projects'] as $_project_slug => $_project_data ) {
            if ( normalizePath( $_project_data['project_root_path'] ) === $fields_data['project_root_path'] ) {
                $this->error->add( 'slug', 'Project path is already in use.' );

                return $fields_data;
            }
        }

        // Check if project path can be created.
        if ( ! isWritablePath( $fields_data['project_root_path'] ) ) {
            $this->error->add( 'project_root_path', 'Project root path is not writable.' );

            return $fields_data;
        }

        return $fields_data;
    }

    public function getAllProjects() : array {
        $projects = [];
        $registry = $this->readProjectsRegistry();

        foreach ( $registry['projects'] as $project_root_path ) {
            $project = $this->readProjectInfoByPath( $project_root_path );
            if ( ! empty( $project ) ) {
                $projects[] = $project;
            }
        }

        return $projects;
    }

    public function getProject( string $projectId ) : ?array {
        foreach ( $this->getAllProjects() as $project ) {
            if ( ( $project['id'] ?? '' ) === $projectId || ( $project['slug'] ?? '' ) === $projectId ) {
                return $project;
            }
        }

        return null;
    }

    private function normalizeCreateInput( array $input_data ) : array {
        $input_data['title']               = trim( (string) ( $input_data['title'] ?? '' ) );
        $input_data['slug']                = trim( strtolower( (string) ( $input_data['slug'] ?? '' ) ) );
        $input_data['domain']              = trim( strtolower( (string) ( $input_data['domain'] ?? '' ) ) );
        $input_data['client_name']         = trim( (string) ( $input_data['client_name'] ?? '' ) );
        $input_data['custom_path_enabled'] = isTruthy( $input_data['custom_path_enabled'] ?? false ) ? '1' : '0';

        if ( $input_data['custom_path_enabled'] === '1' ) {
            $input_data['path_type'] = trim( (string) ( $input_data['path_type'] ?? '' ) );

            if ( $input_data['path_type'] === 'relative' ) {
                $input_data['relative_path'] = trim( (string) ( $input_data['relative_path'] ?? '' ) );
                unset( $input_data['absolute_path'] );
            } elseif ( $input_data['path_type'] === 'absolute' ) {
                $input_data['absolute_path'] = trim( (string) ( $input_data['absolute_path'] ?? '' ) );
                unset( $input_data['relative_path'] );
            } else {
                unset( $input_data['relative_path'], $input_data['absolute_path'] );
            }
        } else {
            unset( $input_data['path_type'], $input_data['relative_path'], $input_data['absolute_path'] );
        }

        return $input_data;
    }

    private function isTruthy( mixed $value ) : bool {
        return in_array( strtolower( (string) $value ), [ '1', 'true', 'yes', 'on' ], true );
    }

    private function prepareProjectRootPath( array $project_data ) : string|false {
        $projects_root = trim( (string) config( 'path_to_projects_root', '' ) );
        if ( '' === $projects_root ) {
            return false;
        }

        $slug = trim( (string) ( $project_data['slug'] ?? '' ) );

        $base_path = $projects_root;
        if ( isTruthy( $project_data['custom_path_enabled'] ?? '0' ) ) {
            $path_type = (string) ( $project_data['path_type'] ?? '' );
            if ( 'absolute' === $path_type ) {
                $base_path = trim( (string) ( $project_data['absolute_path'] ?? '' ) );
                if ( '' === $base_path ) {
                    return false;
                }
            } elseif ( 'relative' === $path_type ) {
                $relative_path = trim( (string) ( $project_data['relative_path'] ?? '' ), " \/" );
                if ( '' === $relative_path ) {
                    return false;
                }
                $base_path = $projects_root . '/' . $relative_path;
            } else {

                return false;
            }
        }

        return normalizePath( $base_path . '/' . $slug );
    }

    private function getProjectRegistryPath() : string {
        $projects_registry_root = normalizePath( (string) config( 'path_to_projects_registry', '' ) );

        return $projects_registry_root . '/projects-info.json';
    }

    /**
     * @throws \JsonException
     */
    private function readProjectsRegistry() : array {
        $default_registry = [
            'lastUpdated' => gmdate( 'c' ),
            'projects'    => [],
        ];

        $registry_path = $this->getProjectRegistryPath();
        if ( ! file_exists( $registry_path ) ) {
            return $default_registry;
        }

        $json = file_get_contents( $registry_path );
        if ( false === $json || '' === trim( $json ) ) {
            return $default_registry;
        }

        $decoded = json_decode( $json, true, 512, JSON_THROW_ON_ERROR );
        if ( ! is_array( $decoded ) ) {
            $this->error->add( 'projects_registry', 'Projects registry file is corrupted.' );

            return $default_registry;
        }

        $projects = [];
        if ( ! empty( $decoded['projects'] ) && is_array( $decoded['projects'] ) ) {
            foreach ( $decoded['projects'] as $project_path ) {
                if ( is_string( $project_path ) && '' !== trim( $project_path ) ) {
                    $projects[] = normalizePath( $project_path );
                }
            }
        }

        return [
            'lastUpdated' => (string) ( $decoded['lastUpdated'] ?? $default_registry['lastUpdated'] ),
            'projects'    => array_values( array_unique( $projects ) ),
        ];
    }

    private function addProjectPathToRegistry( string $project_root_path ) : bool {
        $registry                = $this->readProjectsRegistry();
        $registry['projects'][]  = $this->normalizePath( $project_root_path );
        $registry['projects']    = array_values( array_unique( $registry['projects'] ) );
        $registry['lastUpdated'] = gmdate( 'c' );

        return $this->writeProjectsRegistry( $registry );
    }

    private function writeProjectsRegistry( array $registry ) : bool {
        $registry_path = $this->getProjectRegistryPath();
        $registry_dir  = dirname( $registry_path );

        if ( ! is_dir( $registry_dir ) && ! mkdir( $registry_dir, 0755, true ) ) {
            $this->error->add( 'projects_registry', 'Failed to create projects registry directory.' );

            return false;
        }

        $json = json_encode(
            [
                'lastUpdated' => $registry['lastUpdated'] ?? gmdate( 'c' ),
                'projects'    => $registry['projects'] ?? [],
            ],
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
        );

        if ( false === $json ) {
            $this->error->add( 'projects_registry', 'Failed to encode projects registry data.' );

            return false;
        }

        if ( false === file_put_contents( $registry_path, $json . PHP_EOL, LOCK_EX ) ) {
            $this->error->add( 'projects_registry', 'Failed to write projects registry file.' );

            return false;
        }

        return true;
    }

    private function getProjectFoldersStructure( string $slug ) : array {
        $configured_structure = config( 'project_folders_structure', [] );
        if ( ! is_array( $configured_structure ) || empty( $configured_structure ) ) {
            return [ 'www' => $slug ];
        }

        $resolved_structure = [];
        foreach ( $configured_structure as $key => $folder_name ) {
            $resolved_structure[ $key ] = str_replace( '{slug}', $slug, (string) $folder_name );
        }

        return $resolved_structure;
    }

    private function writeProjectInfoFile( string $project_root_path, array $project ) : bool {
        $project_info_path = $this->normalizePath( $project_root_path . '/project-info.json' );
        $json              = json_encode( $project, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );

        if ( false === $json ) {
            $this->error->add( 'project_info', 'Failed to encode project info file data.' );

            return false;
        }

        if ( false === file_put_contents( $project_info_path, $json . PHP_EOL, LOCK_EX ) ) {
            $this->error->add( 'project_info', 'Failed to write project info file.' );

            return false;
        }

        return true;
    }

    private function createApacheVhostFile( array $project ) : bool {
        $template_path = (string) config( 'path_to_vhost_tpl_file', '' );
        if ( '' === $template_path || ! file_exists( $template_path ) ) {
            $this->error->add( 'vhost', 'Virtual host template file does not exist.' );

            return false;
        }

        $template_content = file_get_contents( $template_path );
        if ( false === $template_content || '' === $template_content ) {
            $this->error->add( 'vhost', 'Failed to read virtual host template file.' );

            return false;
        }

        $vhosts_dir = trim( (string) config( 'path_to_apache_vhosts_dir', '' ) );
        if ( '' === $vhosts_dir ) {
            $this->error->add( 'vhost', 'Apache virtual hosts directory is not configured.' );

            return false;
        }

        if ( ! is_dir( $vhosts_dir ) && ! mkdir( $vhosts_dir, 0755, true ) ) {
            $this->error->add( 'vhost', 'Failed to create Apache virtual hosts directory.' );

            return false;
        }

        $replacements = [
            '{{SERVER_NAME}}'   => $project['domain'],
            '{{DOCUMENT_ROOT}}' => $project['document_root'],
            '{{PROJECT_SLUG}}'  => $project['slug'],
            '{server_name}'     => $project['domain'],
            '{document_root}'   => $project['document_root'],
            '{project_slug}'    => $project['slug'],
            '%server_name%'     => $project['domain'],
            '%document_root%'   => $project['document_root'],
            '%project_slug%'    => $project['slug'],
        ];

        $vhost_content = strtr( $template_content, $replacements );
        $vhost_path    = $this->normalizePath( $vhosts_dir . '/' . $project['slug'] . '.conf' );

        if ( false === file_put_contents( $vhost_path, $vhost_content, LOCK_EX ) ) {
            $this->error->add( 'vhost', 'Failed to write Apache virtual host file.' );

            return false;
        }

        return true;
    }

    private function readProjectInfoByPath( string $project_root_path ) : ?array {
        $project_root_path = $this->normalizePath( $project_root_path );
        $project_info_path = $this->normalizePath( $project_root_path . '/project-info.json' );

        if ( ! file_exists( $project_info_path ) ) {
            return null;
        }

        $json = file_get_contents( $project_info_path );
        if ( false === $json || '' === trim( $json ) ) {
            return null;
        }

        $decoded = json_decode( $json, true );
        if ( ! is_array( $decoded ) ) {
            return null;
        }

        return $decoded;
    }

//    private function deleteDirectory( string $path ) : void {
//        if ( ! is_dir( $path ) ) {
//            return;
//        }
//
//        $items = scandir( $path );
//        if ( false === $items ) {
//            return;
//        }
//
//        foreach ( $items as $item ) {
//            if ( $item === '.' || $item === '..' ) {
//                continue;
//            }
//
//            $item_path = $path . '/' . $item;
//            if ( is_dir( $item_path ) ) {
//                $this->deleteDirectory( $item_path );
//            } elseif ( file_exists( $item_path ) ) {
//                @unlink( $item_path );
//            }
//        }
//
//        @rmdir( $path );
//    }
}
