export function truncate(value, length) {
    length = length || 15;
    if (!value || typeof value !== 'string') {
        return '';
    }
    if (value.length <= length) {
        return value;
    }
    return value.substring(0, length) + '...';
}

export function capitalize(value) {
    if (!value && value !== 0) {
        return '';
    }
    value = value.toString();
    return value.charAt(0).toUpperCase() + value.slice(1);
}

export function lowercase(value) {
    return (value || value === 0)
        ? value.toString().toLowerCase()
        : '';
}

export function formatSize(size) {
    if (size > 1024 * 1024 * 1024 * 1024) {
        return (size / 1024 / 1024 / 1024 / 1024).toFixed(2) + ' TB';
    } else {
        if (size > 1024 * 1024 * 1024) {
            return (size / 1024 / 1024 / 1024).toFixed(2) + ' GB';
        } else {
            if (size > 1024 * 1024) {
                return (size / 1024 / 1024).toFixed(2) + ' MB';
            } else {
                if (size > 1024) {
                    return (size / 1024).toFixed(2) + ' KB'
                }
            }
        }
    }
    return size.toString() + ' B'
}

export function integer(value) {
    return parseInt(value);
}