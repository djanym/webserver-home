/**
 * Add project form component.
 */

import React, { useState } from 'react';
import { apiCreateProject } from '../projects-api';
import { useAppConfig } from '../../../services/config-context';
import { formFn } from '../../../services/forms';
import { slugify } from '../../../services/helpers';
import ManagedForm from '../../../components/ManagedForm';
import FormResponse from '../../../components/FormResponse';
import { showNotification } from '../../../services/notifications';

const CreateProjectForm = ({ onProjectAdded, onCancel }) => {
    // App configuration context from the app backend.
    const config = useAppConfig();
    // Flag to track if slug was manually edited by the user.
    const [wasSlugEdited, setSlugEdited] = useState(false);

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
    const handleSuccess = (result = {}) => {
        const createdProject = result.project || null;
        // const issues = normalizeBackendIssues(result.errors || []);
        // In case of success, error still can happen,
        // and these errors are not fields related, so we need to show error notifications.
        const errors = result.errors || [];

        console.log('errors', errors);

        // If there is a `message` field in the response. We should show it.
        if (result.message && typeof result.message === 'string' && result.message.trim().length > 0) {
            showNotification(
                    result.message,
                    errors.length > 0 ? 'warning' : 'success',
            );
        }

        // Go through all issues and show them as notifications.
        errors.forEach((error) => {
            console.log('error', error);
            const { code, message } = error;
            const notificationMessage = `(${code}) ${message}`;
            showNotification(notificationMessage, 'error');
        });

        // If there is `log` records, then show them as one multi-line notification.
        if (Array.isArray(result.log) && result.log.length > 0) {
            const logMessages = result.log.map((logItem) => {
                const { level, message } = logItem;
                return `${level+1}: ${message}`;
            });
            showNotification(logMessages.join('<br/>\n'), 'info');
        }

        // If there was passed on project added callback.
        if (typeof onProjectAdded === 'function') {
            onProjectAdded(createdProject);
        }

        reset();
        setSlugEdited(false);
    };

    const form = formFn({
        validationRules: projectValidationRules,
        onSubmit: async (formValues) => {
            // Explicit conversion to boolean to handle empty string values.
            const customPathEnabled = !!formValues.custom_path_enabled;
            const pathType = formValues.path_type || 'relative';

            const submissionData = {
                ...formValues,
                custom_path_enabled: customPathEnabled,
                path_type: customPathEnabled ? pathType : null,
                relative_path: customPathEnabled ? (formValues.relative_path || null) : null,
                absolute_path: customPathEnabled ? (formValues.absolute_path || null) : null
            };

            return apiCreateProject(submissionData);
        },
        onSuccess: handleSuccess
    });

    const {
        values,
        setValue,
        setMultipleValues,
        FormField,
        FormActions,
        responseMessage,
        responseType,
        responseErrors,
        clearFieldError,
        reset
    } = form;

    const customPathEnabled = !!values.custom_path_enabled;
    const pathType = values.path_type || 'relative';
    const customRelativePath = values.relative_path || '';
    const customAbsolutePath = values.absolute_path || '';

    // If title was changed, then slug should be updated as well.
    const handleTitleChange = (e) => {
        const { value } = e.target;
        const updates = { title: value };

        // In case slug was manually updated, then skip sync with title.
        if (!wasSlugEdited) {
            updates.slug = slugify(value);
            clearFieldError('slug');
        }

        setMultipleValues(updates);
    };

    // If slug was changed manually, then don't sync it with title.
    const handleSlugChange = (e) => {
        setSlugEdited(true);
        setValue('slug', e.target.value);
    };

    // When custom path fields are changed,
    // we need to clear their errors as well,
    // so we pass the field name to clear in the handler.
    const handleCustomPathChange = (fieldName) => (e) => {
        setValue(fieldName, e.target.value);
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
            <ManagedForm form={form} className="add-project-form">
                <div className="form-row">
                    <div className="form-group">
                        <label htmlFor="project-title">Project Title *</label>
                        <FormField name="title" rules="required" onChange={handleTitleChange}>
                            <input
                                type="text"
                                id="project-title"
                                placeholder="Unique Project Title"
                            />
                        </FormField>
                    </div>
                </div>

                <div className="form-row">
                    <div className="form-group">
                        <label htmlFor="project-slug">Project Slug *</label>
                        <FormField
                            name="slug"
                            rules="required,isSlug"
                            onChange={handleSlugChange}
                        >
                            <input
                                type="text"
                                id="project-slug"
                                placeholder="my-awesome-project"
                            />
                        </FormField>
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
                                <FormField
                                    name="custom_path_enabled"
                                    bindValue={false}
                                    onChange={(e) => {
                                        const isEnabled = e.target.checked;
                                        setValue('custom_path_enabled', isEnabled);

                                        if (!isEnabled) {
                                            setMultipleValues({
                                                path_type: 'relative',
                                                relative_path: '',
                                                absolute_path: ''
                                            });
                                            clearFieldError('relative_path');
                                            clearFieldError('absolute_path');
                                        } else if (!values.path_type) {
                                            setValue('path_type', 'relative');
                                        }
                                    }}
                                >
                                    <input
                                        type="checkbox"
                                        checked={customPathEnabled}
                                    />
                                </FormField>
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
                                <FormField
                                    name="path_type"
                                    bindValue={false}
                                    onChange={(e) => {
                                        setValue('path_type', e.target.value);
                                        clearFieldError('absolute_path');
                                    }}
                                >
                                    <input
                                        type="radio"
                                        id="path-type-relative"
                                        value="relative"
                                        checked={pathType === 'relative'}
                                    />
                                </FormField>
                                <label htmlFor="path-type-relative">Relative path</label>
                            </div>
                            <div className="form-group">
                                <FormField
                                    name="relative_path"
                                    bindValue={false}
                                    onChange={handleCustomPathChange('relative_path')}
                                >
                                    <input
                                        type="text"
                                        value={customRelativePath}
                                        placeholder="e.g. clients/acme"
                                        disabled={pathType !== 'relative'}
                                        className={pathType !== 'relative' ? 'disabled' : ''}
                                    />
                                </FormField>
                                <small className="help-text">Relative to: {config.projects_root_path}</small>
                            </div>
                        </div>

                        <div className="form-row form-option">
                            <div className="form-group radio-group">
                                <FormField
                                    name="path_type"
                                    bindValue={false}
                                    onChange={(e) => {
                                        setValue('path_type', e.target.value);
                                        clearFieldError('relative_path');
                                    }}
                                >
                                    <input
                                        type="radio"
                                        id="path-type-absolute"
                                        value="absolute"
                                        checked={pathType === 'absolute'}
                                    />
                                </FormField>
                                <label htmlFor="path-type-absolute">Absolute path</label>
                            </div>
                            <div className="form-group">
                                <FormField
                                    name="absolute_path"
                                    bindValue={false}
                                    onChange={handleCustomPathChange('absolute_path')}
                                >
                                    <input
                                        type="text"
                                        value={customAbsolutePath}
                                        placeholder="e.g. /var/www/projects"
                                        disabled={pathType !== 'absolute'}
                                        className={pathType !== 'absolute' ? 'disabled' : ''}
                                    />
                                </FormField>
                                <small className="help-text">Full system path</small>
                            </div>
                        </div>
                    </div>
                )}

                <div className="form-row">
                    <div className="form-group">
                        <label htmlFor="project-domain">Virtual Domain Name *</label>
                        <FormField name="domain" rules="required">
                            <input
                                type="text"
                                id="project-domain"
                                placeholder="myproject.local"
                            />
                        </FormField>
                    </div>
                </div>

                <div className="form-row">
                    <div className="form-group">
                        <label htmlFor="project-client">Client Name *</label>
                        <FormField name="client_name" rules="required">
                            <input
                                type="text"
                                id="project-client"
                                placeholder="Acme Corp"
                            />
                        </FormField>
                    </div>
                </div>

                <FormActions
                    onCancel={onCancel}
                    submitLabel="Create Project"
                    submittingLabel="Creating..."
                />

                <FormResponse
                        responseMessage={responseMessage}
                        responseType={responseType}
                        responseErrors={responseErrors}
                />
            </ManagedForm>
        </div>
    );
};

export default CreateProjectForm;
