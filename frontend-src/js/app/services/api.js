/**
 * API service for communicating with the backend.
 */

// Initialize API base URL.
let API_BASE_URL;

/**
 * Loads application configuration data from the config file.
 */
const loadConfig = async () => {
    // const response = await fetch('../../../app-config.json');
    const response = await fetch('app-public-config.json');
    return await response.json();
};

loadConfig().then(config => {
    if (!config.apiBaseUrl) {
        console.error('FATAL: apiBaseUrl is not configured in app-config.json');
        document.body.innerHTML = '<div style="padding: 2rem; text-align: center; color: #dc2626;"><h1>Configuration Error</h1><p>API Base URL is not configured. Please check app-config.json.</p></div>';
        throw new Error('apiBaseUrl is required in configuration');
    }
    API_BASE_URL = config.apiBaseUrl;
}).catch((error) => {
    console.error('FATAL: Failed to load configuration or apiBaseUrl is missing', error);
    document.body.innerHTML = '<div style="padding: 2rem; text-align: center; color: #dc2626;"><h1>Configuration Error</h1><p>Failed to load configuration or API Base URL is missing.</p></div>';
    throw error;
});

/**
 * Shared fetch wrapper with response.ok check.
 *
 * @param {string} api_route
 * @param {RequestInit} options
 * @returns {Promise<Object>}
 */
const apiFetch = async (api_route, options = {}) => {
    const url = `${API_BASE_URL}/${api_route}`;
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
    const data = await apiFetch(`projects`);
    return data.data;
};

/**
 * Create a new project.
 *
 * @param {Object} projectData - Project data.
 * @returns {Promise<Object>} Created project.
 */
export const createProject = async (projectData) => {
    const data = await apiFetch(`projects`, {
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
