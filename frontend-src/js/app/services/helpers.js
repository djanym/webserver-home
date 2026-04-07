/**
 * Shared backend config for the Projects module.
 */

import { createContext, useContext } from 'react';

const defaultConfig = { projects_root_path: '' };

export const AppConfigContext = createContext(defaultConfig);

export const useAppConfig = () => useContext(AppConfigContext);

export const AppConfigDefaultValue = defaultConfig;

