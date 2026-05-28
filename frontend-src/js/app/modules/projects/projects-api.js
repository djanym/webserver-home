/**
 * Projects module API functions.
 */

import { apiRequest } from '../../services/api';

const apiProjectsRequest = (apiRoute = '', data = null, method = 'POST') => {
    const normalizedRoute = String(apiRoute || '').replace(/^\/+/, '');
    const route = normalizedRoute ? `projects/${normalizedRoute}` : 'projects';

    return apiRequest(route, data, method);
};

const apiProjectDbDumpRequest = (projectId, apiRoute = '', data = null, method = 'POST', options = {}) => {
    const normalizedId = String(projectId || '').replace(/^\/+|\/+$/g, '');
    const normalizedRoute = String(apiRoute || '').replace(/^\/+/, '');
    const route = normalizedRoute ? `projects/${normalizedId}/db-dump/${normalizedRoute}` : `projects/${normalizedId}/db-dump`;

    return apiRequest(route, data, method, options);
};

export const apiFetchProjects = async () => {
    const data = await apiProjectsRequest('', null, 'GET');
    return data.data || data;
};

export const apiCreateProject = async (projectData) => {
    const data = await apiProjectsRequest('add', projectData);
    return data.data || data;
};

export const apiUpdateProject = async (projectId, projectData) => {
    const data = await apiProjectsRequest(projectId, projectData, 'PUT');
    return data.data?.project || data.data;
};

export const apiDeleteProject = async (projectId) => {
    const data = await apiProjectsRequest(projectId, null, 'DELETE');
    return data.data;
};

export const apiCreateProjectDbDump = async (projectId) => {
    const data = await apiProjectDbDumpRequest(projectId, 'create', null, 'POST');
    return data.data || data;
};

export const apiFetchProjectDbDumps = async (projectId) => {
    const data = await apiProjectDbDumpRequest(projectId, '', null, 'GET');
    return data.data || data;
};

export const apiDownloadProjectDbDump = async (projectId, fileName) => {
    return apiProjectDbDumpRequest(projectId, `${encodeURIComponent(fileName)}/download`, null, 'GET', { responseType: 'blob' });
};
