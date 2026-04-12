/**
 * Reusable form actions UI.
 */

import React from 'react';

const FormActionsBase = ({
    isSubmitting,
    generalError,
    generalMessage,
    className = 'form-actions',
    submitLabel = 'Submit',
    submittingLabel = 'Submitting...',
    submitClassName = 'btn btn-primary',
    cancelLabel = 'Cancel',
    onCancel,
    cancelClassName = 'btn btn-secondary',
    errorClassName = 'form-error',
    successClassName = 'form-success',
    spinnerClassName = 'button-spinner',
    children,
    submitButtonProps = {},
    cancelButtonProps = {}
}) => {
    return (
        <div className={className}>
            {generalError && (
                <div className={errorClassName}>{generalError}</div>
            )}

            {generalMessage && (
                <div className={successClassName}>{generalMessage}</div>
            )}

            {typeof onCancel === 'function' && (
                <button
                    type="button"
                    className={cancelClassName}
                    onClick={onCancel}
                    disabled={isSubmitting}
                    {...cancelButtonProps}
                >
                    {cancelLabel}
                </button>
            )}

            {children}

            <button
                type="submit"
                className={submitClassName}
                disabled={isSubmitting}
                {...submitButtonProps}
            >
                {isSubmitting ? (
                    <>
                        <span className={spinnerClassName} aria-hidden="true"></span>
                        <span>{submittingLabel}</span>
                    </>
                ) : submitLabel}
            </button>
        </div>
    );
};

export default FormActionsBase;
