/**
 * Form submission and reusable validation utilities for React forms.
 * Notes:
 * <form> element should use `onSubmit={handleSubmit}` and then use it when call formFn().
 */

import React, { useState, useCallback, useMemo, useRef } from 'react';

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
 * Reads a single form field value based on field type.
*/
const readFieldValue = (field) => {
    if (field.type === 'checkbox') {
        return !!field.checked;
    }

    if (field.tagName === 'SELECT' && field.multiple) {
        return Array.from(field.selectedOptions || []).map((option) => option.value);
    }

    return field.value;
};

/**
 * Scans all named controls in a form and creates a values object from current DOM values.
 * Fields without [name] will be ignored.
 */
const collectFormValues = (formElement) => {
    if (!formElement || !formElement.elements) {
        return {};
    }

    const formFields = Array.from(formElement.elements).filter((field) => field && field.name && !field.disabled);

    /**
     * Count checkboxes by field name so grouped checkboxes can be collected as arrays.
     * For example, if you have <input type="checkbox" name="features" value="a">
     *     and <input type="checkbox" name="features" value="b">,
     *     then checkboxGroupSizes['features'] will be 2,
     *     so we know to collect them into an array like features: ['a', 'b'] when both are checked.
     */
    const checkboxGroupSizes = formFields.reduce((acc, field) => {
        if (field.type !== 'checkbox') {
            return acc;
        }

        acc[field.name] = (acc[field.name] || 0) + 1;
        return acc;
    }, {});

    return formFields.reduce((acc, field) => {
        if (field.type === 'radio') {
            if (field.checked) {
                acc[field.name] = field.value;
            } else if (!(field.name in acc)) {
                acc[field.name] = '';
            }
            return acc;
        }

        if (field.type === 'checkbox' && checkboxGroupSizes[field.name] > 1) {
            if (!Array.isArray(acc[field.name])) {
                acc[field.name] = [];
            }

            if (field.checked) {
                acc[field.name].push(field.value);
            }

            return acc;
        }

        acc[field.name] = readFieldValue(field);
        return acc;
    }, {});
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
            // Backend may return field errors in different wrappers depending on source.
            const fieldErrors = err?.errors || err?.validationErrors || err?.payload?.errors || err?.payload?.data?.errors;
            // Also, a general error message can be returned under `message`.
            const message = err?.message || err?.payload?.error_message || 'An error occurred. Please try again.';

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
export const formFieldsFn = () => {
    const [values, setValues] = useState({});
    const [touched, setTouched] = useState({});
    const defaultValuesRef = useRef({});

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

    const setDefaultValues = useCallback((defaultValues = {}) => {
        const normalizedDefaults = (defaultValues && typeof defaultValues === 'object')
            ? Object.entries(defaultValues).reduce((acc, [key, fieldValue]) => {
                if (
                    fieldValue
                    && typeof fieldValue === 'object'
                    && 'defaultValue' in fieldValue
                ) {
                    acc[key] = fieldValue.defaultValue;
                    return acc;
                }

                acc[key] = fieldValue;
                return acc;
            }, {})
            : {};

        defaultValuesRef.current = normalizedDefaults;
        setValues(normalizedDefaults);
        setTouched({});
    }, []);

    // Set multiple values at once.
    const setMultipleValues = useCallback((newValues) => {
        setValues((prev) => ({ ...prev, ...newValues }));
    }, []);

    // Reset form to initial values. Useful for resetting form state after successful submission.
    const reset = useCallback(() => {
        setValues({ ...defaultValuesRef.current });
        setTouched({});
    }, []);

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
        setDefaultValues,
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
        /**
         * Used to push extra values to validation flow, if it relies on some extra value.
         */
        extraValidationValuesCb
    } = {}
) => {
    // Hook functionality related to form fields, fields state, etc.
    const formFields = formFieldsFn();
    // Hook functionality related to submitting the form, showing errors, running on success callbacks.
    const formSubmit = formSubmitFn({ onSubmit, onSuccess, onError });
    // Stores form DOM element so we can use its attributes, options, etc. here.
    const formRef = useRef(null);
    // Each field can register rules from JSX via getFieldProps({ validationRules: 'required,isSlug' }).
    const fieldRulesRef = useRef({});

    // Keep ref assignment side-effect free to avoid render loops from repeated callback ref invocations.
    const setFormRef = useCallback((element) => {
        formRef.current = element;
    }, []);

    const getFormValues = useCallback(() => {
        const domValues = collectFormValues(formRef.current);
        return {
            ...formFields.values,
            ...domValues
        };
    }, [formFields.values]);

    const collectExtraValuesForValidation = useCallback(() => {
        // Optional external values allow validating fields that are not stored in formFields.values.
        const extraValues = typeof extraValidationValuesCb === 'function' ? extraValidationValuesCb() : {};
        return {
            ...getFormValues(),
            ...(extraValues || {})
        };
    }, [getFormValues, extraValidationValuesCb]);

    // No need to track any form change?
    // Delegated form-level change handler so plain inputs with `name` auto-sync with form state.
    // const handleFormChange = useCallback((e) => {
    //     const target = e?.target;
    //
    //     if (!target || !target.name || target.disabled) {
    //         return;
    //     }
    //
    //     if (target.type === 'radio' && !target.checked) {
    //         return;
    //     }
    //
    //     if (target.type === 'checkbox') {
    //         const latestGroupValues = collectFormValues(formRef.current);
    //         formFields.setValue(target.name, latestGroupValues[target.name]);
    //     } else {
    //         formFields.setValue(target.name, readFieldValue(target));
    //     }
    //
    //     formSubmit.clearFieldError(target.name);
    // }, [formFields, formSubmit]);

    // Do we need this?
    // Delegated blur handler marks touched fields when user leaves an input.
    const handleFormBlur = useCallback((e) => {
        if (!e?.target?.name) {
            return;
        }

        formFields.handleBlur(e);
    }, [formFields]);

    // Runs before form submission in handleSubmit().
    const validateForm = useCallback(
        () => {
            // Combines values between the form-passed values and optionally provided external values for validation.
            // This allows validating fields that are not stored in formFields.values.
            const combinedValues = collectExtraValuesForValidation();
            // Global rules and per-field JSX rules are combined into one validation map.
            const combinedRules = {
                ...validationRules, // is passed when formFn() is called.
                ...fieldRulesRef.current // ? still need?
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

    // Fires when form is being submitted.
    const handleSubmit = useCallback(async (e) => {
        // Prevent default <form> submission behavior.
        if (e && e.preventDefault) {
            e.preventDefault();
        }

        formSubmit.clearAllErrors();

        // Run form validations.
        const validationErrors = validateForm();

        // Block submit when client-side validation fails.
        if (Object.keys(validationErrors).length > 0) {
            formSubmit.setFieldErrors(validationErrors);
            return { success: false, validationErrors };
        }

        // If no errors, then submit. Backend check also will happen.
        return formSubmit.executeSubmit(getFormValues());
    }, [formSubmit, validateForm, getFormValues]);

    // Initialize form functionality. Attaches events handlers.
    const initFormFn = useCallback((options = {}) => {
        const {
            ref,
            onSubmit: onSubmitCustom,
            onChange: onChangeCustom,
            onBlur: onBlurCustom,
            ...rest
        } = options;

        return {
            ...rest,
            ref: (element) => {
                setFormRef(element);

                if (typeof ref === 'function') {
                    ref(element);
                } else if (ref && typeof ref === 'object') {
                    ref.current = element;
                }
            },
            onSubmit: async (e) => {
                if (onSubmitCustom) {
                    onSubmitCustom(e);
                }

                return handleSubmit(e);
            },
            onChange: (e) => {
                // handleFormChange(e);
                if (onChangeCustom) {
                    onChangeCustom(e);
                }
            },
            onBlur: (e) => {
                handleFormBlur(e);

                if (onBlurCustom) {
                    onBlurCustom(e);
                }
            }
        };
    }, [setFormRef, handleSubmit, handleFormBlur, formFields]);
    // }, [setFormRef, handleSubmit, handleFormChange, handleFormBlur]);

    // Shared field props builder: attaches value, handlers, error class, and rule registration.
    const buildFieldProps = useCallback((name, options = {}) => {
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

    // Keep latest field helpers in refs so FormField component identity stays stable.
    const fieldRuntimeRef = useRef({
        buildFieldProps,
        renderFieldError: formSubmit.renderFieldError
    });

    fieldRuntimeRef.current.buildFieldProps = buildFieldProps;
    fieldRuntimeRef.current.renderFieldError = formSubmit.renderFieldError;

    // Declarative field wrapper that injects field props and auto-renders field error right after child input.
    const FormField = useMemo(() => {
        return function FormFieldComponent({
        name,
        rules,
        className = '',
        errorClassName = 'error-message',
        bindValue = true,
        onChange,
        onBlur,
        children,
        ...rest
        }) {
            if (!React.isValidElement(children)) {
                return children || null;
            }

            const runtime = fieldRuntimeRef.current;

            const fieldProps = runtime.buildFieldProps(name, {
                onChange,
                onBlur,
                className,
                validationRules: rules,
                ...rest
            });

            const childOnChange = children.props.onChange;
            const childOnBlur = children.props.onBlur;
            const childHasOwnValue = Object.prototype.hasOwnProperty.call(children.props, 'value');

            const mergedProps = {
                ...fieldProps,
                ...children.props,
                name,
                className: [children.props.className, fieldProps.className].filter(Boolean).join(' '),
                onChange: (e) => {
                    fieldProps.onChange(e);

                    if (typeof childOnChange === 'function') {
                        childOnChange(e);
                    }
                },
                onBlur: (e) => {
                    fieldProps.onBlur(e);

                    if (typeof childOnBlur === 'function') {
                        childOnBlur(e);
                    }
                }
            };

            if (!bindValue || childHasOwnValue) {
                delete mergedProps.value;
            }

            return (
                <>
                    {React.cloneElement(children, mergedProps)}
                    {runtime.renderFieldError(name, errorClassName)}
                </>
            );
        };
    }, []);

    return {
        ...formFields,
        ...formSubmit,
        handleSubmit,
        initFormFn,
        FormField,
        validateForm
    };
};
