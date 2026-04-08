/**
 * Form submission and error handling utilities for React.
 */

import { useState, useCallback } from 'react';

/**
 * Hook for managing form submission state and backend error handling.
 *
 * @param {Object} options
 * @param {Function} options.onSubmit - Async function that submits the form data
 * @param {Function} options.onSuccess - Callback when submission succeeds (receives response data)
 * @param {Function} options.onError - Callback when submission fails (receives error)
 * @param {Object} options.initialErrors - Initial error state
 * @returns {Object}
 */
export const useFormSubmit = ({
    onSubmit,
    onSuccess,
    onError,
    initialErrors = {}
} = {}) => {
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [errors, setErrors] = useState(initialErrors);
    const [generalError, setGeneralError] = useState(null);

    /**
     * Clear error for a specific field.
     */
    const clearFieldError = useCallback((fieldName) => {
        setErrors((prev) => {
            if (!prev[fieldName]) return prev;
            const { [fieldName]: _, ...rest } = prev;
            return rest;
        });
    }, []);

    /**
     * Clear all errors.
     */
    const clearAllErrors = useCallback(() => {
        setErrors({});
        setGeneralError(null);
    }, []);

    /**
     * Set field errors from backend response.
     * Backend returns errors as: { fieldName: errorMessage, ... }
     */
    const setFieldErrors = useCallback((fieldErrors) => {
        if (!fieldErrors || typeof fieldErrors !== 'object') return;
        setErrors((prev) => ({ ...prev, ...fieldErrors }));
    }, []);

    /**
     * Execute form submission.
     */
    const executeSubmit = useCallback(async (data) => {
        if (!onSubmit) return;

        setIsSubmitting(true);
        setGeneralError(null);
        setErrors({});

        try {
            const result = await onSubmit(data);

            if (onSuccess) {
                onSuccess(result);
            }

            return { success: true, data: result };
        } catch (err) {
            const fieldErrors = err?.validationErrors || err?.errors;
            const message = err?.message || 'An error occurred. Please try again.';

            if (fieldErrors && typeof fieldErrors === 'object') {
                setFieldErrors(fieldErrors);
            }

            setGeneralError(message);

            if (onError) {
                onError(err);
            }

            return { success: false, error: err, fieldErrors };
        } finally {
            setIsSubmitting(false);
        }
    }, [onSubmit, onSuccess, onError, setFieldErrors]);

    /**
     * Get error message for a specific field.
     */
    const getFieldError = useCallback((fieldName) => {
        return errors[fieldName] || null;
    }, [errors]);

    /**
     * Check if a field has an error.
     */
    const hasFieldError = useCallback((fieldName) => {
        return !!errors[fieldName];
    }, [errors]);

    return {
        isSubmitting,
        errors,
        generalError,
        executeSubmit,
        clearFieldError,
        clearAllErrors,
        setFieldErrors,
        getFieldError,
        hasFieldError,
        setErrors
    };
};

/**
 * Hook for managing form state with change handlers.
 *
 * @param {Object} initialValues
 * @returns {Object}
 */
export const useFormState = (initialValues = {}) => {
    const [values, setValues] = useState(initialValues);
    const [touched, setTouched] = useState({});

    const handleChange = useCallback((e) => {
        const { name, value, type, checked } = e.target;
        setValues((prev) => ({
            ...prev,
            [name]: type === 'checkbox' ? checked : value
        }));
    }, []);

    const handleBlur = useCallback((e) => {
        const { name } = e.target;
        setTouched((prev) => ({ ...prev, [name]: true }));
    }, []);

    const setValue = useCallback((name, value) => {
        setValues((prev) => ({ ...prev, [name]: value }));
    }, []);

    const setMultipleValues = useCallback((newValues) => {
        setValues((prev) => ({ ...prev, ...newValues }));
    }, []);

    const reset = useCallback(() => {
        setValues(initialValues);
        setTouched({});
    }, [initialValues]);

    const clear = useCallback(() => {
        const emptyValues = Object.keys(values).reduce((acc, key) => {
            acc[key] = '';
            return acc;
        }, {});
        setValues(emptyValues);
        setTouched({});
    }, [values]);

    return {
        values,
        touched,
        handleChange,
        handleBlur,
        setValue,
        setMultipleValues,
        reset,
        clear
    };
};

/**
 * Combines useFormState and useFormSubmit for a complete form solution.
 *
 * @param {Object} options
 * @param {Object} options.initialValues - Initial form values
 * @param {Function} options.onSubmit - Submit handler
 * @param {Function} options.onSuccess - Success callback
 * @param {Function} options.onError - Error callback
 * @param {Function} options.validate - Optional validation function
 * @returns {Object}
 */
export const useForm = ({
    initialValues = {},
    onSubmit,
    onSuccess,
    onError,
    validate
} = {}) => {
    const formState = useFormState(initialValues);
    const formSubmit = useFormSubmit({ onSubmit, onSuccess, onError });

    const handleSubmit = useCallback(async (e) => {
        if (e && e.preventDefault) {
            e.preventDefault();
        }

        if (validate) {
            const validationErrors = validate(formState.values);
            if (validationErrors && Object.keys(validationErrors).length > 0) {
                formSubmit.setFieldErrors(validationErrors);
                return { success: false, validationErrors };
            }
        }

        return formSubmit.executeSubmit(formState.values);
    }, [formState.values, validate, formSubmit]);

    const getFieldProps = useCallback((name) => ({
        name,
        value: formState.values[name] || '',
        onChange: formState.handleChange,
        onBlur: formState.handleBlur,
        className: formSubmit.hasFieldError(name) ? 'error' : ''
    }), [formState, formSubmit]);

    return {
        ...formState,
        ...formSubmit,
        handleSubmit,
        getFieldProps
    };
};
