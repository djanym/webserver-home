/**
 * Project card component.
 */

import React from 'react';

const ProjectCard = ({ project }) => {
    const { id, name, domain, status, path, created_at } = project;

    const statusClass = status === 'active' ? 'status-active' : 'status-inactive';

    return (
        <div className="project-card" data-project-id={id}>
            <div className="project-card-header">
                <h3 className="project-name">{name}</h3>
                <span className={`project-status ${statusClass}`}>
                    {status}
                </span>
            </div>
            <div className="project-card-body">
                <div className="project-field">
                    <span className="field-label">Domain:</span>
                    <a
                        href={`http://${domain}`}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="field-value project-domain"
                    >
                        {domain}
                    </a>
                </div>
                <div className="project-field">
                    <span className="field-label">Path:</span>
                    <span className="field-value project-path">{path}</span>
                </div>
                <div className="project-field">
                    <span className="field-label">Created:</span>
                    <span className="field-value">{created_at}</span>
                </div>
            </div>
            <div className="project-card-footer">
                <button className="btn btn-secondary btn-sm">
                    Edit
                </button>
                <button className="btn btn-danger btn-sm">
                    Delete
                </button>
            </div>
        </div>
    );
};

export default ProjectCard;
