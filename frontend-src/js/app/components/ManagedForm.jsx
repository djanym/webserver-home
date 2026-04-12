/**
 * Thin wrapper that applies formFn-managed props to a native form element.
 */

import React from 'react';

const ManagedForm = ({ form, children, ...options }) => {
    if (!form || typeof form.initFormFn !== 'function') {
        return (
            <form {...options}>
                {children}
            </form>
        );
    }

    return (
        <form {...form.initFormFn(options)}>
            {children}
        </form>
    );
};

export default ManagedForm;

