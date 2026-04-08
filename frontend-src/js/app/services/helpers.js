/**
 * Helper utilities.
 */

/**
 * Convert a string to a URL-friendly slug.
 * @param {string} value
 * @returns {string}
 */
export const slugify = (value) =>
    value
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-');
