/* eslint-disable require-jsdoc */

import NetService from './net';

let services_instance;
export default class Services {
    constructor() {
        this.services = {};

        this.register('net', function() {
            return new NetService();
        });
    }

    static instance() {
        if (services_instance === undefined) {
            services_instance = new Services();
        }
        return services_instance;
    }

    /**
     *
     * @param {string} name
     * @param {function(Services)} cb
     */
    register(name, cb) {
        this.services[name] = {
            cb,
            instance: undefined,
        };
    }

    /**
     * Получить сервис.
     *
     * @param {string} name
     * @returns {*}
     */
    get(name) {
        if (this.services[name] === undefined) {
            throw new Error(`Service '${name}' is not registered.`);
        }
        if (this.services[name].instance === undefined) {
            this.services[name].instance = this.services[name].cb(this);
        }

        return this.services[name].instance;
    }

    /**
     * @return {NetService}
     */
    static net() {
        return Services.instance().get('net');
    }

    /**
     * @return {Vuex}
     */
    static store() {
        return Services.instance().get('store');
    }

    /**
     * @return {Vuex}
     */
    static event() {
        return Services.instance().get('event');
    }

    static showLoader() {
        return Services.store().commit('loaderShow', true);
    }

    static hideLoader() {
        return Services.store().commit('loaderShow', false);
    }

    static msg(text, variant) {
        Services.event().$emit('toast', {text, variant});
    }
}
