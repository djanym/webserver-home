/**
 * API service for communicating with the backend.
 */

const API_BASE_URL = '/backend/api.php';

/**
 * Fetch all projects.
 *
 * @returns {Promise<Object>} Projects data.
 */
export const fetchProjects = async () => {
    const response = await fetch(`${API_BASE_URL}/projects`);
    const data = await response.json();

    if (!data.success) {
        throw new Error(data.message || 'Failed to fetch projects');
    }

    return data.data;
};

/**
 * Create a new project.
 *
 * @param {Object} projectData - Project data.
 * @returns {Promise<Object>} Created project.
 */
export const createProject = async (projectData) => {
    const response = await fetch(`${API_BASE_URL}/projects`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(projectData),
    });

    const data = await response.json();

    if (!data.success) {
        throw new Error(data.message || 'Failed to create project');
    }

    return data.data;
};

/**
 * Delete a project.
 *
 * @param {string} projectId - Project ID.
 * @returns {Promise<Object>} Response data.
 */
export const deleteProject = async (projectId) => {
    const response = await fetch(`${API_BASE_URL}/projects/${projectId}`, {
        method: 'DELETE',
    });

    const data = await response.json();

    if (!data.success) {
        throw new Error(data.message || 'Failed to delete project');
    }

    return data.data;
};
