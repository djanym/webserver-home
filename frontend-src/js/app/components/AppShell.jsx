/**
 * Shared app shell with global header/main/footer layout.
 * Mostly used for the main app container. So should not be used for module-based features.
 */

import React from 'react';
import NotificationsHost from './NotificationsHost';
import logo from '../../../images/logo.svg';

const AppShell = ({ title, headerAction, children }) => {
    return (
        <div className="app">
            <NotificationsHost />

            <header className="app-header">
                <div className="container">
                    <div className="header-left">
                        <div className="app-logo-container">
                            <img src={logo} alt="Webserver Home Manager Logo" />
                        </div>
                        <h1 className="app-title">{title}</h1>
                        <div className="search-placeholder">
                            <input type="text" placeholder="Search projects..." disabled />
                        </div>
                    </div>
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

