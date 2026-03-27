<?php
/**
 * Project Manager class for handling project operations.
 *
 * This class provides methods for creating, reading, updating, and deleting projects.
 */

namespace WebserverHome;

/**
 * Class ProjectManager
 *
 * @package WebserverHome
 */
class ProjectManager
{
    /**
     * Path to the server configuration file.
     *
     * @var string
     */
    private string $configPath;

    /**
     * Path to the server root directory.
     *
     * @var string
     */
    private string $serverRoot;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->serverRoot = config('home_path', dirname(__DIR__, 2));
        $this->configPath = ABSPATH . '/config/projects.meta.json';
    }

    /**
     * Get all projects.
     *
     * @return array List of projects.
     */
    public function getAllProjects(): array
    {
        // @todo: Implement project retrieval from server-config.php.
        return [];
    }

    /**
     * Get a single project by ID.
     *
     * @param string $projectId Project ID.
     * @return array|null Project data or null if not found.
     */
    public function getProject(string $projectId): ?array
    {
        // @todo: Implement single project retrieval.
        return null;
    }

    /**
     * Create a new project.
     *
     * @param array $data Project data including name, domain, folder_name.
     * @return array Created project data.
     */
    public function createProject(array $data): array
    {
        // @todo: Implement project creation logic.
        // - Validate input data.
        // - Create project folder structure.
        // - Create project-config.php.
        // - Update server-config.php.
        // - Create virtual host configuration.
        return [];
    }

    /**
     * Update an existing project.
     *
     * @param string $projectId Project ID.
     * @param array $data Updated project data.
     * @return array Updated project data.
     */
    public function updateProject(string $projectId, array $data): array
    {
        // @todo: Implement project update logic.
        return [];
    }

    /**
     * Delete a project.
     *
     * @param string $projectId Project ID.
     * @return bool True if deleted successfully.
     */
    public function deleteProject(string $projectId): bool
    {
        // @todo: Implement project deletion logic.
        return false;
    }

    /**
     * Check if a project exists.
     *
     * @param string $projectId Project ID.
     * @return bool True if project exists.
     */
    public function projectExists(string $projectId): bool
    {
        // @todo: Implement project existence check.
        return false;
    }

    /**
     * Validate project data.
     *
     * @param array $data Project data to validate.
     * @return array Array of validation errors, empty if valid.
     */
    public function validateProjectData(array $data): array
    {
        $errors = [];

        if (empty($data['title'])) {
            $errors['title'] = 'Project title is required.';
        }

        if (empty($data['slug'])) {
            $errors['slug'] = 'Project slug is required.';
        } elseif (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $data['slug'])) {
            $errors['slug'] = 'Slug may only contain lowercase letters, numbers, and hyphens.';
        }

        if (empty($data['domain'])) {
            $errors['domain'] = 'Virtual domain name is required.';
        }

        if (empty($data['client_name'])) {
            $errors['client_name'] = 'Client name is required.';
        }

        return $errors;
    }
}
