/**
 * Main App component for Webserver Home Manager.
 */

import React, { useState, useEffect } from 'react';
import ProjectList from './ProjectList';
import AddProjectForm from './AddProjectForm';
import { fetchProjects } from '../services/api';

const App = () => {
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

    const handleProjectAdded = (newProject) => {
        setProjects([...projects, newProject]);
        setShowAddForm(false);
    };

    return (
        <div className="app">
            <header className="app-header">
                <div className="container">
                    <h1 className="app-title">Webserver Home Manager</h1>
                    <button
                        className="btn btn-primary"
                        onClick={() => setShowAddForm(!showAddForm)}
                    >
                        {showAddForm ? 'Cancel' : 'Add Project'}
                    </button>
                </div>
            </header>

            <main className="app-main">
                <div className="container">
                    {showAddForm && (
                        <AddProjectForm
                            onProjectAdded={handleProjectAdded}
                            onCancel={() => setShowAddForm(false)}
                        />
                    )}

                    {loading ? (
                        <div className="loading">Loading projects...</div>
                    ) : error ? (
                        <div className="error">{error}</div>
                    ) : (
                        <ProjectList projects={projects} />
                    )}
                </div>
            </main>
        </div>
    );
};

export default App;
