/**
 * Projects module API functions.
 */

import { apiRequest } from '../../services/api';

const apiProjectsRequest = (apiRoute = '', data = null, method = 'POST') => {
    const normalizedRoute = String(apiRoute || '').replace(/^\/+/, '');
    const route = normalizedRoute ? `projects/${normalizedRoute}` : 'projects';

    return apiRequest(route, data, method);
};

export const apiFetchProjects = async () => {
    const data = await apiProjectsRequest('', null, 'GET');
    return data.data;
};

export const apiCreateProject = async (projectData) => {
    const data = await apiProjectsRequest('add', projectData);
    return data.data?.project || data.data;
};

export const apiUpdateProject = async (projectId, projectData) => {
    const data = await apiProjectsRequest(projectId, projectData, 'PUT');
    return data.data?.project || data.data;
};

export const apiDeleteProject = async (projectId) => {
    const data = await apiProjectsRequest(projectId, null, 'DELETE');
    return data.data;
};
