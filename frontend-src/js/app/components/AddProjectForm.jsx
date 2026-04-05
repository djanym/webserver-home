/**
 * Add project form component.
 */

import React, { useState } from 'react';
import { createProject } from '../services/api';

// Generate a URL-safe slug from a title string.
const slugify = (value) =>
    value
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-');

const AddProjectForm = ({ onProjectAdded, onCancel }) => {
    const [formData, setFormData] = useState({
        title: '',
        slug: '',
        domain: '',
        client_name: '',
    });
    const [slugEdited, setSlugEdited] = useState(false);
    const [errors, setErrors] = useState({});
    const [isSubmitting, setIsSubmitting] = useState(false);

    const handleChange = (e) => {
        const { name, value } = e.target;

        setFormData((prev) => {
            const updated = { ...prev, [name]: value };

            // Auto-generate slug from title unless user has manually edited it.
            if (name === 'title' && !slugEdited) {
                updated.slug = slugify(value);
            }

            return updated;
        });

        // Clear field error on change.
        if (errors[name]) {
            setErrors((prev) => ({ ...prev, [name]: null }));
        }
    };

    const handleSlugChange = (e) => {
        setSlugEdited(true);
        handleChange(e);
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
            setFormData({ title: '', slug: '', domain: '', client_name: '' });
            setSlugEdited(false);
        } catch (err) {
            setErrors({ submit: err.message || 'Failed to create project.' });
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
                        {isSubmitting ? 'Creating...' : 'Create Project'}
                    </button>
                </div>

            </form>
        </div>
    );
};

export default AddProjectForm;
