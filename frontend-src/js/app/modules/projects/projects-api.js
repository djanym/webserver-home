/**
 * Projects module API functions.
 */

import { apiRequest } from '../../services/api';

const projectsRequest = (apiRoute = '', data = null, method = 'POST') => {
    const normalizedRoute = String(apiRoute || '').replace(/^\/+/, '');
    const route = normalizedRoute ? `projects/${normalizedRoute}` : 'projects';

    return apiRequest(route, data, method);
};

export const fetchProjects = async () => {
    const data = await projectsRequest('', null, 'GET');
    return data.data;
};

export const createProject = async (projectData) => {
    const data = await projectsRequest('', projectData);
    return data.data?.project || data.data;
};

export const updateProject = async (projectId, projectData) => {
    const data = await projectsRequest(projectId, projectData, 'PUT');
    return data.data?.project || data.data;
};

export const deleteProject = async (projectId) => {
    const data = await projectsRequest(projectId, null, 'DELETE');
    return data.data;
};
