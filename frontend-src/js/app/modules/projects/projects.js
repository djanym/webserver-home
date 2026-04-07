/**
 * Main Projects module component.
 */

import React, { useEffect, useState } from 'react';
import ProjectList from './components/ProjectList';
import CreateProjectForm from './components/CreateProjectForm';
import { apiFetchBackendConfig } from '../../services/api';
import { apiFetchProjects } from './projects-api';
import { ProjectsConfigContext, projectsConfigDefaultValue } from './projects-config-context';

const ProjectsModule = ({ setHeaderAction }) => {
    const [projects, setProjects] = useState([]);
    const [backendConfig, setBackendConfig] = useState(projectsConfigDefaultValue);
    const [loadingIndicator, setLoadingIndicator] = useState(true);
    const [error, setError] = useState(null);
    const [isCreateProjectFormOpen, toggleCreateProjectForm] = useState(false);

    // Fetch backend config data.
    const loadBackendConfig = async () => {
        try {
            const data = await apiFetchBackendConfig();
            if (data) {
                setBackendConfig(data);
            }
        } catch (err) {
            setError('Failed to load backend configuration'); // Show error message.
        } finally {
            setLoadingIndicator(false);
        }
    };

    // Fetch projects from API and update state.
    const loadProjects = async () => {
        try {
            setLoadingIndicator(true);
            const data = await apiFetchProjects();
            setProjects(data.projects || []); // Store projects in state.
            setError(null); // Clear any previous errors.
        } catch (err) {
            setError('Failed to load projects'); // Show error message.
        } finally {
            setLoadingIndicator(false);
        }
    };

    // Run once when component mounts - load initial projects.
    useEffect(() => {
        loadBackendConfig().then(r => r).catch(e => console.error(e));
        loadProjects().then(r => r).catch(e => console.error(e));
    }, []);
    
    // Update header button whenever form visibility changes
    useEffect(() => {
        if (!setHeaderAction) {
            return undefined;
        }
    
        // Set button text and style based on form state
        setHeaderAction({
            label: isCreateProjectFormOpen ? 'Cancel' : 'Add Project',
            className: isCreateProjectFormOpen ? 'btn-secondary' : 'btn-primary',
            onClick: () => toggleCreateProjectForm((prev) => !prev), // Toggle form
        });
    
        // Cleanup: remove button when component unmounts
        return () => {
            setHeaderAction(null);
        };
    }, [setHeaderAction, isCreateProjectFormOpen]);
    
    // Add new project to list and close form
    const handleProjectAdded = (newProject) => {
        setProjects((prev) => [...prev, newProject]); // Add to existing projects
        toggleCreateProjectForm(false); // Hide the form
    };

    return (
        <ProjectsConfigContext.Provider value={backendConfig}>
            <section className="projects-module" aria-label="Projects">
                {isCreateProjectFormOpen ? (
                    <CreateProjectForm
                        onProjectAdded={handleProjectAdded}
                        onCancel={() => toggleCreateProjectForm(false)}
                    />
                ) : null}

                {loadingIndicator ? (
                    <div className="loading">Loading projects...</div>
                ) : error ? (
                    <div className="error">{error}</div>
                ) : (
                    <ProjectList projects={projects} />
                )}
            </section>
        </ProjectsConfigContext.Provider>
    );
};

export default ProjectsModule;
