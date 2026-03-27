/**
 * API service for communicating with the backend.
 */

const API_BASE_URL = '/backend/api.php';

/**
 * Shared fetch wrapper with response.ok check.
 *
 * @param {string} url
 * @param {RequestInit} options
 * @returns {Promise<Object>}
 */
const apiFetch = async (url, options = {}) => {
    const response = await fetch(url, options);

    if (!response.ok) {
        throw new Error(`Server error: ${response.status} ${response.statusText}`);
    }

    const data = await response.json();

    if (!data.success) {
        throw new Error(data.error || data.message || 'Request failed.');
    }

    return data;
};

/**
 * Fetch all projects.
 *
 * @returns {Promise<Object>} Projects data.
 */
export const fetchProjects = async () => {
    const data = await apiFetch(`${API_BASE_URL}/projects`);
    return data.data;
};

/**
 * Create a new project.
 *
 * @param {Object} projectData - Project data.
 * @returns {Promise<Object>} Created project.
 */
export const createProject = async (projectData) => {
    const data = await apiFetch(`${API_BASE_URL}/projects`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(projectData),
    });
    return data.data;
};

/**
 * Delete a project.
 *
 * @param {string} projectId - Project ID.
 * @returns {Promise<Object>} Response data.
 */
export const deleteProject = async (projectId) => {
    const data = await apiFetch(`${API_BASE_URL}/projects/${projectId}`, {
        method: 'DELETE',
    });
    return data.data;
};
