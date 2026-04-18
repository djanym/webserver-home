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

const cloneNotification = (notification) => ({
    ...notification,
    errors: Array.isArray(notification.errors) ? [...notification.errors] : []
});

const normalizeType = (type) => (allowedTypes.includes(type) ? type : 'info');

const normalizeText = (value) => {
    if (typeof value === 'string') {
        return value.trim();
    }

    if (value === null || value === undefined) {
        return '';
    }

    return String(value).trim();
};

const normalizeList = (value) => {
    if (!Array.isArray(value)) {
        return [];
    }

    return value
        .map((item) => normalizeText(item))
        .filter(Boolean);
};

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
 * Supports the legacy signature showNotification(message, type, autoHide, autoHideDelay)
 * and the newer options object showNotification(message, type, { autoHide, autoHideDelay, errors }).
 *
 * @param {string} message
 * @param {string} type
 * @param {boolean|Object} options
 * @param {number} legacyAutoHideDelay
 * @returns {string|null}
 */
export function showNotification(message, type = 'info', options = true, legacyAutoHideDelay = 5000) {
    const resolvedMessage = normalizeText(message);

    if (!resolvedMessage) {
        return null;
    }

    const isLegacySignature = typeof options === 'boolean';
    const resolvedOptions = isLegacySignature
        ? { autoHide: options, autoHideDelay: legacyAutoHideDelay }
        : (options && typeof options === 'object' ? options : {});

    const autoHide = resolvedOptions.autoHide !== undefined ? Boolean(resolvedOptions.autoHide) : true;
    const autoHideDelay = Number.isFinite(Number(resolvedOptions.autoHideDelay))
        ? Number(resolvedOptions.autoHideDelay)
        : 5000;

    const notification = {
        id: `notification-${Date.now()}-${++notificationSequence}`,
        message: resolvedMessage,
        type: normalizeType(type),
        autoHide,
        autoHideDelay,
        errors: normalizeList(resolvedOptions.errors),
        meta: resolvedOptions.meta || null,
        createdAt: new Date().toISOString()
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
