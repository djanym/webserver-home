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
            'max'           => 1,
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
        // @llm-agent-task: finalize filterValidateSpecific() function.
        $validated_data = $this->filterValidateSpecific( $validated_data );

        // Check for errors after validation.
        if ( $this->error->hasErrors() ) {
            return false;
        }

        // @llm-agent-task: Implement project creation logic.
        $project = $this->createProject( $validated_data );

        return $project;
    }

    private function createProject( array $data ) {
        /**
         * @llm-agent-task: create function for adding project to the list of projects. something like addProject().
         * App main projects info should be stored in the .webserver-home/projects-info.json file.
         * The file should be in the following format:
         *                {
         * "lastUpdated": "2025-06-25T00:37:00.208Z",
         * "projects": []
         * }
         *                projects info should contain only the list of path to project root folder.
         *                project root folder contains project-info.json file.
         * /

        // ---

        /**
         * @llm-agent-task: Create function for creating project root folder.
         *                Creating project-info.json file inside the project root folder.
         *                Adding all neessary info to the project-info.json file.
         *                Creating initial folder structure based on the path provided.
         *                Initial folder structure is set in the app-config.php file in the project_folders_structure key.
         *                The keys are reserved names. Values are the names of the folders. `{slug}` is placeholder for the project slug.
         *                'docs' for the project documentation.
         *                'www' for the project website files. will be used as a directory root for appache config.
         *                'db-dump' for the database dumps.
         *                But the folder structure should be stored in the project-info.json file. Because maybe the initial folder structure will be changed in the future. So to avoid any issues, the folder structure should be stored in the project-info.json file.
         */

        /**
         * @llm-agent-task: Create function for creating apache virtual host configuration file.
         *                the path is in the server-config.php file in the path_to_apache_vhosts_dir key.
         *                the vhosts file sample is in server-config.php file in the path_to_vhost_tpl_file key.
         *                the vhosts file name should be in the following format: {project_slug}.conf
         *                the vhosts file has placeholders whoch should be replaced.
         */
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
        // @llm-agent-task: Check if the project name is already in use.
        // @llm-agent-task: Check if project with the same slug already exists.
        // @llm-agent-task: Check if the project path is already in use. Check depending on the path type.
        // @llm-agent-task: Check if the domain is already in use.
        // @llm-agent-task: Check if the custom path is already in use.
        return $fields_data;
    }
}
