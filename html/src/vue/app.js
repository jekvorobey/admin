import '../scripts/common';
import Vue from 'vue';
import Helpers from '../scripts/helpers';
import 'core-js/modules/es.promise';
import 'core-js/modules/es.array.iterator';
import 'whatwg-fetch';
import 'lazysizes/plugins/object-fit/ls.object-fit';
import 'lazysizes';
import 'lazysizes/plugins/parent-fit/ls.parent-fit';
import 'lazysizes/plugins/respimg/ls.respimg';
import store from './store/store';
import Services from '../scripts/services/services';
import BootstrapVue from 'bootstrap-vue'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import './fontawesome';

Vue.use(BootstrapVue);

//Font Awesome Icons
Vue.component('fa-icon', FontAwesomeIcon);

// Boot the current Vue component
const root = document.getElementById('app');

store.commit('layout', JSON.parse(root.dataset.layout));
store.commit('env', JSON.parse(root.dataset.env));
store.commit('title', root.dataset.title);
store.commit('routes', JSON.parse(root.dataset.routes));

Services.instance().register('store', () => {
    return store;
});

Services.instance().register('event', () => {
    return new Vue();
});

Vue.mixin({
    methods: {
        preparePrice(number, decimals, dec_point, thousands_sep) {
            return Helpers.preparePrice(number, decimals, dec_point, thousands_sep);
        },
        pluralForm(n, forms) {
            return Helpers.plural_form(n, forms);
        },
        strToList(str) {
            if (!str) {
                return [];
            }
            return String(str).split('\n');
        },
        changeModal(modal, flag) {
            this.$store.commit('modals', {modal, flag});
        },
        route(name) {
            return '/' + this.$store.state.routes[name].replace(/^\//, '');
        },
    },
    computed: {
        breadcrumbsItems() {
            return this.$store.state.layout.breadcrumbs.map(item => ({
                text: item.title,
                href: item.url,
            }));
        },
        staticText() {
            return this.$store.state.layout.staticBlock;
        },
        isGuest() {
            return this.$store.state.layout.isGuest;
        }
    },
});
