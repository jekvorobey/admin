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
import BootstrapVue from 'bootstrap-vue';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import './fontawesome';
import { capitalize, formatSize, integer, lowercase, truncate } from '../scripts/filters';
import OrderStatus from './components/order-status.vue';
import Media from '../scripts/media.js';
import * as moment from 'moment';
import { mapGetters } from 'vuex';

Vue.use(BootstrapVue);

//Font Awesome Icons
Vue.component('fa-icon', FontAwesomeIcon);

//Filters
Vue.filter('capitalize', capitalize);
Vue.filter('lowercase', lowercase);
Vue.filter('truncate', truncate);
Vue.filter('formatSize', formatSize);
Vue.filter('integer', integer);

// Boot the current Vue component
const root = document.getElementById('app');

store.commit('layout', JSON.parse(root.dataset.layout));
store.commit('env', JSON.parse(root.dataset.env));
store.commit('title', root.dataset.title);
store.commit('routes', JSON.parse(root.dataset.routes));

Vue.component('order-status', OrderStatus);

Services.instance().register('store', () => {
    return store;
});

Services.instance().register('event', () => {
    return new Vue();
});

moment.locale('ru');
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
        datetimePrint(date) {
            return moment(date, "YYYY-MM-DD HH:mm:ss").format('LLL');
        },
    },
    computed: {
        ...mapGetters(['getRoute']),
        staticText() {
            return this.$store.state.layout.staticBlock;
        },
        /** @return {User} */
        user() {
            return this.$store.state.layout.user;
        },
        /** @return {UserRoles} */
        userRoles() {
            return this.$store.state.layout.userRoles;
        },
        customerStatusByRole() {
            return this.$store.state.layout.customerStatusByRole;
        },
        customerStatusName() {
            return this.$store.state.layout.customerStatusName;
        },
        /** @return {CustomerStatus} */
        customerStatus() {
            return this.$store.state.layout.customerStatus;
        },
        /** @return {Media} */
        media() {
            return Media;
        }
    },
});

/**
 @typedef CustomerStatus
 @type {Object}
 @property {number} created
 @property {number} new
 @property {number} consideration
 @property {number} rejected
 @property {number} active
 @property {number} problem
 @property {number} block
 @property {number} potential_rp
 @property {number} temporarily_suspended
 */
/**
 @typedef User
 @type {Object}
 @property {boolean} isGuest - isGuest
 @property {boolean} isSuper - isSuper
 */
/**
 @typedef UserRoles
 @type {Object}
 @property {ShowcaseUserRoles} showcase
 @property {ICommerceMlUserRoles} i_commerce_ml
 @property {MasUserRoles} mas
 @property {AdminUserRoles} admin
 */
/**
 @typedef ShowcaseUserRoles
 @type {Object}
 @property {number} referral_partner
 @property {number} professional
 */
/**
 @typedef ICommerceMlUserRoles
 @type {Object}
 @property {number} external_system
 */
/**
 @typedef MasUserRoles
 @type {Object}
 @property {number} merchant_operator
 @property {number} merchant_admin
 */
/**
 @typedef AdminUserRoles
 @type {Object}
 @property {number} manager_client
 @property {number} manager_merchant
 @property {number} admin
 @property {number} super
 */
