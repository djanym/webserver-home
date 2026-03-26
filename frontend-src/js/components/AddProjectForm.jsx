/**
 * Add project form component.
 */

import React, { useState } from 'react';
import { createProject } from '../services/api';

const AddProjectForm = ({ onProjectAdded, onCancel }) => {
    const [formData, setFormData] = useState({
        name: '',
        domain: '',
        folder_name: '',
    });
    const [errors, setErrors] = useState({});
    const [isSubmitting, setIsSubmitting] = useState(false);

    const handleChange = (e) => {
        const { name, value } = e.target;
        setFormData({
            ...formData,
            [name]: value
        });
        // Clear error when user types.
        if (errors[name]) {
            setErrors({
                ...errors,
                [name]: null
            });
        }
    };

    const validate = () => {
        const newErrors = {};
        if (!formData.name.trim()) {
            newErrors.name = 'Project name is required';
        }
        if (!formData.domain.trim()) {
            newErrors.domain = 'Domain is required';
        }
        if (!formData.folder_name.trim()) {
            newErrors.folder_name = 'Folder name is required';
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
            const newProject = await createProject(formData);
            onProjectAdded(newProject);
            setFormData({ name: '', domain: '', folder_name: '' });
        } catch (err) {
            setErrors({ submit: err.message || 'Failed to create project' });
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
                        <label htmlFor="project-name">Project Name *</label>
                        <input
                            type="text"
                            id="project-name"
                            name="name"
                            value={formData.name}
                            onChange={handleChange}
                            placeholder="My Awesome Project"
                            className={errors.name ? 'error' : ''}
                        />
                        {errors.name && <span className="error-message">{errors.name}</span>}
                    </div>
                </div>

                <div className="form-row">
                    <div className="form-group">
                        <label htmlFor="project-domain">Domain *</label>
                        <input
                            type="text"
                            id="project-domain"
                            name="domain"
                            value={formData.domain}
                            onChange={handleChange}
                            placeholder="project.local"
                            className={errors.domain ? 'error' : ''}
                        />
                        {errors.domain && <span className="error-message">{errors.domain}</span>}
                    </div>
                </div>

                <div className="form-row">
                    <div className="form-group">
                        <label htmlFor="project-folder">Folder Name *</label>
                        <input
                            type="text"
                            id="project-folder"
                            name="folder_name"
                            value={formData.folder_name}
                            onChange={handleChange}
                            placeholder="my-project"
                            className={errors.folder_name ? 'error' : ''}
                        />
                        {errors.folder_name && <span className="error-message">{errors.folder_name}</span>}
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
                        {isSubmitting ? 'Creating...' : 'Create Project'}
                    </button>
                </div>
            </form>
        </div>
    );
};

export default AddProjectForm;
