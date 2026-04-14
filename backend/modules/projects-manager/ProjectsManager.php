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
            'when'    => [
                'another_field'                 => 'custom_path_enabled',
                'another_field_value_is_truthy' => true,
            ],
            'always_required',
            'options' => [ 'relative', 'absolute' ],
        ],
        'relative_path'       => [
            'when' => [
                [
                    'another_field'                 => 'custom_path_enabled',
                    'another_field_value_is_truthy' => true,
                ],
                [
                    'another_field'          => 'path_type',
                    'another_field_value_is' => 'relative',
                ],
            ],
            'always_required',
            'max'  => 200,
            'is_path',
        ],
        'absolute_path'       => [
            'when' => [
                [
                    'another_field'                 => 'custom_path_enabled',
                    'another_field_value_is_truthy' => true,
                ],
                [
                    'another_field'          => 'path_type',
                    'another_field_value_is' => 'absolute',
                ],
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
        // Sanitize and validate provided fields data.
        $validated_data = $this->filterValidateAll( $input_data, $this->createProjectFields );

        // Check for errors after validation.
        if ( $this->error->hasErrors() ) {
            return false;
        }

        $validated_data['project_root_path'] = $this->prepareProjectRootPath( $validated_data );

        // Run specific checks for the data.
        $validated_data = $this->filterValidateSpecific( $validated_data );

        // Check for errors after validation.
        if ( $this->error->hasErrors() ) {
            return false;
        }

        return $this->createProject( $validated_data );
    }

    /**
     * Creates a new project. All checks are done before this method is called.
     * If something goes wrong, it will return false, or if project is created partially,
     * it will return the partially created project.
     * No errors will be added to the error object.
     *
     * @param array $data
     *
     * @return array|false
     */
    private function createProject( array $data ) : array|false {
        $project_root_path = $data['project_root_path'];

        // If the project root path is not existing, create it.
        if ( ! is_dir( $project_root_path ) && ! createDirectory( $project_root_path ) ) {
            $this->error->add( 'project_root_path', 'Failed to create project root directory.' );

            return false;
        }

        // Init data.
        $project_registry = [
            'title'               => $data['title'],
            'slug'                => $data['slug'],
            'domain'              => $data['domain'],
            'client_name'         => $data['client_name'],
            'custom_path_enabled' => isTruthy( $data['custom_path_enabled'] ),
            'path_type'           => $data['path_type'] ?? 'relative',
            'relative_path'       => $data['relative_path'] ?? '',
            'absolute_path'       => $data['absolute_path'] ?? '',
            'created_at'          => gmdate( 'c' ),
            'updated_at'          => gmdate( 'c' ),
        ];

        // Firstly we need to create a project registry file.
        // If fails then, we need to skip the rest of the process.
        if ( ! $this->updateProjectRegistry( $project_root_path, $project_registry ) ) {
            $this->error->add( 'project_registry', 'Failed to create project registry file in the project directory `' . $project_root_path . '`.' );

            return false;
        }

        // Additionally, we need to create a project record in main registry file.
        // If fails then, we need to skip the rest of the process.
        if ( ! $this->updateMainRegistryProjectRecord( $project_root_path, $project_registry ) ) {
            $this->error->add( 'main_projects_registry', 'Failed to add project path to the main projects registry.' );

            return false;
        }

        // Add project folder structure to project registry.
        $project_registry['folders_structure'] = config( 'project_folders_structure', [] );

        // Create project folders structure.
        $folders = $this->getProjectFolders( $data['slug'] );
        foreach ( $folders as $folder_relative_path ) {
            $folder_absolute_path = normalizePath( $project_root_path . '/' . $folder_relative_path );
            if ( ! is_dir( $folder_absolute_path ) ) {
                createDirectory( $folder_absolute_path );
            }
        }

        $www_root = $folders['www'] ? normalizePath( $project_root_path . '/' . $folders['www'] ) : null;

        $created_at = gmdate( 'c' );
        $project    = [

            'project_root_path' => $project_root_path,
            'document_root'     => $document_root,
            'vhost_file'        => $this->normalizePath( config( 'path_to_apache_vhosts_dir', '' ) . '/' . $data['slug'] . '.conf' ),
            'folders_structure' => $folders,
            'created_at'        => $created_at,
            'updated_at'        => $created_at,
        ];

        if ( ! $this->createApacheVhostFile( $project ) ) {
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

        if ( $this->error->hasErrors() ) {
            return $fields_data;
        }

        // Check if project root path can be determined.
        if ( empty( $fields_data['project_root_path'] ) ) {
            $this->error->add( 'project_root_path', 'Project root path could not be determined.' );

            return $fields_data;
        }

        // Check if this path is already in use.
        $registry = $this->readMainRegistry();
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

        // Check if projects registry can be written.
        if ( isWritablePath( config( 'path_to_projects_registry', '' ) ) ) {
            $this->error->add( 'projects_registry', 'Projects registry is not writable. Check `path_to_projects_registry` in the server-config file.' );

            return $fields_data;
        }

        return $fields_data;
    }

    public function getAllProjects() : array {
        $projects = [];
        $registry = $this->readMainRegistry();

        foreach ( $registry['projects'] as $project_root_path ) {
            $project = $this->readProjectInfoByPath( $project_root_path );
            if ( ! empty( $project ) ) {
                $projects[] = $project;
            }
        }

        return $projects;
    }

    public function getProject( string $slug ) : ?array {
        // @llm-agent-task: Implement this method.
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

    private function getMainRegistryPath() : string {
        $projects_registry_root = normalizePath( (string) config( 'path_to_projects_registry', '' ) );

        return $projects_registry_root . '/projects.registry.json';
    }

    private function getProjectRegistryPath( string $project_slug ) : string {
        // @llm-agent-task: Implement this method.
    }

    /**
     * @throws \JsonException
     */
    private function readMainRegistry() : array {
        $default_registry = [
            'last_updated' => gmdate( 'c' ),
            'projects'     => [],
        ];

        $registry_path = $this->getMainRegistryPath();
        if ( ! file_exists( $registry_path ) ) {
            return $default_registry;
        }

        $json = file_get_contents( $registry_path );
        if ( false === $json || '' === trim( $json ) ) {
            return $default_registry;
        }

        $decoded = json_decode( $json, true, 512, JSON_THROW_ON_ERROR );
        if ( ! is_array( $decoded ) ) {
            $this->error->add( 'main_projects_registry', 'Main projects registry file is corrupted.' );

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
            'last_updated' => (string) ( $decoded['last_updated'] ?? $default_registry['last_updated'] ),
            'projects'     => array_values( array_unique( $projects ) ),
        ];
    }

    private function readProjectRegistry( string $project_root_path ) : bool {
        // @llm-agent-task: Implement this method.
    }

    private function updateMainRegistryProjectRecord( string $project_root_path, array $project_registry ) : bool {
        // @llm-agent-task: Implement this method. If file is exists, then update it. If not, then create it. If project record is already exists, then update it, if not, then create it.
    }

    private function updateProjectRegistry( string $project_root_path, array $project_registry ) : bool {
        // @llm-agent-task: Implement this method. If file is exists, then update it. If not, then create it.
    }

    /**
     * Prepare array with main project folders, such as folder for `www` and `docs`.
     *
     * @param string $slug
     *
     * @return string[]
     */
    private function getProjectFolders( string $slug ) : array {
        $configured_structure = config( 'project_folders_structure', [] );
        if ( ! is_array( $configured_structure ) || empty( $configured_structure ) ) {
            return [ 'www' => $slug ];
        }

        return array_map( static function( $folder_name ) use ( $slug ) { return str_replace( '{slug}', $slug, (string) $folder_name ); }, $configured_structure );
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
}
