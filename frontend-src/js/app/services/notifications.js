/**
 * Notification store/service.
 *
 * Keep this file framework-agnostic so any component or helper can call
 * showNotification() without knowing how the UI is rendered.
 */

const listeners = new Set();
const timers = new Map();

let notifications = [];
let notificationSequence = 0;

const allowedTypes = ['success', 'info', 'error', 'warning'];

const filterType = (type) => (allowedTypes.includes(type) ? type : 'info');

/**
 * Create a shallow clone of a notification object, ensuring the errors array is also cloned if it exists.
 *
 * @param notification
 * @returns {*&{errors: *[]}}
 */
const cloneNotification = (notification) => ({
    ...notification,
    errors: Array.isArray(notification.errors) ? [...notification.errors] : []
});

const notifyListeners = () => {
    const snapshot = notifications.map(cloneNotification);

    listeners.forEach((listener) => {
        listener(snapshot);
    });
};

const clearTimer = (notificationId) => {
    const timerId = timers.get(notificationId);

    if (timerId) {
        clearTimeout(timerId);
        timers.delete(notificationId);
    }
};

/**
 * Remove a single notification from the store.
 *
 * @param {string} notificationId
 */
export const closeNotification = (notificationId) => {
    clearTimer(notificationId);

    const nextNotifications = notifications.filter((notification) => notification.id !== notificationId);

    if (nextNotifications.length === notifications.length) {
        return;
    }

    notifications = nextNotifications;
    notifyListeners();
};

/**
 * Clear all active notifications.
 */
export const clearNotifications = () => {
    notifications.forEach((notification) => {
        clearTimer(notification.id);
    });

    notifications = [];
    notifyListeners();
};

/**
 * Subscribe to notification state changes.
 *
 * @param {Function} listener
 * @returns {Function}
 */
export const subscribeNotifications = (listener) => {
    if (typeof listener !== 'function') {
        return () => {};
    }

    listeners.add(listener);
    listener(notifications.map(cloneNotification));

    return () => {
        listeners.delete(listener);
    };
};

/**
 * Publish a notification that can be rendered by the global host.
 *
 * @param {string} message
 * @param {string} type
 * @param {number} autoHideDelay
 * @param {Object} options
 * @returns {string|null}
 */
export function showNotification(message, type = 'info', autoHideDelay = 5000, options = {}) {
    // Merge passed options with default options.
    options = { ...{
            autoHide: true,
            autoHideDelay: autoHideDelay
        }, ...options };

    console.log('showNotification for seconds', options.autoHideDelay / 1000);

    const notification = {
        id: `notification-${Date.now()}-${++notificationSequence}`,
        message: message || '',
        type: filterType(type),
        autoHide: options.autoHide,
        autoHideDelay: options.autoHideDelay
    };

    notifications = [...notifications, notification];
    notifyListeners();

    if (notification.autoHide && notification.autoHideDelay > 0) {
        const timerId = setTimeout(() => {
            closeNotification(notification.id);
        }, notification.autoHideDelay);

        timers.set(notification.id, timerId);
    }

    return notification.id;
}
