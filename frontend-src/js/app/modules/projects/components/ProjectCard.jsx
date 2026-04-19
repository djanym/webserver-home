/**
 * Project card component.
 */

import React, { useState } from 'react';

const OpenIcon = () => (
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
        <polyline points="15 3 21 3 21 9"></polyline>
        <line x1="10" y1="14" x2="21" y2="3"></line>
    </svg>
);

const BrowseIcon = () => (
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
        <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
    </svg>
);

const SettingsIcon = () => (
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
        <circle cx="12" cy="12" r="3"></circle>
        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
    </svg>
);

const MoreIcon = () => (
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
        <circle cx="12" cy="12" r="1"></circle>
        <circle cx="19" cy="12" r="1"></circle>
        <circle cx="5" cy="12" r="1"></circle>
    </svg>
);

const ProjectCard = ({ project }) => {
    const { id, title, domain, status, path, client_name } = project;
    const [isExpanded, setIsExpanded] = useState(false);

    const toggleExpand = () => setIsExpanded(!isExpanded);

    const statusClass = status === 'active' ? 'status-active' : (status === 'error' ? 'status-error' : 'status-inactive');

    return (
        <div className={`project-row ${isExpanded ? 'expanded' : ''}`} data-project-id={id}>
            <div className="project-row-main">
                <div className="project-title-col" onClick={toggleExpand}>
                    <h3 className="project-name-text">{title}</h3>
                    <div className="project-secondary-text">{domain}</div>
                </div>
                <div className="project-status-col">
                    <span className={`status-indicator ${statusClass}`} title={status}></span>
                </div>
                <div className="project-actions-col">
                    <a href={`http://${domain}`} target="_blank" rel="noopener noreferrer" className="action-icon-btn" title="Open URL">
                        <OpenIcon />
                    </a>
                    <button className="action-icon-btn" title="Browse Files">
                        <BrowseIcon />
                    </button>
                    <button className="action-icon-btn" title="Settings">
                        <SettingsIcon />
                    </button>
                    <button className="action-icon-btn" title="More">
                        <MoreIcon />
                    </button>
                </div>
            </div>
            {isExpanded && (
                <div className="project-row-details">
                    <div className="details-grid">
                        <div className="detail-item">
                            <span className="detail-label">Domain:</span>
                            <span className="detail-value">{domain}</span>
                        </div>
                        <div className="detail-item">
                            <span className="detail-label">Path:</span>
                            <span className="detail-value">{path}</span>
                        </div>
                        {client_name && (
                            <div className="detail-item">
                                <span className="detail-label">Client:</span>
                                <span className="detail-value">{client_name}</span>
                            </div>
                        )}
                    </div>
                </div>
            )}
        </div>
    );
};

export default ProjectCard;

