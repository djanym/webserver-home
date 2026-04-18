/**
 * Global notification host.
 *
 * This component renders the stacked notification bubbles at the top of the page
 * and listens to the shared notification store for updates.
 */

import React, { useEffect, useState } from 'react';
import { closeNotification, subscribeNotifications } from '../services/notifications';

const NotificationsHost = () => {
    const [notifications, setNotifications] = useState([]);

    useEffect(() => {
        // Subscribe once so any component can trigger notifications through the service.
        const unsubscribe = subscribeNotifications(setNotifications);

        return () => {
            unsubscribe();
        };
    }, []);

    if (notifications.length === 0) {
        return null;
    }

    return (
        <div className="notifications-container" aria-live="polite" aria-atomic="true" style={{ display: 'flex' }}>
            <div className="items-listing">
                {notifications.map((notification) => (
                    <div
                        key={notification.id}
                        className={`notification-popup ${notification.type}`}
                        role={notification.type === 'error' ? 'alert' : 'status'}
                    >
                        <div className="notification-content">
                            <div className="notification-message">
                                {notification.message}
                            </div>

                            {notification.errors.length > 0 ? (
                                <ul className="notification-errors">
                                    {notification.errors.map((errorMessage, index) => (
                                        <li key={`${notification.id}-${index}`}>{errorMessage}</li>
                                    ))}
                                </ul>
                            ) : null}
                        </div>

                        <button
                            type="button"
                            className="close"
                            aria-label="Close notification"
                            onClick={() => closeNotification(notification.id)}
                        >
                            ×
                        </button>
                    </div>
                ))}
            </div>
        </div>
    );
};

export default NotificationsHost;
