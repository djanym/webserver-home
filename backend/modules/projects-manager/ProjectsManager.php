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
        'title'       => [
            'always_required',
            'max'           => 60,
            'allowed_chars' => [ 'name_digits' ],
        ],
        'slug'        => [
            'always_required',
            'max'           => 60,
            'allowed_chars' => [ 'slug' ],
        ],
        'domain'      => [
            'always_required',
            'is_domain',
        ],
        'client_name' => [
            'always_required',
            'max'           => 60,
            'allowed_chars' => [ 'name_digits' ],
        ],
        'custom_path_enabled' => [
            'bool',
        ],
        'path_type' => [
            'options' => [ 'relative', 'absolute' ],
        ],
        'relative_path' => [
            'max' => 200,
            'is_path',
        ],
        'absolute_path' => [
            'max' => 200,
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
     * Get all projects.
     *
     * @return array List of projects.
     */
    public function getAllProjects() : array {
        // @todo: Implement project retrieval from server-config.php.
        return [];
    }

    /**
     * Get a single project by ID.
     *
     * @param string $projectId Project ID.
     *
     * @return array|null Project data or null if not found.
     */
    public function getProject( string $projectId ) : ?array {
        // @todo: Implement single project retrieval.
        return null;
    }

    /**
     * Create a new project.
     *
     * @param array $input_data Project data from the form input.
     *
     * @return array|false Created project data.
     */
    public function tryCreateProject( array $input_data ) : array|false {
        // Validate input data first.
//        $errors = $this->validateFormData( $data );
        $input_data['custom_path_enabled'] = 0;

        // Sanitize and validate provided fields data.
        $validated_data = $this->filterValidateAll( $input_data, $this->createProjectFields );

        // Check for errors after validation.
        if ( $this->error->hasErrors() ) {
            return false;
        }

        // Run specific checks for the data.
        $validated_data = $this->filterValidateSpecific( $validated_data );

        // Check for errors after validation.
        if ( $this->error->hasErrors() ) {
            return false;
        }

        $path = $this->serverRoot . '/' . $data['slug'];

        if ( ! empty( $data['custom_path_enabled'] ) ) {
            if ( $data['path_type'] === 'relative' && ! empty( $data['relative_path'] ) ) {
                $path = $this->serverRoot . '/' . $data['relative_path'] . '/' . $data['slug'];
            } elseif ( $data['path_type'] === 'absolute' && ! empty( $data['absolute_path'] ) ) {
                $path = $data['absolute_path'] . '/' . $data['slug'];
            }
        }

        // Normalize path.
        $path = preg_replace( '#/+#', '/', $path );

        $data['path'] = $path;

        // @todo: Implement project creation logic.
        // - Create project folder structure.
        // - Create project-config.php.
        // - Update server-config.php.
        // - Create virtual host configuration.

        return $data;
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
        // @todo: Implement project existence check.
        return false;
    }

    /**
     * Specific validation for specific fields.
     *
     * @param array $fields_data Project data to validate.
     *
     * @return array Array of validation errors, empty if valid.
     */
    public function filterValidateSpecific( array $fields_data ) : array {
        # Check if the project name is already in use.
        # Check if project with the same slug already exists.
        # Check if the project path is already in use. Check depending on the path type.
        # Check if the domain is already in use.
        return $fields_data;
    }
}
