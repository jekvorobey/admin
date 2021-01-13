export default {
    filters: {
        datetime(value) {
            if (value) {
                const parts = value.split(' ');
                if (parts.length === 2) {
                    return parts[0].split('-').reverse().join('.') + ' ' + parts[1];
                }
            }
            return value;
        },
    }
}
