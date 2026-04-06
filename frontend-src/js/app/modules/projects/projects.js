/**
 * Main Projects module component.
 */

import React, { useEffect, useState } from 'react';
import ProjectList from './components/ProjectList';
import AddProjectForm from './components/AddProjectForm';
import { fetchProjects } from './projects-api';

const ProjectsModule = ({ setHeaderAction }) => {
    const [projects, setProjects] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [showAddForm, setShowAddForm] = useState(false);

    const loadProjects = async () => {
        try {
            setLoading(true);
            const data = await fetchProjects();
            setProjects(data.projects || []);
            setError(null);
        } catch (err) {
            setError('Failed to load projects');
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        loadProjects();
    }, []);

    useEffect(() => {
        if (!setHeaderAction) {
            return undefined;
        }

        setHeaderAction({
            label: showAddForm ? 'Cancel' : 'Add Project',
            className: showAddForm ? 'btn-secondary' : 'btn-primary',
            onClick: () => setShowAddForm((prev) => !prev),
        });

        return () => {
            setHeaderAction(null);
        };
    }, [setHeaderAction, showAddForm]);

    const handleProjectAdded = (newProject) => {
        setProjects((prev) => [...prev, newProject]);
        setShowAddForm(false);
    };

    return (
        <section className="projects-module" aria-label="Projects">
            {showAddForm ? (
                <AddProjectForm
                    onProjectAdded={handleProjectAdded}
                    onCancel={() => setShowAddForm(false)}
                />
            ) : null}

            {loading ? (
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
