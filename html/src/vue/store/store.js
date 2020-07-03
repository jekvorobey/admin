import Vuex from 'vuex';
import Vue from 'vue';
import NetService from '../../scripts/services/net';

import ModalModule from './modules/modal.js';
import massSelection from './modules/mass-selection.js';

import products from './modules/products.js';
import brands from './modules/brands.js';
import organizers from './modules/organizers.js';
import publicEvents from './modules/public-events.js';
import places from './modules/places.js';
import speakers from './modules/speakers.js';
import types from './modules/types.js';
import serviceNotifications from './modules/service-notifications.js'


Vue.use(Vuex);

export default new Vuex.Store({
    state: {
        title: '',
        layout: {},
        env: {},
        routes: {},
        loaderShow: false,
    },
    modules: {
        modal: ModalModule(),
        massSelection,
        products,
        brands,
        places,
        publicEvents,
        organizers,
        speakers,
        types,
        serviceNotifications,
    },
    mutations: {
        loaderShow(state, loaderShow) {
            state.loaderShow = loaderShow;
        },
        title(state, data) {
            state.title = data;
            document.title = data;
        },
        env(state, data) {
            state.env = data;
        },
        layout(state, data) {
            state.layout = data;
        },
        routes(state, routes) {
            state.routes = routes;
        },

    },
    getters: {
        getRoute: state => (name, params = {}) => {
            let r = state.routes[name];
            if (!r) return;
            let {uri} = NetService.prepareUri(r, params);
            return '/' + uri.replace(/^\//, '');
        }
    },
});
