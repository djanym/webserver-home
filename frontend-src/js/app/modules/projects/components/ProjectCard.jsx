import React, { useEffect, useRef, useState } from 'react';
import { apiCreateProjectDbDump, apiDownloadProjectDbDump, apiFetchProjectDbDumps } from '../projects-api';
import { showNotification } from '../../../services/notifications';

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

const DownloadIcon = () => (
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
        <polyline points="7 10 12 15 17 10"></polyline>
        <line x1="12" y1="15" x2="12" y2="3"></line>
    </svg>
);

const DbDumpIcon = () => (
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
        <ellipse cx="12" cy="5" rx="7" ry="3"></ellipse>
        <path d="M5 5v6c0 1.7 3.1 3 7 3s7-1.3 7-3V5"></path>
        <path d="M5 11v6c0 1.7 3.1 3 7 3s7-1.3 7-3v-6"></path>
    </svg>
);

const ErrorIcon = () => (
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
        <circle cx="12" cy="12" r="10"></circle>
        <line x1="15" y1="9" x2="9" y2="15"></line>
        <line x1="9" y1="9" x2="15" y2="15"></line>
    </svg>
);

const normalizeIssueList = (issues) => {
    if (!Array.isArray(issues)) {
        return [];
    }

    return issues
        .map((issue, index) => {
            if (typeof issue === 'string') {
                const message = issue.trim();
                return message ? { id: `issue-${index}`, message } : null;
            }

            if (!issue || typeof issue !== 'object') {
                const message = String(issue || '').trim();
                return message ? { id: `issue-${index}`, message } : null;
            }

            const message = String(issue.message || issue.error || issue.text || '').trim();
            if (!message) {
                return null;
            }

            return {
                id: issue.code ? `${issue.code}-${index}` : `issue-${index}`,
                code: issue.code || null,
                message,
                details: typeof issue.details === 'string' ? issue.details.trim() : (issue.details ? String(issue.details).trim() : ''),
            };
        })
        .filter(Boolean);
};

const getIssueSummary = (count, label) => `${count} ${label}${count === 1 ? '' : 's'}`;

const formatFileSize = (size) => {
    if (!Number.isFinite(size)) {
        return '';
    }

    if (size >= 1024 * 1024) {
        return `${(size / (1024 * 1024)).toFixed(2)} MB`;
    }

    if (size >= 1024) {
        return `${(size / 1024).toFixed(2)} KB`;
    }

    return `${size} B`;
};

const ProjectCard = ({ project, onProjectUpdated }) => {
    const {
        title,
        domain,
        status,
        client_name,
        project_root_path,
        document_root,
        vhost_file,
        created_at,
        updated_at,
        errors,
        db_dumps,
    } = project;
    const [isExpanded, setIsExpanded] = useState(false);
    const [isActionsOpen, setIsActionsOpen] = useState(false);
    const [isDbDumpBusy, setIsDbDumpBusy] = useState(false);
    const [dbDumps, setDbDumps] = useState(Array.isArray(db_dumps) ? db_dumps : []);
    const actionsMenuRef = useRef(null);

    const normalizedErrors = normalizeIssueList(errors);
    const hasIssues = normalizedErrors.length > 0;
    const resolvedStatus = status;
    const statusClass = resolvedStatus === 'active'
        ? 'status-active'
        : (resolvedStatus === 'error' ? 'status-error' : (resolvedStatus === 'warning' ? 'status-warning' : 'status-inactive'));
    const projectKey = project.slug;
    const projectDbDumps = Array.isArray(dbDumps) ? dbDumps : [];

    useEffect(() => {
        const handleOutsideClick = (event) => {
            if (!actionsMenuRef.current) {
                return;
            }

            if (!actionsMenuRef.current.contains(event.target)) {
                setIsActionsOpen(false);
            }
        };

        document.addEventListener('mousedown', handleOutsideClick);

        return () => {
            document.removeEventListener('mousedown', handleOutsideClick);
        };
    }, []);

    useEffect(() => {
        let isMounted = true;

        const loadDbDumps = async () => {
            try {
                const result = await apiFetchProjectDbDumps(projectKey);
                if (isMounted && result && Array.isArray(result.db_dumps)) {
                    setDbDumps(result.db_dumps);
                    return;
                }
            } catch (error) {
                if (isMounted) {
                    setDbDumps(Array.isArray(db_dumps) ? db_dumps : []);
                }
                return;
            }

            if (isMounted) {
                setDbDumps(Array.isArray(db_dumps) ? db_dumps : []);
            }
        };

        loadDbDumps();

        return () => {
            isMounted = false;
        };
    }, [db_dumps, projectKey]);

    const toggleExpand = () => setIsExpanded((prev) => !prev);
    const toggleActionsMenu = () => setIsActionsOpen((prev) => !prev);

    const handleCreateDbDump = async () => {
        setIsDbDumpBusy(true);
        setIsActionsOpen(false);

        try {
            const result = await apiCreateProjectDbDump(projectKey);
            const createdProject = result.project || null;
            const createdDump = result.dump || null;

            if (createdProject && typeof onProjectUpdated === 'function') {
                onProjectUpdated(createdProject);
            }

            if (createdDump) {
                setDbDumps((prev) => [createdDump, ...prev]);
            }

            showNotification(result.message || 'DB dump placeholder created successfully.', 'success');
        } catch (error) {
            const message = error?.payload?.message || error?.message || 'Failed to create DB dump placeholder.';
            showNotification(message, 'error');
        } finally {
            setIsDbDumpBusy(false);
        }
    };

    const handleDownloadDbDump = async (fileName) => {
        if (!fileName) {
            return;
        }

        setIsActionsOpen(false);

        try {
            const fileBlob = await apiDownloadProjectDbDump(projectKey, fileName);
            const downloadUrl = URL.createObjectURL(fileBlob);
            const link = document.createElement('a');

            link.href = downloadUrl;
            link.download = fileName;
            document.body.appendChild(link);
            link.click();
            link.remove();
            URL.revokeObjectURL(downloadUrl);

            showNotification(`DB dump downloaded: ${fileName}`, 'success');
        } catch (error) {
            const message = error?.payload?.message || error?.message || 'Failed to download DB dump.';
            showNotification(message, 'error');
        }
    };

    return (
        <div className={`project-row ${isExpanded ? 'expanded' : ''} ${hasIssues ? 'has-issues' : ''}`} data-project-id={projectKey}>
            <div className="project-row-main">
                <button type="button" className="project-title-col" onClick={toggleExpand} aria-expanded={isExpanded}>
                    <h3 className="project-name-text">{title}</h3>
                    <div className="project-secondary-text">{domain}</div>
                </button>
                <div className="project-status-col">
                    <span className={`status-indicator ${statusClass}`} title={resolvedStatus}></span>
                </div>
                <div className="project-issue-col">
                    {normalizedErrors.length > 0 ? (
                        <button
                            type="button"
                            className="issue-badge error"
                            onClick={toggleExpand}
                            title={`${getIssueSummary(normalizedErrors.length, 'error')} - click to view details`}
                            aria-label={`${getIssueSummary(normalizedErrors.length, 'error')}`}
                        >
                            <ErrorIcon />
                            <span>{normalizedErrors.length}</span>
                        </button>
                    ) : null}
                </div>
                <div className="project-actions-col">
                    <a href={`https://${domain}`} target="_blank" rel="noopener noreferrer" className="action-icon-btn" title="Open URL">
                        <OpenIcon />
                    </a>
                    <button type="button" className="action-icon-btn" title="Browse Files">
                        <BrowseIcon />
                    </button>
                    <button type="button" className="action-icon-btn" title="Settings">
                        <SettingsIcon />
                    </button>
                    <div className="project-actions-menu" ref={actionsMenuRef}>
                        <button
                            type="button"
                            className="action-icon-btn"
                            title="More"
                            aria-haspopup="menu"
                            aria-expanded={isActionsOpen}
                            onClick={toggleActionsMenu}
                        >
                            <MoreIcon />
                        </button>
                        {isActionsOpen && (
                            <div className="project-actions-dropdown" role="menu">
                                <button type="button" className="project-actions-dropdown-item" onClick={handleCreateDbDump} disabled={isDbDumpBusy} role="menuitem">
                                    <DbDumpIcon />
                                    <span>{isDbDumpBusy ? 'Creating DB dump...' : 'Create DB dump'}</span>
                                </button>
                                {projectDbDumps.length > 0 && (
                                    <div className="project-actions-dropdown-group">
                                        <div className="project-actions-dropdown-group-title">Download DB dump</div>
                                        {projectDbDumps.map((dump) => (
                                            <button
                                                key={dump.file_name}
                                                type="button"
                                                className="project-actions-dropdown-item"
                                                onClick={() => handleDownloadDbDump(dump.file_name)}
                                                role="menuitem"
                                            >
                                                <DownloadIcon />
                                                <span>{dump.file_name}</span>
                                            </button>
                                        ))}
                                    </div>
                                )}
                            </div>
                        )}
                    </div>
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
                            <span className="detail-label">Project root:</span>
                            <span className="detail-value">{project_root_path || project.registered_root_path || ''}</span>
                        </div>
                        <div className="detail-item">
                            <span className="detail-label">Document root:</span>
                            <span className="detail-value">{document_root || ''}</span>
                        </div>
                        <div className="detail-item">
                            <span className="detail-label">Vhost file:</span>
                            <span className="detail-value">{vhost_file || ''}</span>
                        </div>
                        {client_name && (
                            <div className="detail-item">
                                <span className="detail-label">Client:</span>
                                <span className="detail-value">{client_name}</span>
                            </div>
                        )}
                        {created_at && (
                            <div className="detail-item">
                                <span className="detail-label">Created:</span>
                                <span className="detail-value">{created_at}</span>
                            </div>
                        )}
                        {updated_at && (
                            <div className="detail-item">
                                <span className="detail-label">Updated:</span>
                                <span className="detail-value">{updated_at}</span>
                            </div>
                        )}
                    </div>

                    {normalizedErrors.length > 0 && (
                        <div className="project-issue-section error-section">
                            <h4 className="issue-section-title">
                                <ErrorIcon />
                                Errors
                            </h4>
                            <ul className="issue-list">
                                {normalizedErrors.map((issue) => (
                                    <li key={issue.id} className="issue-list-item">
                                        {issue.code ? <div className="issue-code">{issue.code}</div> : null}
                                        <div className="issue-message">{issue.message}</div>
                                        {issue.details ? <div className="issue-details">{issue.details}</div> : null}
                                    </li>
                                ))}
                            </ul>
                        </div>
                    )}

                    {projectDbDumps.length > 0 && (
                        <div className="project-issue-section db-dump-section">
                            <h4 className="issue-section-title">
                                <DbDumpIcon />
                                DB dumps
                            </h4>
                            <ul className="db-dump-list">
                                {projectDbDumps.map((dump) => (
                                    <li key={dump.file_name} className="db-dump-list-item">
                                        <div className="db-dump-list-main">
                                            <div className="db-dump-file-name">{dump.file_name}</div>
                                            <div className="db-dump-file-meta">
                                                {dump.created_at || ''}
                                                {dump.size ? ` • ${formatFileSize(Number(dump.size))}` : ''}
                                            </div>
                                        </div>
                                        <button
                                            type="button"
                                            className="action-icon-btn db-dump-download-btn"
                                            onClick={() => handleDownloadDbDump(dump.file_name)}
                                            title="Download dump"
                                        >
                                            <DownloadIcon />
                                        </button>
                                    </li>
                                ))}
                            </ul>
                        </div>
                    )}
                </div>
            )}
        </div>
    );
};

export default ProjectCard;
