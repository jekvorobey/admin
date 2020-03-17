/**
 * @param text
 * @returns {*}
 */
export const escape_html = (text) => {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;',
    };

    return text && text.length
        ? text.replace(/[&<>"']/g, (m) => map[m])
        : text;
};

/**
 * @param text
 * @returns {*}
 */
export const decode_escaped_html = (text) => {
    return text && text.length
        ? text.replace(/&amp;/g, '&')
            .replace(/&lt;/g, '<')
            .replace(/&gt;/g, '>')
            .replace(/&quot;/g, '"')
            .replace(/&#039;/g, "'")
        : text;
};
