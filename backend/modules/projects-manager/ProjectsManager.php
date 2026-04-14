<?php
/**
 * Project Manager class for handling project operations.
 * This class provides methods for creating, reading, updating, and deleting projects.
 */

namespace WebserverHome;

use JsonException;
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
     * @throws JsonException
     */
    public function tryCreateProject( array $input_data ) : array|false {
        $input_data = $this->normalizeCreateInput( $input_data );

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
     * @throws JsonException
     */
    private function createProject( array $data ) : array|false {
        $project_root_path = $data['project_root_path'];
        $created_at        = gmdate( 'c' );

        // If the project root path is not existing, create it.
        if ( ! is_dir( $project_root_path ) && ! createDirectory( $project_root_path ) ) {
            $this->error->add( 'project_root_path', 'Failed to create project root directory.' );

            return false;
        }

        // Initial project registry data. We need to run this to check if it's possible to create the project.
        $project_registry = [
            'title'                    => $data['title'],
            'slug'                     => $data['slug'],
            'domain'                   => $data['domain'],
            'client_name'              => $data['client_name'],
            'custom_path_enabled'      => isTruthy( $data['custom_path_enabled'] ),
            'path_type'                => $data['path_type'] ?? 'relative',
            'relative_path'            => $data['relative_path'] ?? '',
            'absolute_path'            => $data['absolute_path'] ?? '',
            'registered_project_path'  => $project_root_path,
            'registered_registry_path' => $project_root_path . '/project.registry.json',
            'created_at'               => $created_at,
            'updated_at'               => $created_at,
        ];

        // Firstly we need to create a project registry file.
        $project_registry = $this->updateProjectRegistry( $project_registry['registered_registry_path'], $project_registry );

        // If fails then, we need to skip the rest of the process.
        if ( ! $project_registry ) {
            $this->error->add( 'project_registry', 'Failed to create project registry file in the project directory `' . $project_root_path . '`.' );

            return false;
        }

        // Additionally, we need to create a project record in main registry file.
        // If fails then, we need to skip the rest of the process.
        if ( ! $this->updateMainRegistryProjectRecord( $project_registry['slug'], $project_registry ) ) {
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

        $www_root = ! empty( $folders['www'] ) ? normalizePath( $project_root_path . '/' . $folders['www'] ) : null;

        $project_registry = array_merge( $project_registry, [
//            'project_root_path' => $project_root_path,
'document_root' => $www_root,
//            'vhost_file'        => normalizePath( (string) config( 'path_to_apache_vhosts_dir', '' ) . '/' . $data['slug'] . '.conf' ),

        ] );

        $project_registry['vhost_file'] = $this->createApacheVhostFile( $project_registry );

        // After adding more fields, we need to update the project registry.
        $this->updateProjectRegistry( $project_root_path, $project_registry );
//        $this->updateMainRegistryProjectRecord( $project_root_path, $project_registry );

        // Fields to return to frontend.
        return [
            'title'       => $data['title'],
            'slug'        => $data['slug'],
            'domain'      => $data['domain'],
            'client_name' => $data['client_name'],
            'created_at'  => $created_at,
            'updated_at'  => $created_at,
        ];
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
     * @throws JsonException
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

        $registry = $this->readMainRegistry();

        // Try to find project record in the registry by project registered root path.
        $matched_slug = searchInMultiByValue(
            $registry['projects'],
            'registered_root_path',
            $fields_data['project_root_path']
        );
        if ( null !== $matched_slug ) {
            $this->error->add( 'project_root_path', 'Project root path is already used by `' . $matched_slug . '`.' );

            return $fields_data;
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

    /**
     * @throws JsonException
     */
    public function getProject( string $slug ) : ?array {
        if ( '' === $slug ) {
            return null;
        }

        $registry = $this->readMainRegistry();
        foreach ( $registry['projects'] as $project_slug => $project_data ) {
            $project = $this->readProjectInfoByPath( $project_data['project_root_path'] );
            if ( ! empty( $project ) && ! empty( $project['slug'] ) ) {
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

    private function getProjectRegistryPath( string $slug ) : string {
        if ( '' === $slug ) {
            return '';
        }

        $registry = $this->readMainRegistry();
        foreach ( $registry['projects'] as $project_slug => $project_data ) {
            if ( $slug === $project_slug ) {
                return normalizePath( $project_data['registered_registry_path'] ?? '' );
            }
        }

        $projects_root = trim( (string) config( 'path_to_projects_root', '' ) );
        if ( '' === $projects_root ) {
            return '';
        }

        return normalizePath( $projects_root . '/' . $slug . '/project.registry.json' );
    }

    /**
     * Reads the main projects registry file. If the file does not exist, it will try to create it.
     *
     * @throws JsonException
     */
    private function readMainRegistry( bool $create_default = true ) : array|null {
        $default_registry = [
            'last_updated' => gmdate( 'c' ),
            'projects'     => [],
        ];

        $registry_path = config( 'path_to_projects_registry', '' );
        $registry_path = normalizePath( trim( (string) $registry_path ) );

        if ( '' === $registry_path ) {
            $this->error->add( 'main_projects_registry', 'Projects registry path is not configured.' );

            return null;
        }

        // If file does not exist, create it.
        if ( ! file_exists( $registry_path ) ) {
            if ( ! $create_default ) {
                return null;
            }

            $registry_dir = dirname( $registry_path );
            if ( '' !== $registry_dir && ! is_dir( $registry_dir ) && ! createDirectory( $registry_dir ) ) {
                $this->error->add( 'main_projects_registry', 'Failed to create the projects registry directory.' );

                return null;
            }

            if ( false === file_put_contents(
                    $registry_path,
                    json_encode( $default_registry, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ),
                    LOCK_EX
                ) ) {
                $this->error->add( 'main_projects_registry', 'Failed to create the main projects registry file.' );

                return null;
            }

            return $default_registry;
        }

        $json = file_get_contents( $registry_path );

        try {
            $decoded = json_decode( $json, true, 512, JSON_THROW_ON_ERROR );
        } catch ( JsonException $exception ) {
            $this->error->add(
                'main_projects_registry',
                'Main projects registry file is corrupted. JSON error: ' . $exception->getMessage() . '.'
            );

            return null;
        }

        if ( ! is_array( $decoded ) ) {
            $this->error->add( 'main_projects_registry', 'Main projects registry file is corrupted.' );

            return null;
        }

        return $decoded;
    }

    /**
     * Read project registry file by path. If the file does not exist, it will try to read the fallback registry file.
     * If create_default is true, it will create a default registry file and return an empty array.
     *
     * @param string $registry_path
     * @param bool   $create_default Create a default registry file if it does not exist.
     *
     * @return array|null
     * @throws JsonException
     */
    public function readProjectRegistry( string $registry_path, bool $create_default = false ) : array|null {
        $registry_path = normalizePath( trim( $registry_path ) );
        if ( '' === $registry_path ) {
            return null;
        }

        if ( ! file_exists( $registry_path ) ) {
            $fallback_registry_path = normalizePath( dirname( $registry_path ) . '/project.registry.json' );
            if ( ! file_exists( $fallback_registry_path ) ) {
                // Create default registry file and put empty data into it.
                if ( $create_default ) {
                    if ( false === file_put_contents(
                            $fallback_registry_path,
                            json_encode( [ 'registered_registry_path' => $fallback_registry_path ], JSON_THROW_ON_ERROR ),
                            LOCK_EX ) ) {
                        $this->error->add( 'project_registry', 'Failed to write the default project registry file.' );

                        return null;
                    }

                    return [];
                }

                return null;
            }

            $registry_path = $fallback_registry_path;
        }

        $json = file_get_contents( $registry_path );
        if ( false === $json || '' === trim( $json ) ) {
            return null;
        }

        try {
            $decoded = json_decode( $json, true, 512, JSON_THROW_ON_ERROR );
        } catch ( JsonException $exception ) {
            $this->error->add(
                'project_registry',
                'Project registry file is corrupted. JSON error: ' . $exception->getMessage() . '.'
            );

            return null;
        }

        return is_array( $decoded ) ? $decoded : null;
    }

    private function updateMainRegistry( array $registry ) : bool {
        $registry_path = config( 'path_to_projects_registry', '' );
        $registry_path = normalizePath( trim( (string) $registry_path ) );
        if ( '' === $registry_path ) {
            $this->error->add( 'main_projects_registry', 'Projects registry path is not configured.' );

            return false;
        }
        $json = json_encode( $registry, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
        if ( false === file_put_contents( $registry_path, $json, LOCK_EX ) ) {
            $this->error->add( 'main_projects_registry', 'Failed to write the main projects registry file.' );

            return false;
        }

        return true;
    }

    /**
     * @throws JsonException
     */
    private function updateMainRegistryProjectRecord( array $project_registry ) : bool {
        $project_regisered_root_path = $project_registry['registered_root_path'] ?? null;
        $project_registry_path       = $project_registry['registered_registry_path'] ?? null;

        if ( empty( $project_regisered_root_path ) || empty( $project_registry_path ) ) {
            $this->error->add( 'main_projects_registry', 'Project root path is empty.' );

            return false;
        }

        $registry = $this->readMainRegistry();

        if ( $this->error->hasErrors() ) {
            return false;
        }

        // Set default values for the project registry.
        if ( ! isset( $registry['projects'] ) || ! is_array( $registry['projects'] ) ) {
            $registry['projects'] = [];
        }

        // Find project record in the registry by project slug.
        $project_slug = $project_registry['slug'] ?? '';
        if ( isset( $registry['projects'][ $project_slug ] ) ) {
            $registry['projects'][ $project_slug ]['registered_root_path']     = $project_regisered_root_path;
            $registry['projects'][ $project_slug ]['registered_registry_path'] = $project_registry_path;
        } else {
            // Find project record in the registry by project registered root path.
            $matched_slug = searchInMultiByValue(
                $registry['projects'],
                'registered_root_path',
                $project_regisered_root_path
            );
            if ( null !== $matched_slug ) {
                $this->error->add( 'main_projects_registry', 'Project root path is already used by `' . $matched_slug . '`.' );

                return false;
            }

            // Find project record in the registry by project registered registry path.
            $matched_slug = searchInMultiByValue(
                $registry['projects'],
                'registered_registry_path',
                $project_registry_path
            );
            if ( null !== $matched_slug ) {
                $this->error->add( 'main_projects_registry', 'Project registry path is already used by `' . $matched_slug . '`.' );

                return false;
            }
        }

        $this->updateMainRegistry( $registry );

        if ( $this->error->hasErrors() ) {
            return false;
        }

        return true;
    }

    /**
     * @throws JsonException
     */
    private function updateProjectRegistry( string $registry_path, array $project_registry ) : array|false {
        $existing_registry = $this->readProjectRegistry( $registry_path, true );

        $registry_path                                = $existing_registry['registered_registry_path'] ?? $registry_path;
        $project_registry['registered_registry_path'] = $registry_path;

        if ( false === file_put_contents(
                $registry_path,
                json_encode( $project_registry, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ),
                LOCK_EX ) ) {
            $this->error->add( 'project_registry', 'Failed to write the project registry file.' );

            return false;
        }

        return $project_registry;
    }

    private function readProjectInfoByPath( string $project_root_path ) : ?array {
        $project_root_path = normalizePath( trim( $project_root_path ) );
        if ( '' === $project_root_path ) {
            return null;
        }

        $project_registry = $this->readProjectRegistry( $project_root_path );
        if ( false === $project_registry ) {
            return null;
        }

        $project_registry['project_root_path'] = $project_registry['project_root_path'] ?? $project_root_path;
        $project_registry['slug']              = $project_registry['slug'] ?? basename( $project_root_path );

        if ( empty( $project_registry['document_root'] ) ) {
            $folders = $project_registry['folders_structure'] ?? $this->getProjectFolders( (string) $project_registry['slug'] );
            if ( is_array( $folders ) && ! empty( $folders['www'] ) ) {
                $project_registry['document_root'] = normalizePath( $project_root_path . '/' . $folders['www'] );
            }
        }

        if ( empty( $project_registry['vhost_file'] ) ) {
            $project_registry['vhost_file'] = normalizePath( (string) config( 'path_to_apache_vhosts_dir', '' ) . '/' . $project_registry['slug'] . '.conf' );
        }

        return $project_registry;
    }

    private function deleteDirectory( string $path ) : bool {
        $path = normalizePath( trim( $path ) );
        if ( '' === $path || '/' === $path ) {
            return false;
        }

        if ( ! file_exists( $path ) ) {
            return true;
        }

        if ( is_file( $path ) || is_link( $path ) ) {
            return unlink( $path );
        }

        $items = scandir( $path );
        if ( false === $items ) {
            return false;
        }

        foreach ( array_diff( $items, [ '.', '..' ] ) as $item ) {
            if ( ! $this->deleteDirectory( $path . '/' . $item ) ) {
                return false;
            }
        }

        return rmdir( $path );
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

        if ( ! is_dir( $vhosts_dir ) ) {
            if ( ! mkdir( $vhosts_dir, 0755, true ) && ! is_dir( $vhosts_dir ) ) {
                $this->error->add( 'vhost', 'Failed to create Apache virtual hosts directory.' );

                return false;
            }
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
        $vhost_path    = normalizePath( $vhosts_dir . '/' . $project['slug'] . '.conf' );

        if ( false === file_put_contents( $vhost_path, $vhost_content, LOCK_EX ) ) {
            $this->error->add( 'vhost', 'Failed to write Apache virtual host file.' );

            return false;
        }

        return true;
    }
}
