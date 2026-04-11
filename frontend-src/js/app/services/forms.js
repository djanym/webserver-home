/**
 * Form submission and reusable validation utilities for React forms.
 * Notes:
 * <form> element should use `onSubmit={handleSubmit}` and then use it when call formFn().
 */

import React, { useState, useCallback, useRef } from 'react';

// Built-in validation rule handlers that can be reused in any form.
const BUILT_IN_VALIDATORS = {
    required: (value) => {
        if (typeof value === 'boolean') {
            return value;
        }

        if (value === null || value === undefined) {
            return false;
        }

        return String(value).trim().length > 0;
    },
    isSlug: (value) => /^[a-z0-9]+(?:-[a-z0-9]+)*$/.test(String(value || '').trim())
};

const DEFAULT_RULE_MESSAGES = {
    required: 'This field is required.',
    isSlug: 'Only lowercase letters, numbers, and hyphens are allowed.'
};

// Parses a string of comma-separated rules defined in the field attributes into an array of rule names.
const parseRulesString = (rulesString = '') => {
    return rulesString
        .split(/[|,]/)
        .map((rule) => rule.trim())
        .filter(Boolean);
};

// Converts different rule formats into one predictable array shape.
const normalizeRuleItem = (ruleItem) => {
    if (!ruleItem) {
        return [];
    }

    if (typeof ruleItem === 'string') {
        return parseRulesString(ruleItem).map((ruleName) => ({
            name: ruleName,
            message: null,
            when: null
        }));
    }

    if (Array.isArray(ruleItem)) {
        return ruleItem.flatMap(normalizeRuleItem);
    }

    if (typeof ruleItem === 'object') {
        if (ruleItem.rules) {
            return normalizeRuleItem(ruleItem.rules).map((normalized) => ({
                ...normalized,
                when: ruleItem.when || normalized.when,
                message: ruleItem.message || normalized.message
            }));
        }

        if (ruleItem.rule) {
            return [{
                name: ruleItem.rule,
                message: ruleItem.message || null,
                when: ruleItem.when || null
            }];
        }
    }

    return [];
};

/**
 * Validates form values against provided rules. Returns an object with error messages per field, e.g. { title: 'Title is required.' }.
 *
 * @param param0
 * @param param0.values
 * @param param0.rulesMap
 * @param param0.validators
 * @returns {{}}
 */
const validateFields = ({ values, rulesMap, validators = {} }) => {
    // Form-specific validators can override built-in ones when needed.
    const mergedValidators = {
        ...BUILT_IN_VALIDATORS,
        ...validators
    };

    return Object.entries(rulesMap).reduce((acc, [fieldName, fieldRules]) => {
        const normalizedRules = normalizeRuleItem(fieldRules);

        // We stop at first failed rule per field to keep messages clear and stable.
        for (let i = 0; i < normalizedRules.length; i += 1) {
            const currentRule = normalizedRules[i];
            const ruleName = currentRule.name;
            const validateRule = mergedValidators[ruleName];

            if (typeof validateRule !== 'function') {
                continue;
            }

            if (typeof currentRule.when === 'function' && !currentRule.when(values)) {
                continue;
            }

            const isValid = validateRule(values[fieldName], {
                fieldName,
                values,
                ruleName
            });

            if (!isValid) {
                acc[fieldName] = currentRule.message || DEFAULT_RULE_MESSAGES[ruleName] || 'Invalid value.';
                break;
            }
        }

        return acc;
    }, {});
};

/**
 * Collection of functions related to form submission: handling submit state, showing errors, running onSuccess/onError callbacks, etc.
 * Used by formFn().
 * Accepts custom onSubmit, onSuccess, onError callbacks and initialErrors when formFn() is called.
 */
export const formSubmitFn = ({
    onSubmit,
    onSuccess,
    onError,
    initialErrors = {}
} = {}) => {
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [errors, setErrors] = useState(initialErrors);
    const [generalError, setGeneralError] = useState(null);

    // Removes one field error so UI can react immediately when the user edits that field.
    const clearFieldError = useCallback((fieldName) => {
        setErrors((prev) => {
            // If field is not present in the errors object, then there's nothing to clear.
            if (!prev[fieldName]) {
                return prev;
            }

            // We need to create a new object to trigger re-render.
            const { [fieldName]: _removed, ...rest } = prev;
            return rest;
        });
    }, []);

    const clearAllErrors = useCallback(() => {
        setErrors({});
        setGeneralError(null);
    }, []);

    // Merges new backend/client errors into current error object.
    const setFieldErrors = useCallback((fieldErrors) => {
        if (!fieldErrors || typeof fieldErrors !== 'object') {
            return;
        }

        setErrors((prev) => ({ ...prev, ...fieldErrors }));
    }, []);

    const executeSubmit = useCallback(async (data) => {
        if (!onSubmit) {
            return;
        }

        // Every submit starts from a clean visual error state.
        setIsSubmitting(true);
        setGeneralError(null);
        setErrors({});

        try {
            const result = await onSubmit(data);

            // onSuccess callback can be provided when formFn() is called.
            if (onSuccess) {
                onSuccess(result);
            }

            return { success: true, data: result };
        } catch (err) {
            // Backend may return field errors under `errors`.
            const fieldErrors = err?.errors;
            // Also, a general error message can be returned under `message`.
            const message = err?.message || 'An error occurred. Please try again.';

            if (fieldErrors && typeof fieldErrors === 'object') {
                setFieldErrors(fieldErrors);
            }

            setGeneralError(message);

            // onError callback can be provided when formFn() is called.
            if (onError) {
                onError(err);
            }

            return { success: false, error: err, fieldErrors };
        } finally {
            setIsSubmitting(false);
        }
    }, [onSubmit, onSuccess, onError, setFieldErrors]);

    const getFieldError = useCallback((fieldName) => {
        return errors[fieldName] || null;
    }, [errors]);

    const hasFieldError = useCallback((fieldName) => {
        return !!errors[fieldName];
    }, [errors]);

    // Reusable renderer: returns <span className="error-message">...</span> only when a field has an error.
    const renderFieldError = useCallback((fieldName, className = 'error-message') => {
        // @todo: check why it's called everytime a field is changed and not only when submitted.
        // Or we need to make it when a field is changing then only it should be checked immediately.
        const fieldError = errors[fieldName];
        console.log('renderFieldError', fieldName, fieldError);

        if (!fieldError) {
            return null;
        }

        return (
            <span className={className}>{fieldError}</span>
        );
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
        renderFieldError,
        setErrors
    };
};

/**
 * Collection of functions related to form fields: handling value change, settings/clearing values, etc.
 * Used by formFn().
 */
export const formFieldsFn = (initialValues = {}) => {
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

    // Set multiple values at once.
    const setMultipleValues = useCallback((newValues) => {
        setValues((prev) => ({ ...prev, ...newValues }));
    }, []);

    // Reset form to initial values. Useful for resetting form state after successful submission.
    const reset = useCallback(() => {
        setValues(initialValues);
        setTouched({});
    }, [initialValues]);

    //
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
 * Main form functionality hook.
 */
export const formFn = (
    {
        initialValues = {},
        onSubmit,
        onSuccess,
        onError,
        /**
        * Custom validation function that can be used to validate form values before submission.
        * It receives all form values and should return an object with error messages per field,
        * e.g. { title: 'Title is required.' }.
        * This allows complex validations that can't be easily expressed via simple rules.
        */
        customValidateCb,
        /**
        * Validation rules should be passed.
        */
        validationRules = {},
        //
        validators,
        extraValidationValuesCb
    } = {}
) => {
    // Hook functionality related to form fields, fields state, etc.
    const formFields = formFieldsFn(initialValues);
    // Hook functionality related to submitting the form, showing errors, running on success callbacks.
    const formSubmit = formSubmitFn({ onSubmit, onSuccess, onError });

    // Each field can register rules from JSX via getFieldProps({ validationRules: 'required,isSlug' }).
    const fieldRulesRef = useRef({});

    const collectExtraValuesForValidation = useCallback(() => {
        // Optional external values allow validating fields that are not stored in formFields.values.
        const extraValues = typeof extraValidationValuesCb === 'function' ? extraValidationValuesCb() : {};
        return {
            ...formFields.values,
            ...(extraValues || {})
        };
    }, [formFields.values, extraValidationValuesCb]);

    // Runs before form submission in handleSubmit().
    const validateForm = useCallback(
        () => {
            // Combines values between the form-passed values and optionally provided external values for validation.
            // This allows validating fields that are not stored in formFields.values.
            const combinedValues = collectExtraValuesForValidation();
            // Global rules and per-field JSX rules are combined into one validation map.
            const combinedRules = {
                ...validationRules, // is passed when formFn() is called.
                ...fieldRulesRef.current // ?
            };

            const ruleErrors = validateFields({
                values: combinedValues,
                rulesMap: combinedRules,
                validators // ?
            });

            // Run custom validations. customValidateCb function can be passed wheb formFn() is called.
            const customErrors = typeof customValidateCb === 'function'
                ? (customValidateCb(combinedValues) || {})
                : {};

            // If both sources define same key, customErrors win because they are more specific.
            return {
                ...ruleErrors,
                ...customErrors
            };
        },
        // These are cached dependencies for re-running the function.
        // If any of them changes, validation function will be re-created with new values.
        [collectExtraValuesForValidation, validationRules, validators, customValidateCb]
    );

    // Fires when form is being submitted. Hooked in the <form onSubmit> and used in when formFn() called.
    const handleSubmit = useCallback(async (e) => {
        // Prevent default <form> submission behavior.
        if (e && e.preventDefault) {
            e.preventDefault();
        }

        formSubmit.clearAllErrors();

        // Run client side validations.
        const validationErrors = validateForm();

        // Block submit when client-side validation fails.
        if (Object.keys(validationErrors).length > 0) {
            formSubmit.setFieldErrors(validationErrors);
            return { success: false, validationErrors };
        }

        // If no errors, then submit. Backend check also will happen.
        return formSubmit.executeSubmit(formFields.values);
    }, [formSubmit, validateForm, formFields.values]);

    // Shared field props builder: attaches value, handlers, error class, and rule registration.
    const getFieldProps = useCallback((name, options = {}) => {
        const {
            onChange,
            onBlur,
            className = '',
            validationRules: rules,
            ...rest
        } = options;

        if (rules) {
            // Rules are registered once field props are requested by the component.
            fieldRulesRef.current[name] = rules;
        }

        return {
            name,
            value: formFields.values[name] || '',
            onChange: (e) => {
                if (onChange) {
                    onChange(e);
                } else {
                    formFields.handleChange(e);
                }

                formSubmit.clearFieldError(name);
            },
            onBlur: (e) => {
                formFields.handleBlur(e);

                if (onBlur) {
                    onBlur(e);
                }
            },
            className: [className, formSubmit.hasFieldError(name) ? 'error' : '']
                .filter(Boolean)
                .join(' '),
            ...rest
        };
    }, [formFields, formSubmit]);

    return {
        ...formFields,
        ...formSubmit,
        handleSubmit,
        getFieldProps,
        validateForm
    };
};
