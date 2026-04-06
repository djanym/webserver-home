/**
 * Main Projects module component.
 */

import React, { useEffect, useState } from 'react';
import ProjectList from './components/ProjectList';
import AddProjectForm from './components/AddProjectForm';
import { apiFetchProjects } from './projects-api';

const ProjectsModule = ({ setHeaderAction }) => {
    const [projects, setProjects] = useState([]);
    const [loadingIndicator, setLoadingIndicator] = useState(true);
    const [error, setError] = useState(null);
    const [showAddForm, setShowAddForm] = useState(false);

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
        loadProjects().then(r => r).catch(e => console.error(e));
    }, []);
    
    // Update header button whenever form visibility changes
    useEffect(() => {
        if (!setHeaderAction) {
            return undefined;
        }
    
        // Set button text and style based on form state
        setHeaderAction({
            label: showAddForm ? 'Cancel' : 'Add Project',
            className: showAddForm ? 'btn-secondary' : 'btn-primary',
            onClick: () => setShowAddForm((prev) => !prev), // Toggle form
        });
    
        // Cleanup: remove button when component unmounts
        return () => {
            setHeaderAction(null);
        };
    }, [setHeaderAction, showAddForm]);
    
    // Add new project to list and close form
    const handleProjectAdded = (newProject) => {
        setProjects((prev) => [...prev, newProject]); // Add to existing projects
        setShowAddForm(false); // Hide the form
    };

    return (
        <section className="projects-module" aria-label="Projects">
            {showAddForm ? (
                <AddProjectForm
                    onProjectAdded={handleProjectAdded}
                    onCancel={() => setShowAddForm(false)}
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
    );
};

export default ProjectsModule;
