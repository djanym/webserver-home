/**
 * Shared backend config for the Projects module.
 */

import { createContext, useContext } from 'react';

const defaultConfig = { projects_root_path: '' };

export const ProjectsConfigContext = createContext(defaultConfig);

export const useProjectsConfig = () => useContext(ProjectsConfigContext);

export const projectsConfigDefaultValue = defaultConfig;

