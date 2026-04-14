/**
 * Reusable response messages UI for forms.
 */

import React from 'react';

const normalizeResponseType = (type) => {
    return ['error', 'success', 'info'].includes(type) ? type : 'success';
};

const normalizeResponseList = (items) => {
    if (!Array.isArray(items)) {
        return [];
    }

    return items
        .map((item) => (typeof item === 'string' ? item.trim() : String(item || '').trim()))
        .filter(Boolean);
};

const FormResponse = ({
    responseMessage = null,
    responseType = 'success',
    responseErrors = [],
    className = 'form-response',
    responseClassName = 'response-container',
    errorPrefix = 'Error:',
    errorClassName = 'error'
}) => {
    const resolvedResponseType = normalizeResponseType(responseType);
    const hasResponseMessage = typeof responseMessage === 'string' && responseMessage.trim().length > 0;
    const normalizedErrors = normalizeResponseList(responseErrors);

    if (!hasResponseMessage && normalizedErrors.length === 0) {
        return null;
    }

    return (
        <div className={className}>
            {hasResponseMessage && (
                <div className={`${responseClassName} ${resolvedResponseType}`}>
                    {responseMessage}
                </div>
            )}

            {normalizedErrors.map((errorMessage, index) => (
                <div
                    key={`${errorMessage}-${index}`}
                    className={`${responseClassName} ${errorClassName}`}
                >
                    {errorPrefix} {errorMessage}
                </div>
            ))}
        </div>
    );
};

export default FormResponse;

