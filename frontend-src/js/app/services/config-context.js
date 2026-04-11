/**
 * Shared backend config for the Projects module.
 * Uses React context to provide config data to components.
 * The config is passed from the backend via an API call.
 */

import { createContext, useContext } from 'react';

const defaultConfig = { projects_root_path: '' };

export const AppConfigContext = createContext(defaultConfig);

export const useAppConfig = () => useContext(AppConfigContext);

export const AppConfigDefaultValue = defaultConfig;

