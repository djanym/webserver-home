/**
 * Project list component.
 */

import React from 'react';
import ProjectCard from './ProjectCard';

const ProjectList = ({ projects }) => {
    if (!projects || projects.length === 0) {
        return (
            <div className="empty-state">
                <p>No projects found. Create your first project to get started.</p>
            </div>
        );
    }

    return (
        <div className="project-list">
            <h2 className="section-title">Projects ({projects.length})</h2>
            <div className="project-grid">
                {projects.map((project) => (
                    <ProjectCard key={project.id} project={project} />
                ))}
            </div>
        </div>
    );
};

export default ProjectList;
