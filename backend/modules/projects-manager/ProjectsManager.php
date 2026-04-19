<?php
/**
 * Project Manager class for handling project operations.
 * This class provides methods for creating, reading, updating, and deleting projects.
 */

namespace WebserverHome;

use JsonException;

/**
 * Class ProjectManager
 *
 * @package WebserverHome
 */
class ProjectsManager extends Generic {
    private const string MAIN_PROJECTS_REGISTRY_FILENAME = 'projects-main.registry.json';

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
        // If any warnings happens, then we need to add them to the main project registry file.
        $log_warnings = [];
        // Log actions/any information to show in the frontend.
        $log = [];

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
            'registered_root_path'     => $project_root_path,
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

        // Log actions to show in the frontend.
        $log[] = '+ Project registry file created: ' . $project_registry['registered_registry_path'];

        // Additionally, we need to create a project record in main registry file.
        // If fails then, we need to skip the rest of the process.
        if ( ! $this->updateMainRegistryProjectRecord( $project_registry ) ) {
            $this->error->add( 'main_projects_registry', 'Failed to add project path to the main projects registry.' );

            return false;
        }

        $log[] = 'Project record added to the main registry: ' . $project_registry['slug'];

        // Add project folder structure to project registry.
        $project_registry['folders_structure'] = config( 'project_folders_structure', [] );

        // Create project folders structure.
        $folders = $this->getProjectFolders( $data['slug'] );
        foreach ( $folders as $folder_relative_path ) {
            $folder_absolute_path = normalizePath( $project_root_path . '/' . $folder_relative_path );
            if ( ! is_dir( $folder_absolute_path ) ) {
                if ( createDirectory( $folder_absolute_path ) ) {
                    $log[] = '+ Project folder created: ' . $folder_absolute_path;
                } else {
                    $log_warnings[] = [
                        'code'    => 'project_folders_failed',
                        'message' => 'Failed to create folder: ' . $folder_absolute_path,
                    ];
                    $log[]          = ' - Failed creating folder: ' . $folder_absolute_path;
                }
            } else {
                $log[] = ' - Skipped creating folder (already exists): ' . $folder_absolute_path;
            }
        }

        $log[] = 'Project folders created/registered: ' . implode( ', ', $folders );

        $www_root = ! empty( $folders['www'] ) ? normalizePath( $project_root_path . '/' . $folders['www'] ) : null;

        $project_registry['document_root'] = $www_root;

        $vhost_file = $this->createApacheVhostFile( $project_registry );
        if ( ! $vhost_file ) {
            $log_warnings[] = [
                'code'    => 'vhost_file_failed',
                'message' => 'Virtual host file was not created.',
                'details' => implode( ' ', $this->error->getErrorMessages( 'vhost' ) )
            ];
            // Remove the error message from the error object, so it won't be shown in the frontend.'
            $this->error->remove( 'vhost' );

            $log[] = ' - Failed to create Apache vhost file for the project. Please check the server logs for more details.';
        } else {
            $log[]                          = '+ Apache vhost file created: ' . $vhost_file;
            $project_registry['vhost_file'] = (string) $vhost_file;
        }

        // Set project status based on the warnings and errors.
        $project_registry['status'] = $this->prepareProjectStatus( $log_warnings, null ); // Currently all issues are treated as warnings. Later we can add errors and warnings.

        // Initial message.
        $project_message = 'Project created successfully.';

        if ( $project_registry['status'] !== 'active' ) {
            $log[] = 'Project was created, but some issues occurred.';
        }

        $project_registry['warnings'] = $log_warnings;

        // After adding more fields, we need to update the project registry.
        $this->updateProjectRegistry( $project_registry['registered_registry_path'], $project_registry );

        $this->additionalResponseData['log'] = $log;
        $this->updateMainRegistryProjectRecord( $project_registry );

        // Fields to return to frontend.
        return $project_registry;
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
        if ( ! isWritablePath( $this->getMainRegistryFilePath() ) ) {
            $this->error->add( 'projects_registry', 'Projects registry is not writable. Check `path_to_app_working_dir` in the server-config file.' );

            return $fields_data;
        }

        return $fields_data;
    }

    /**
     * Retrieve all projects data. Reads the main projects registry file, then each project registry file, and finally
     * returns an array of project data.
     *
     * @throws JsonException
     */
    public function getAllProjects() : array {
        $projects = [];
        $registry = $this->readMainRegistry();

        if ( empty( $registry['projects'] ) ) {
            return [];
        }

        foreach ( $registry['projects'] as $project_slug => $project_data ) {
            $project = $this->readProjectRegistry( $project_data['registered_registry_path'] ?? '', false );
            if ( ! empty( $project ) ) {
                $projects[] = $project;
            }
        }

        return $projects;
    }

    /**
     * Get project by slug.
     * Reads the main projects registry file, then searches for the project registry file by slug.
     *
     * @param string $slug Project slug.
     *
     * @return array|null Project data or null if not found.
     * @throws JsonException
     */
    public function getProject( string $slug ) : ?array {
        if ( '' === $slug ) {
            return null;
        }

        $registry = $this->readMainRegistry();
        foreach ( $registry['projects'] as $project_slug => $project_data ) {
            if ( $slug !== $project_slug ) {
                continue;
            }

            $project = $this->readProjectRegistry( $project_data['registered_registry_path'] );

            if ( ! empty( $project ) && ! empty( $project['slug'] ) ) {
                return $project;
            }

            return null;
        }

        return null;
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

    private function getAppWorkingDirPath() : string {
        $registry_root_path = config( 'path_to_app_working_dir', '' );

        return normalizePath( trim( (string) $registry_root_path ) );
    }

    private function getMainRegistryFilePath() : string {
        $registry_root_path = $this->getAppWorkingDirPath();
        if ( '' === $registry_root_path ) {
            return '';
        }

        return normalizePath( $registry_root_path . '/' . self::MAIN_PROJECTS_REGISTRY_FILENAME );
    }

    /**
     * Reads the main projects registry file. If the file does not exist, it will try to create it.
     *
     * @param bool $create_default Optional. Create a default registry file if it does not exist.
     *
     * @return array|null Returns the registry data as an associative array, or null if there was an error.
     *                    If no projects are registered, an empty array will be returned.
     * @throws JsonException
     */
    private function readMainRegistry( bool $create_default = true ) : array|null {
        $default_registry = [
            'projects' => [],
        ];

        $registry_path = $this->getMainRegistryFilePath();

        if ( '' === $registry_path ) {
            $this->error->add( 'main_projects_registry', 'Projects registry root path is not configured.' );

            return null;
        }

        // If file does not exist, create it.
        if ( ! file_exists( $registry_path ) ) {
            if ( ! $create_default ) {
                return null;
            }

            $registry_dir = dirname( $registry_path );
            if ( ! is_dir( $registry_dir ) && ! createDirectory( $registry_dir ) ) {
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
                    $default_project_registry = [
                        'registered_registry_path' => $fallback_registry_path,
                    ];

                    if ( false === file_put_contents(
                            $fallback_registry_path,
                            json_encode( $default_project_registry, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ),
                            LOCK_EX ) ) {
                        $this->error->add( 'project_registry', 'Failed to write the default project registry file.' );

                        return null;
                    }

                    return $default_project_registry;
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

    /**
     * @throws JsonException
     */
    private function updateMainRegistry( array $registry ) : void {
        $registry_path = $this->getMainRegistryFilePath();
        if ( '' === $registry_path ) {
            $this->error->add( 'main_projects_registry', 'Projects registry does not exists.' );
        }

        $json = json_encode( $registry, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
        if ( false === file_put_contents( $registry_path, $json, LOCK_EX ) ) {
            $this->error->add( 'main_projects_registry', 'Failed to write the main projects registry file.' );
        }
    }

    /**
     * @throws JsonException
     */
    private function updateMainRegistryProjectRecord( array $project_registry ) : bool {
        $project_registered_root_path = $project_registry['registered_root_path'] ?? null;
        $project_registry_path        = $project_registry['registered_registry_path'] ?? null;

        // Project root path is required to be registered in the main registry, because it's used as a unique identifier for the project.
        if ( empty( $project_registered_root_path ) ) {
            $this->error->add( 'project_root_path', 'Project root path is empty.' );

            return false;
        }

        // Project registry path is required to be registered in the main registry.
        if ( empty( $project_registry_path ) ) {
            $this->error->add( 'project_registry_path', 'Project registry path is empty.' );

            return false;
        }

        $registry = $this->readMainRegistry();

        if ( ! $registry ) {
            return false;
        }

        // Set default values for the project registry.
        if ( ! isset( $registry['projects'] ) || ! is_array( $registry['projects'] ) ) {
            $registry['projects'] = [];
        }

        $project_slug = $project_registry['slug'] ?? '';
        // Not all project registry data should be stored in the main registry.
        // Only the data that is required for listing.
        // So prepare a new array with only the required data.
        $_record = [];

        // Find project record in the registry by project slug.
        // If exists, then update it.
        if ( isset( $registry['projects'][ $project_slug ] ) ) {
            $_record = $registry['projects'][ $project_slug ];
        } else {
            // Find project record in the registry by project registered root path.
            // If exists, then should skip updating because it's already registered by another project.
            $matched_slug = searchInMultiByValue(
                $registry['projects'],
                'registered_root_path',
                $project_registered_root_path
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

        // Update project record with latest data.
        $_record['registered_root_path']     = $project_registered_root_path;
        $_record['registered_registry_path'] = $project_registry_path;

        // Add warnings if happened.
        if ( isset( $project_registry['warnings'] ) && is_array( $project_registry['warnings'] ) ) {
            // Retain existing warnings. Warnings should be removed by resolving each separately.
            $_record['warnings'] = array_merge(
                $_record['warnings'] ?? [],
                $project_registry['warnings']
            );
        }

        // Update status if exists.
        if ( ! empty( $project_registry['status'] ) ) {
            $_record['status'] = $project_registry['status'];
        }

        // Store updated record in the registry array.
        $registry['projects'][ $project_slug ] = $_record;

        $this->updateMainRegistry( $registry );

        return true;
    }

    /**
     * @throws JsonException
     */
    private function updateProjectRegistry( string $registry_path, array $project_registry ) : array|false {
        $existing_registry = $this->readProjectRegistry( $registry_path, true );

        // If $project_registry has `registered_registr_path`, then use it as higher priority.
        $registry_path = $existing_registry['registered_registry_path'] ?? $registry_path;

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

    /**
     * Returns project status based on provided info.
     * If has warnings, then set status 'has_warnings'.
     * If has errors, then set status 'has_errors'.
     * If has both, then set status 'has_warnings_and_errors'.
     * Otherwise, set status 'ok'.
     *
     * @param array $warnings
     * @param array $errors
     *
     * @return string
     */
    private function prepareProjectStatus( array $warnings, array $errors ) : string {
        $has_warnings = ! empty( $warnings );
        $has_errors   = ! empty( $errors );
        if ( $has_warnings && $has_errors ) {
            return 'has_warnings_and_errors';
        }
        if ( $has_warnings ) {
            return 'has_warnings';
        }
        if ( $has_errors ) {
            return 'has_errors';
        }

        return 'active';
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
            $this->error->add( 'vhost', 'Apache virtual hosts directory does not exist. Check the server-config file.' );

            return false;
        }

        $replacements = [
            '{{SERVER_NAME}}'   => $project['domain'],
            '{{DOCUMENT_ROOT}}' => $project['document_root'],
            '{{PROJECT_SLUG}}'  => $project['slug'],
        ];

        // Add replacements.
        foreach ( config( 'vhost_tpl_replacements', [] ) as $key => $value ) {
            $replacements[ '{{' . $key . '}}' ] = $value;
        }

        $vhost_content = strtr( $template_content, $replacements );
        $vhost_path    = normalizePath( $vhosts_dir . '/' . $project['slug'] . '.conf' );

        if ( false === file_put_contents( $vhost_path, $vhost_content, LOCK_EX ) ) {
            $this->error->add( 'vhost', 'Failed to write Apache virtual host file.' );

            return false;
        }

        return true;
    }
}
