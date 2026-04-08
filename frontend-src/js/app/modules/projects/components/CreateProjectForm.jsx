/**
 * Add project form component.
 */

import React, { useState } from 'react';
import { apiCreateProject } from '../projects-api';
import { useAppConfig } from '../../../services/config-context';
import { useForm } from '../../../services/forms';
import { slugify } from '../../../services/helpers';

const CreateProjectForm = ({ onProjectAdded, onCancel }) => {
    const config = useAppConfig();
    const [pathType, setPathType] = useState('relative');
    const [customPathEnabled, setCustomPathEnabled] = useState(false);
    const [customRelativePath, setCustomRelativePath] = useState('');
    const [customAbsolutePath, setCustomAbsolutePath] = useState('');
    const [slugEdited, setSlugEdited] = useState(false);

    const validate = (values) => {
        const errors = {};

        if (!values.title?.trim()) {
            errors.title = 'Project title is required.';
        }

        if (!values.slug?.trim()) {
            errors.slug = 'Project slug is required.';
        } else if (!/^[a-z0-9]+(?:-[a-z0-9]+)*$/.test(values.slug)) {
            errors.slug = 'Slug may only contain lowercase letters, numbers, and hyphens.';
        }

        if (!values.domain?.trim()) {
            errors.domain = 'Virtual domain name is required.';
        }

        if (!values.client_name?.trim()) {
            errors.client_name = 'Client name is required.';
        }

        if (customPathEnabled) {
            if (pathType === 'relative' && !customRelativePath.trim()) {
                errors.relative_path = 'Relative path is required.';
            } else if (pathType === 'absolute' && !customAbsolutePath.trim()) {
                errors.absolute_path = 'Absolute path is required.';
            }
        }

        return errors;
    };

    const handleSuccess = () => {
        onProjectAdded();
        reset();
        setCustomPathEnabled(false);
        setCustomRelativePath('');
        setCustomAbsolutePath('');
        setPathType('relative');
        setSlugEdited(false);
    };

    const {
        values,
        setValue,
        setMultipleValues,
        handleSubmit,
        isSubmitting,
        errors,
        generalError,
        getFieldProps,
        hasFieldError,
        reset
    } = useForm({
        initialValues: {
            title: '',
            slug: '',
            domain: '',
            client_name: ''
        },
        onSubmit: async (formValues) => {
            const submissionData = {
                ...formValues,
                custom_path_enabled: customPathEnabled,
                path_type: customPathEnabled ? pathType : null,
                relative_path: customPathEnabled ? customRelativePath : null,
                absolute_path: customPathEnabled ? customAbsolutePath : null
            };
            return apiCreateProject(submissionData);
        },
        onSuccess: handleSuccess,
        validate
    });

    const handleTitleChange = (e) => {
        const { value } = e.target;
        const updates = { title: value };

        if (!slugEdited) {
            updates.slug = slugify(value);
        }

        setMultipleValues(updates);
    };

    const handleSlugChange = (e) => {
        setSlugEdited(true);
        setValue('slug', e.target.value);
    };

    const handleCustomPathChange = (setter, field) => (e) => {
        setter(e.target.value);
    };

    const getFinalPath = () => {
        const projectsRoot = config.projects_root_path || '';
        const slug = values.slug || '';

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
                            {...getFieldProps('title')}
                            onChange={handleTitleChange}
                            placeholder="My Awesome Project"
                        />
                        {hasFieldError('title') && <span className="error-message">{errors.title}</span>}
                    </div>
                </div>

                <div className="form-row">
                    <div className="form-group">
                        <label htmlFor="project-slug">Project Slug *</label>
                        <input
                            type="text"
                            id="project-slug"
                            name="slug"
                            value={values.slug}
                            onChange={handleSlugChange}
                            placeholder="my-awesome-project"
                            className={hasFieldError('slug') ? 'error' : ''}
                        />
                        {hasFieldError('slug') && <span className="error-message">{errors.slug}</span>}
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
                                    className={(pathType !== 'relative' ? 'disabled' : '') + (hasFieldError('relative_path') ? ' error' : '')}
                                />
                                {hasFieldError('relative_path') && <span className="error-message">{errors.relative_path}</span>}
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
                                    className={(pathType !== 'absolute' ? 'disabled' : '') + (hasFieldError('absolute_path') ? ' error' : '')}
                                />
                                {hasFieldError('absolute_path') && <span className="error-message">{errors.absolute_path}</span>}
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
                            {...getFieldProps('domain')}
                            placeholder="myproject.local"
                        />
                        {hasFieldError('domain') && <span className="error-message">{errors.domain}</span>}
                    </div>
                </div>

                <div className="form-row">
                    <div className="form-group">
                        <label htmlFor="project-client">Client Name *</label>
                        <input
                            type="text"
                            id="project-client"
                            {...getFieldProps('client_name')}
                            placeholder="Acme Corp"
                        />
                        {hasFieldError('client_name') && <span className="error-message">{errors.client_name}</span>}
                    </div>
                </div>

                {generalError && (
                    <div className="form-error">{generalError}</div>
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
