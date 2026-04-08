/**
 * API service for communicating with the backend.
 */

let API_BASE_URL;
let apiConfigPromise;

const renderConfigError = (message) => {
    document.body.innerHTML = `<div style="padding: 2rem; text-align: center; color: #dc2626;"><h1>Configuration Error</h1><p>${message}</p></div>`;
};

/**
 * Loads application configuration data from the config file.
 */
const loadConfig = async () => {
    const response = await fetch('app-public-config.json');
    return response.json();
};

const initApiConfig = async () => {
    if (API_BASE_URL) {
        return API_BASE_URL;
    }

    if (!apiConfigPromise) {
        apiConfigPromise = loadConfig()
            .then((config) => {
                if (!config.apiBaseUrl) {
                    throw new Error('API Base URL is not configured. Please check app-public-config.json.');
                }

                API_BASE_URL = String(config.apiBaseUrl).replace(/\/+$/, '');
                return API_BASE_URL;
            })
            .catch((error) => {
                console.error('FATAL: Failed to load configuration or apiBaseUrl is missing', error);
                renderConfigError(error.message || 'Failed to load configuration or API Base URL is missing.');
                throw error;
            });
    }

    return apiConfigPromise;
};

const resolveApiUrl = (apiRoute) => {
    const normalizedRoute = String(apiRoute || '').replace(/^\/+/, '');
    return `${API_BASE_URL}/${normalizedRoute}`;
};

const buildApiError = (message, payload = null, status = null) => {
    const error = new Error(message || 'Request failed.');

    if (status !== null) {
        error.status = status;
    }

    if (payload) {
        error.payload = payload;
    }

    const validationErrors = payload?.data?.errors;

    if (validationErrors && typeof validationErrors === 'object') {
        error.validationErrors = validationErrors;
    }

    return error;
};

/**
 * Shared fetch wrapper with strict response handling.
 *
 * @param {string} apiRoute
 * @param {Object|null} data
 * @param {string} method
 * @param {RequestInit} options
 * @returns {Promise<Object>}
 */
export const apiRequest = async (apiRoute, data = null, method = 'GET', options = {}) => {
    await initApiConfig();

    const requestMethod = String(method || 'POST').toUpperCase();
    const requestOptions = {
        method: requestMethod,
        ...options,
        headers: {
            ...(requestMethod !== 'GET' ? { 'Content-Type': 'application/json' } : {}),
            ...(options.headers || {}),
        },
    };

    if (data !== null && requestMethod !== 'GET') {
        requestOptions.body = JSON.stringify(data);
    }

    const response = await fetch(resolveApiUrl(apiRoute), requestOptions);
    let payload = null;

    try {
        payload = await response.json();
    } catch (error) {
        payload = null;
    }

    if (!response.ok) {
        const message = payload?.error || payload?.message || `Server error: ${response.status} ${response.statusText}`;
        throw buildApiError(message, payload, response.status);
    }

    if (payload && payload.success === false) {
        throw buildApiError(payload.error || payload.message || 'Request failed.', payload, response.status);
    }

    return payload;
};

const apiProjectsRequest = (apiRoute = '', data = null, method = 'POST') => {
    const normalizedRoute = String(apiRoute || '').replace(/^\/+/, '');
    const route = normalizedRoute ? `projects/${normalizedRoute}` : 'projects';

    return apiRequest(route, data, method);
};

export const apiFetchBackendConfig = async () => {
    const data = await apiRequest('public-config', null, 'GET');
    return data.data?.config || data.data;
};
