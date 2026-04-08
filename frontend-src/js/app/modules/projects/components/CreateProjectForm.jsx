/**
 * Add project form component.
 */

import React, { useState } from 'react';
import { apiCreateProject } from '../projects-api';
import { useAppConfig } from '../../../services/config-context';

const slugify = (value) =>
    value
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-');

const CreateProjectForm = ({ onProjectAdded, onCancel }) => {
    const config = useAppConfig();
    const [formData, setFormData] = useState({
        title: '',
        slug: '',
        domain: '',
        client_name: '',
    });
    const [pathType, setPathType] = useState('relative'); // 'relative' or 'absolute'
    const [customPathEnabled, setCustomPathEnabled] = useState(false);
    const [customRelativePath, setCustomRelativePath] = useState('');
    const [customAbsolutePath, setCustomAbsolutePath] = useState('');

    // If slug was edited, then don't change it when title changes. If not edited, keep slug in sync with title.
    const [slugEdited, setSlugEdited] = useState(false);
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [errors, setErrors] = useState({});

    // Calculate final path based on form data and config.
    const getFinalPath = () => {
        const projectsRoot = config.projects_root_path || '';
        const slug = formData.slug || '';

        if (!customPathEnabled) {
            return `${projectsRoot}/${slug}`.replace(/\/+/g, '/');
        }

        if (pathType === 'relative') {
            const relPath = customRelativePath.trim();
            return `${projectsRoot}/${relPath}/${slug}`.replace(/\/+/g, '/');
        } else {
            const absPath = customAbsolutePath.trim();
            return `${absPath}/${slug}`.replace(/\/+/g, '/');
        }
    };

    const handleChange = (e) => {
        const { name, value } = e.target;

        setFormData((prev) => {
            const updated = { ...prev, [name]: value };

            if (name === 'title' && !slugEdited) {
                updated.slug = slugify(value);
            }

            return updated;
        });

        if (errors[name] || errors.submit) {
            setErrors((prev) => ({ ...prev, [name]: null, submit: null }));
        }
    };

    const handleSlugChange = (e) => {
        setSlugEdited(true);
        handleChange(e);
    };

    const handleCustomPathChange = (setter, field) => (e) => {
        setter(e.target.value);
        if (errors[field] || errors.submit) {
            setErrors((prev) => ({ ...prev, [field]: null, submit: null }));
        }
    };

    const validate = () => {
        const newErrors = {};

        if (!formData.title.trim()) {
            newErrors.title = 'Project title is required.';
        }

        if (!formData.slug.trim()) {
            newErrors.slug = 'Project slug is required.';
        } else if (!/^[a-z0-9]+(?:-[a-z0-9]+)*$/.test(formData.slug)) {
            newErrors.slug = 'Slug may only contain lowercase letters, numbers, and hyphens.';
        }

        if (!formData.domain.trim()) {
            newErrors.domain = 'Virtual domain name is required.';
        }

        if (!formData.client_name.trim()) {
            newErrors.client_name = 'Client name is required.';
        }

        if (customPathEnabled) {
            if (pathType === 'relative' && !customRelativePath.trim()) {
                newErrors.relative_path = 'Relative path is required.';
            } else if (pathType === 'absolute' && !customAbsolutePath.trim()) {
                newErrors.absolute_path = 'Absolute path is required.';
            }
        }

        return newErrors;
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        const validationErrors = validate();

        if (Object.keys(validationErrors).length > 0) {
            setErrors(validationErrors);
            return;
        }

        setIsSubmitting(true);

        try {
            // Prepare data for submission. Include custom path info if enabled.
            const submissionData = {
                ...formData,
                custom_path_enabled: customPathEnabled,
                path_type: customPathEnabled ? pathType : null,
                relative_path: customPathEnabled ? customRelativePath : null,
                absolute_path: customPathEnabled ? customAbsolutePath : null,
            };

            // Send the data to the backend API.
            const newProject = await apiCreateProject(submissionData);

            onProjectAdded(newProject);
            setFormData({ title: '', slug: '', domain: '', client_name: '' });
            setCustomPathEnabled(false);
            setCustomRelativePath('');
            setCustomAbsolutePath('');
            setPathType('relative');
            setSlugEdited(false);
        } catch (err) {
            const backendValidationErrors = err?.validationErrors;

            if (backendValidationErrors && typeof backendValidationErrors === 'object') {
                setErrors((prev) => ({
                    ...prev,
                    ...backendValidationErrors,
                    submit: err.message || null,
                }));
            } else {
                setErrors({ submit: err.message || 'Failed to create project.' });
            }
        } finally {
            setIsSubmitting(false);
        }
    };

    return (
        <div className="add-project-form-wrapper">
            <h2 className="form-title">Add New Project</h2>
            <form className="add-project-form" onSubmit={handleSubmit}>
                <div className="form-row">
                    <div className="form-group">
                        <label htmlFor="project-title">Project Title *</label>
                        <input
                            type="text"
                            id="project-title"
                            name="title"
                            value={formData.title}
                            onChange={handleChange}
                            placeholder="My Awesome Project"
                            className={errors.title ? 'error' : ''}
                        />
                        {errors.title && <span className="error-message">{errors.title}</span>}
                    </div>
                </div>

                <div className="form-row">
                    <div className="form-group">
                        <label htmlFor="project-slug">Project Slug *</label>
                        <input
                            type="text"
                            id="project-slug"
                            name="slug"
                            value={formData.slug}
                            onChange={handleSlugChange}
                            placeholder="my-awesome-project"
                            className={errors.slug ? 'error' : ''}
                        />
                        {errors.slug && <span className="error-message">{errors.slug}</span>}
                    </div>
                </div>

                <div className="form-row">
                    <div className="form-group">
                        <label>Project files path: </label>
                        <div className="path-preview">
                            <code className="path-value">{getFinalPath()}</code>
                        </div>
                    </div>
                </div>

                <div className="form-row custom-path-toggle-row">
                    <div className="form-group">
                        <div className="switch-wrapper">
                            <label className="switch">
                                <input
                                    type="checkbox"
                                    checked={customPathEnabled}
                                    onChange={(e) => setCustomPathEnabled(e.target.checked)}
                                />
                                <span className="slider round"></span>
                            </label>
                            <span className="switch-label">Enable custom path</span>
                        </div>
                    </div>
                </div>

                {customPathEnabled && (
                    <div className="custom-path-fields">
                        <div className="form-row form-option">
                            <div className="form-group radio-group">
                                <input
                                    type="radio"
                                    id="path-type-relative"
                                    name="pathType"
                                    value="relative"
                                    checked={pathType === 'relative'}
                                    onChange={(e) => setPathType(e.target.value)}
                                />
                                <label htmlFor="path-type-relative">Relative path</label>
                            </div>
                            <div className="form-group">
                                <input
                                    type="text"
                                    name="customRelativePath"
                                    value={customRelativePath}
                                    onChange={handleCustomPathChange(setCustomRelativePath, 'relative_path')}
                                    placeholder="e.g. clients/acme"
                                    disabled={pathType !== 'relative'}
                                    className={(pathType !== 'relative' ? 'disabled' : '') + (errors.relative_path ? ' error' : '')}
                                />
                                {errors.relative_path && <span className="error-message">{errors.relative_path}</span>}
                                <small className="help-text">Relative to: {config.projects_root_path}</small>
                            </div>
                        </div>

                        <div className="form-row form-option">
                            <div className="form-group radio-group">
                                <input
                                    type="radio"
                                    id="path-type-absolute"
                                    name="pathType"
                                    value="absolute"
                                    checked={pathType === 'absolute'}
                                    onChange={(e) => setPathType(e.target.value)}
                                />
                                <label htmlFor="path-type-absolute">Absolute path</label>
                            </div>
                            <div className="form-group">
                                <input
                                    type="text"
                                    name="customAbsolutePath"
                                    value={customAbsolutePath}
                                    onChange={handleCustomPathChange(setCustomAbsolutePath, 'absolute_path')}
                                    placeholder="e.g. /var/www/projects"
                                    disabled={pathType !== 'absolute'}
                                    className={(pathType !== 'absolute' ? 'disabled' : '') + (errors.absolute_path ? ' error' : '')}
                                />
                                {errors.absolute_path && <span className="error-message">{errors.absolute_path}</span>}
                                <small className="help-text">Full system path</small>
                            </div>
                        </div>
                    </div>
                )}

                <div className="form-row">
                    <div className="form-group">
                        <label htmlFor="project-domain">Virtual Domain Name *</label>
                        <input
                            type="text"
                            id="project-domain"
                            name="domain"
                            value={formData.domain}
                            onChange={handleChange}
                            placeholder="myproject.local"
                            className={errors.domain ? 'error' : ''}
                        />
                        {errors.domain && <span className="error-message">{errors.domain}</span>}
                    </div>
                </div>

                <div className="form-row">
                    <div className="form-group">
                        <label htmlFor="project-client">Client Name *</label>
                        <input
                            type="text"
                            id="project-client"
                            name="client_name"
                            value={formData.client_name}
                            onChange={handleChange}
                            placeholder="Acme Corp"
                            className={errors.client_name ? 'error' : ''}
                        />
                        {errors.client_name && <span className="error-message">{errors.client_name}</span>}
                    </div>
                </div>

                {errors.submit && (
                    <div className="form-error">{errors.submit}</div>
                )}

                <div className="form-actions">
                    <button
                        type="button"
                        className="btn btn-secondary"
                        onClick={onCancel}
                        disabled={isSubmitting}
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        className="btn btn-primary"
                        disabled={isSubmitting}
                    >
                        {isSubmitting ? (
                            <>
                                <span className="button-spinner" aria-hidden="true"></span>
                                <span>Creating...</span>
                            </>
                        ) : 'Create Project'}
                    </button>
                </div>
            </form>
        </div>
    );
};

export default CreateProjectForm;
