/**
 * Reusable form actions UI.
 */

import React from 'react';

const FormActionsBase = ({
    isSubmitting,
    responseMessage,
    responseType = 'success',
    className = 'form-actions',
    actionButtonsRowClassName = 'action-buttons-row',
    submitLabel = 'Submit',
    submittingLabel = 'Submitting...',
    submitClassName = 'btn btn-primary',
    cancelLabel = 'Cancel',
    onCancel,
    cancelClassName = 'btn btn-secondary',
    responseClassName = 'response-container',
    spinnerClassName = 'button-spinner',
    children,
    submitButtonProps = {},
    cancelButtonProps = {}
}) => {
    const resolvedResponseType = ['error', 'success', 'info'].includes(responseType) ? responseType : 'success';
    const hasResponseMessage = typeof responseMessage === 'string' && responseMessage.trim().length > 0;

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

            {hasResponseMessage && (
                <div className={`${responseClassName} ${resolvedResponseType}`}>
                    {responseMessage}
                </div>
            )}
        </div>
    );
};

export default FormActionsBase;
