/**
 * Shared app shell with global header/main/footer layout.
 */

import React from 'react';

const AppShell = ({ title, headerAction, children }) => {
    return (
        <div className="app">
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

