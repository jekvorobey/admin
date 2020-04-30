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
import {FontAwesomeIcon} from '@fortawesome/vue-fontawesome';
import './fontawesome';
import {capitalize, formatSize, integer, lowercase, truncate} from '../scripts/filters';
import OrderStatus from './components/status/order-status.vue';
import PaymentStatus from './components/status/payment-status.vue';
import Media from '../scripts/media.js';
import * as moment from 'moment';
import {mapGetters} from 'vuex';

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
Vue.component('payment-status', PaymentStatus);

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
        datePrint(date) {
            return moment(date, "YYYY-MM-DD").format('LL');
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
        },
        /** @return {CommunicationChannelTypes} */
        communicationChannelTypes() {
            return this.$store.state.layout.communicationChannelTypes;
        },
        /** @return {CommunicationChannels} */
        communicationChannels() {
            return this.$store.state.layout.communicationChannels;
        },
        /** @return {CommunicationThemes} */
        communicationThemes() {
            return this.$store.state.layout.communicationThemes;
        },
        /** @return {CommunicationStatuses} */
        communicationStatuses() {
            return this.$store.state.layout.communicationStatuses;
        },
        /** @return {CommunicationTypes} */
        communicationTypes() {
            return this.$store.state.layout.communicationTypes;
        },
        /** @return {MerchantStatuses} */
        merchantStatuses() {
            return this.$store.state.layout.merchantStatuses;
        },
        /** @return {MerchantCommissionTypes} */
        merchantCommissionTypes() {
            return this.$store.state.layout.merchantCommissionTypes;
        },
        /** @return {PublicEventType[]} */
        publicEventTypes() {
            return this.$store.state.layout.publicEventTypes;
        },
        /** @return {PublicEventMediaTypes} */
        publicEventMediaTypes() {
            return this.$store.state.layout.publicEventMediaTypes;
        },
        /** @return {PublicEventMediaCollections} */
        publicEventMediaCollections() {
            return this.$store.state.layout.publicEventMediaCollections;
        },
        /** @return {PublicEventSprintStatus} */
        publicEventSprintStatus() {
            return this.$store.state.layout.publicEventSprintStatus;
        },
        /** @return {PublicEventStatus} */
        publicEventStatus() {
            return this.$store.state.layout.publicEventStatus;
        },
        /** @return {DeliveryType} */
        discountTypes() {
            return this.$store.state.layout.discountTypes;
        },
        /** @return {PromoCodeType} */
        promoCodeTypes() {
            return this.$store.state.layout.promoCodeTypes;
        },
        /** @return {PromoCodeStatus} */
        promoCodeStatus() {
            return this.$store.state.layout.promoCodeStatus;
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
/**
 @typedef CommunicationChannelTypes
 @type {Object}
 @property {number} internal_message
 @property {number} infinity
 @property {number} smsc
 @property {number} livetex_viber
 @property {number} livetex_telegram
 @property {number} livetex_fb
 @property {number} livetex_vk
 @property {number} internal_email
 */
/**
 @typedef CommunicationChannels
 @type {Object}
 @property {CommunicationChannel} {number}
 */
/**
 @typedef CommunicationChannel
 @type {Object}
 @property {number} id
 @property {string} name
 @property {string} created_at
 @property {string} updated_at
 */
/**
 @typedef CommunicationThemes
 @type {Object}
 @property {CommunicationTheme} {number}
 */
/**
 @typedef CommunicationTheme
 @type {Object}
 @property {number} id
 @property {string} name
 @property {boolean} active
 @property {number|null} channel_id
 @property {string} created_at
 @property {string} updated_at
 */
/**
 @typedef CommunicationStatuses
 @type {Object}
 @property {CommunicationStatus} {number}
 */
/**
 @typedef CommunicationStatus
 @type {Object}
 @property {number} id
 @property {string} name
 @property {boolean} active
 @property {boolean} default
 @property {number|null} channel_id
 @property {string} created_at
 @property {string} updated_at
 */
/**
 @typedef CommunicationTypes
 @type {Object}
 @property {CommunicationType} {number}
 */
/**
 @typedef CommunicationType
 @type {Object}
 @property {number} id
 @property {string} name
 @property {boolean} active
 @property {number|null} channel_id
 @property {string} created_at
 @property {string} updated_at
 */
/**
 @typedef MerchantStatuses
 @type {Object}
 @property {string} created
 @property {string} review
 @property {string} cancel
 @property {string} terms
 @property {string} activation
 @property {string} work
 @property {string} stop
 @property {string} close
 */
/**
 @typedef MerchantCommissionTypes
 @type {Object}
 @property {string} global
 @property {string} rating
 @property {string} merchant
 @property {string} brand
 @property {string} category
 @property {string} sku
 */
/**
 @typedef PublicEventType
 @type {Object}
 @property {number} id
 @property {string} name
 @property {string} code
 */
/**
 @typedef PublicEventMediaTypes
 @type {Object}
 @property {string} image
 @property {string} video
 @property {string} youtube
 */
/**
 @typedef PublicEventMediaCollections
 @type {Object}
 @property {string} catalog
 @property {string} detail
 @property {string} gallery
 @property {string} description
 @property {string} history
 */
/**
 @typedef DeliveryType
 @type {Object}
 @property {integer} offer
 @property {integer} bundle
 @property {integer} brand
 @property {integer} category
 @property {integer} delivery
 @property {integer} cartTotal
 */
/**
 @typedef PublicEventStatus
 @type {Object}
 @property {integer} created
 @property {integer} disabled
 @property {integer} active
 */
/**
 @typedef PublicEventSprintStatus
 @type {Object}
 @property {integer} created
 @property {integer} disabled
 @property {integer} ready
 @property {integer} in_process
 @property {integer} done
 */
/**
 @typedef PromoCodeStatus
 @type {Object}
 @property {integer} created
 @property {integer} sent
 @property {integer} checking
 @property {integer} active
 @property {integer} rejected
 @property {integer} paused
 @property {integer} expired
 @property {integer} test
 */
/**
 @typedef PromoCodeType
 @type {Object}
 @property {integer} discount
 @property {integer} delivery
 @property {integer} gift
 @property {integer} bonus
 */