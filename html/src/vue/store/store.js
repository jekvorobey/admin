import Vuex from 'vuex';
import Vue from 'vue';
import NetService from "../../scripts/services/net";

Vue.use(Vuex);

export default new Vuex.Store({
    state: {
        title: '',
        layout: {},
        env: {},
        routes: {},
        modals: {
            message: false,
        },
        modal_message: '',
    },
    mutations: {
        title(state, data) {
            state.title = data;
        },
        env(state, data) {
            state.env = data;
        },
        layout(state, data) {
            state.layout = data;
        },
        modals(state, data) {
            const names = Object.keys(state.modals);
            for (let i = 0; i < names.length; i++) {
                state.modals[names[i]] = false;
            }
            state.modals[data.modal] = data.flag;
        },
        modal_message(state, message) {
            state.modal_message = message;
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
    actions: {
        modal_message({ commit }, message) {
            commit('modal_message', message);
            commit('modals', { modal: 'message', flag: true });
        },
    },
});
