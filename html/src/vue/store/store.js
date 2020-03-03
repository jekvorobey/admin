import Vuex from 'vuex';
import Vue from 'vue';
import NetService from '../../scripts/services/net';

import ModalModule from './modules/modal.js';
import products from './modules/products.js';

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
        products,
    },
    mutations: {
        loaderShow(state, loaderShow) {
            state.loaderShow = loaderShow;
        },
        title(state, data) {
            state.title = data;
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
