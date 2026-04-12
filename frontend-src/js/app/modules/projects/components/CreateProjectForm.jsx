/**
 * Add project form component.
 */

import React, { useState } from 'react';
import { apiCreateProject } from '../projects-api';
import { useAppConfig } from '../../../services/config-context';
import { formFn } from '../../../services/forms';
import { slugify } from '../../../services/helpers';

const CreateProjectForm = ({ onProjectAdded, onCancel }) => {
    // App configuration context from the app backend.
    const config = useAppConfig();
    // Flag to track if slug was manually edited by the user.
    const [wasSlugEdited, setSlugEdited] = useState(false);
    // Constants related to the path selection
    const [pathType, setPathType] = useState('relative'); // Project path type switch.
    const [customPathEnabled, setCustomPathEnabled] = useState(false);
    const [customRelativePath, setCustomRelativePath] = useState('');
    const [customAbsolutePath, setCustomAbsolutePath] = useState('');

    // Validation rules for frontend only.
    const projectValidationRules = {
        title: [{ rule: 'required', message: 'Project title is required.' }],
        slug: [
            { rule: 'required', message: 'Project slug is required.' },
            { rule: 'isSlug', message: 'Slug may only contain lowercase letters, numbers, and hyphens.' }
        ],
        domain: [{ rule: 'required', message: 'Virtual domain name is required.' }],
        client_name: [{ rule: 'required', message: 'Client name is required.' }],
        relative_path: [{
            rule: 'required',
            message: 'Relative path is required.',
            when: (allValues) => allValues.custom_path_enabled && allValues.path_type === 'relative'
        }],
        absolute_path: [{
            rule: 'required',
            message: 'Absolute path is required.',
            when: (allValues) => allValues.custom_path_enabled && allValues.path_type === 'absolute'
        }]
    };

    // What happens if backend returns success.
    const handleSuccess = () => {
        alert('Project created successfully!');
        onProjectAdded();
        reset();
        setCustomPathEnabled(false);
        // setCustomRelativePath('');
        // setCustomAbsolutePath('');
        setPathType('relative');
        setSlugEdited(false);
    };

    const {
        values,
        setValue,
        setMultipleValues,
        isSubmitting,
        generalError,
        initFormFn,
        getFieldProps,
        clearFieldError,
            // @todo: remove renderFieldError and dynamically insert the error element.
        renderFieldError,
        reset
    } = formFn({
        // initialValues: {
        //     title: '',
        //     slug: '',
        //     domain: '',
        //     client_name: ''
        // },
        validationRules: projectValidationRules,
        // extraValidationValuesCb: () => ({
        //     custom_path_enabled: customPathEnabled,
        //     path_type: pathType,
        //     relative_path: customRelativePath,
        //     absolute_path: customAbsolutePath
        // }),
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
        onSuccess: handleSuccess
    });

    // If title was changed, then slug should be updated as well.
    const handleTitleChange = (e) => {
        const { value } = e.target;
        const updates = { title: value };

        // In case slug was manually updated, then skip sync with title.
        if (!wasSlugEdited) {
            updates.slug = slugify(value);
            clearFieldError('slug');
        }

        // setMultipleValues(updates);
        // Directly change element value:
        setValue('slug', slugify(value));
    };

    // If slug was changed manually, then don't sync it with title.
    const handleSlugChange = (e) => {
        setSlugEdited(true);
        setValue('slug', e.target.value);
    };

    // When custom path fields are changed, we need to clear their errors as well, so we pass the field name to clear in the handler.
    const handleCustomPathChange = (setter, fieldName) => (e) => {
        setter(e.target.value);
        clearFieldError(fieldName);
    };

    // Used just for showing the final path in the form. The actual path will be determined in the backend based on the submitted data.
    const getFinalPath = () => {
        const projectsRoot = config.projects_root_path || '';
        const slug = values.slug || '';

        if (!customPathEnabled) {
            return `${projectsRoot}/${slug}`.replace(/\/+/g, '/');
        }

        if (pathType === 'relative') {
            const relPath = customRelativePath.trim();
            return `${projectsRoot}/${relPath}/${slug}`.replace(/\/+/g, '/');
        }

        const absPath = customAbsolutePath.trim();
        return `${absPath}/${slug}`.replace(/\/+/g, '/');
    };

    return (
        <div className="add-project-form-wrapper">
            <h2 className="form-title">Add New Project</h2>
            <form {...initFormFn({ className: 'add-project-form' })}>
                <div className="form-row">
                    <div className="form-group">
                        <label htmlFor="project-title">Project Title *</label>
                        <input
                            type="text"
                            id="project-title"
                            {...getFieldProps('title', {
                                onChange: handleTitleChange
                            })}
                            placeholder="Unique Project Title"
                        />
                        {renderFieldError('title')}
                    </div>
                </div>

                <div className="form-row">
                    <div className="form-group">
                        <label htmlFor="project-slug">Project Slug *</label>
                        <input
                            type="text"
                            id="project-slug"
                            {...getFieldProps('slug', {
                                validationRules: 'required,isSlug',
                                onChange: handleSlugChange
                            })}
                            placeholder="my-awesome-project"
                        />
                        {renderFieldError('slug')}
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
                                    onChange={(e) => {
                                        const isEnabled = e.target.checked;
                                        setCustomPathEnabled(isEnabled);

                                        if (!isEnabled) {
                                            clearFieldError('relative_path');
                                            clearFieldError('absolute_path');
                                        }
                                    }}
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
                                    onChange={(e) => {
                                        setPathType(e.target.value);
                                        clearFieldError('absolute_path');
                                    }}
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
                                    className={pathType !== 'relative' ? 'disabled' : ''}
                                />
                                {renderFieldError('relative_path')}
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
                                    onChange={(e) => {
                                        setPathType(e.target.value);
                                        clearFieldError('relative_path');
                                    }}
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
                                    className={pathType !== 'absolute' ? 'disabled' : ''}
                                />
                                {renderFieldError('absolute_path')}
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
                            {...getFieldProps('domain', { validationRules: 'required' })}
                            placeholder="myproject.local"
                        />
                        {renderFieldError('domain')}
                    </div>
                </div>

                <div className="form-row">
                    <div className="form-group">
                        <label htmlFor="project-client">Client Name *</label>
                        <input
                            type="text"
                            id="project-client"
                            {...getFieldProps('client_name', { validationRules: 'required' })}
                            placeholder="Acme Corp"
                        />
                        {renderFieldError('client_name')}
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
