export default class LandingFieldsStorage {
    static key(id, field) {
        let today = this.getToday();
        return 'landing-' + today.getTime() + '-' + id + '-' + field;
    }

    static checkLocalStorageExistence() {
        return 'localStorage' in window && window['localStorage'] !== null;
    }

    static init() {
        if (this.checkLocalStorageExistence()) {
            this.clearOutdatedData();
        }
    }

    static clearOutdatedData() {
        if (this.checkLocalStorageExistence()) {
            let today = this.getToday();
            for (let i = 0; i < localStorage.length; i++) {
                let key = localStorage.key(i);
                let keyParts = key.split('-');
                if (keyParts[0] === 'landing' && keyParts[1] && new Date(parseInt(keyParts[1])) < today) {
                    localStorage.removeItem(key);
                }
            }
        }
    }

    static set(id, field, value) {
        if (id && field) {
            localStorage[this.key(id, field)] = value;
        }
    }

    static get(id, field) {
        if (id && field) {
            return localStorage[this.key(id, field)];
        }

        return null;
    }

    static getToday() {
        let now = new Date();
        return new Date(now.getFullYear(), now.getMonth(), now.getDate());
    }
};
