/**
 * Shared app shell with global header/main/footer layout.
 * Mostly used for the main app container. So should not be used for module-based features.
 */

import React from 'react';
import NotificationsHost from './NotificationsHost';

const AppShell = ({ title, headerAction, children }) => {
    return (
        <div className="app">
            <NotificationsHost />

            <header className="app-header">
                <div className="container">
                    <h1 className="app-title">{title}</h1>
                    {headerAction ? (
                        <button
                            type="button"
                            className={`btn ${headerAction.className || 'btn-primary'}`}
                            onClick={headerAction.onClick}
                        >
                            {headerAction.label}
                        </button>
                    ) : null}
                </div>
            </header>

            <main className="app-main">
                <div className="container">{children}</div>
            </main>

            <footer className="app-footer">
                <div className="container">
                    <p className="app-footer-text">Webserver Home Manager</p>
                </div>
            </footer>
        </div>
    );
};

export default AppShell;

