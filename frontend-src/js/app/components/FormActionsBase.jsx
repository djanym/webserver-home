/**
 * Reusable form actions UI.
 */

import React from 'react';

const FormActionsBase = ({
    isSubmitting,
    className = 'form-actions',
    actionButtonsRowClassName = 'action-buttons-row',
    submitLabel = 'Submit',
    submittingLabel = 'Submitting...',
    submitClassName = 'btn btn-primary',
    cancelLabel = 'Cancel',
    onCancel,
    cancelClassName = 'btn btn-secondary',
    spinnerClassName = 'button-spinner',
    children,
    submitButtonProps = {},
    cancelButtonProps = {}
}) => {
    return (
        <div className={className}>
            <div className={actionButtonsRowClassName}>
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
        </div>
    );
};

export default FormActionsBase;
