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
import DeliveryStatus from './components/status/delivery-status.vue';
import ShipmentStatus from './components/status/shipment-status.vue';
import PaymentStatus from './components/status/payment-status.vue';
import CargoStatus from './components/status/cargo-status.vue';
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
Vue.component('delivery-status', DeliveryStatus);
Vue.component('shipment-status', ShipmentStatus);
Vue.component('payment-status', PaymentStatus);
Vue.component('cargo-status', CargoStatus);

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
            return moment(date, "YYYY-MM-DD").format('L');
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
        },
        /** @return {BonusValueTypes} */
        bonusValueTypes() {
            return this.$store.state.layout.bonusValueTypes;
        },
        /** @return {BonusTypes} */
        bonusTypes() {
            return this.$store.state.layout.bonusTypes;
        },
        /** @return {CustomerBonusStatus} */
        customerBonusStatus() {
            return this.$store.state.layout.customerBonusStatus;
        },
        /** @return {OrderStatuses} */
        orderStatuses() {
            return this.$store.state.layout.orderStatuses;
        },
        /** @return {PaymentStatuses} */
        paymentStatuses() {
            return this.$store.state.layout.paymentStatuses;
        },
        /** @return {PaymentMethods} */
        paymentMethods() {
            return this.$store.state.layout.paymentMethods;
        },
        /** @return {DeliveryStatuses} */
        deliveryStatuses() {
            return this.$store.state.layout.deliveryStatuses;
        },
        /** @return {ShipmentStatuses} */
        shipmentStatuses() {
            return this.$store.state.layout.shipmentStatuses;
        },
        /** @return {CargoStatuses} */
        cargoStatuses() {
            return this.$store.state.layout.cargoStatuses;
        },
        /** @return {DeliveryTypes} */
        deliveryTypes() {
            return this.$store.state.layout.deliveryTypes;
        },
        /** @return {DeliveryMethods} */
        deliveryMethods() {
            return this.$store.state.layout.deliveryMethods;
        },
        /** @return {DeliveryServices} */
        deliveryServices() {
            return this.$store.state.layout.deliveryServices;
        },
        /** @return {OfferAllSaleStatuses} */
        offerAllSaleStatuses() {
            return this.$store.state.layout.offerAllSaleStatuses;
        },
        /** @return {OfferCreateSaleStatuses} */
        offerCreateSaleStatuses() {
            return this.$store.state.layout.offerCreateSaleStatuses;
        },
        /** @return {OfferEditSaleStatuses} */
        offerEditSaleStatuses() {
            return this.$store.state.layout.offerEditSaleStatuses;
        },
        /** @return {OfferCountdownSaleStatuses} */
        offerCountdownSaleStatuses() {
            return this.$store.state.layout.offerCountdownSaleStatuses;
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
 @property {integer} bundleOffer
 @property {integer} bundleMasterclass
 @property {integer} brand
 @property {integer} category
 @property {integer} delivery
 @property {integer} cartTotal
 @property {integer} anyOffer
 @property {integer} anyBundle
 @property {integer} anyBrand
 @property {integer} anyCategory
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
 /**
 @typedef BonusValueTypes
 @type {Object}
 @property {integer} percent
 @property {integer} absolute
 */
/**
 @typedef BonusTypes
 @type {Object}
 @property {integer} offer
 @property {integer} brand
 @property {integer} category
 @property {integer} service
 @property {integer} cartTotal
 @property {integer} anyOffer
 @property {integer} anyBrand
 @property {integer} anyCategory
 @property {integer} anyService
 */
/**
 @typedef CustomerBonusStatus
 @type {Object}
 @property {integer} onHold
 @property {integer} active
 @property {integer} expired
 @property {integer} debited
 */
/**
 @typedef OrderStatuses - статусы заказа
 @type {Object}
 @property {OrderStatus} created - оформлен
 @property {OrderStatus} awaitingCheck - ожидает проверки АОЗ
 @property {OrderStatus} checking - проверка АОЗ
 @property {OrderStatus} awaitingConfirmation - ожидает подтверждения Мерчантом
 @property {OrderStatus} inProcessing - в обработке
 @property {OrderStatus} transferredToDelivery - передан на доставку
 @property {OrderStatus} delivering - в процессе доставки
 @property {OrderStatus} readyForRecipient - находится в Пункте Выдачи
 @property {OrderStatus} done - доставлен
 @property {OrderStatus} returned - возвращен
 @property {OrderStatus} preOrder - предзаказ: ожидаем поступления товара
 */
/**
 @typedef OrderStatus - статус заказа
 @type {Object}
 @property {integer} id
 @property {string} name - название в админке
 @property {string} description - все Отправления данного Заказа были переведены в статус /// или Смысл статуса если оно не зависит от Отправлений
 @property {string} display_name - название для клиента на витрине
 */
/**
 @typedef PaymentStatuses - статусы оплаты
 @type {Object}
 @property {PaymentStatus} notPaid - не оплачено
 @property {PaymentStatus} paid - оплачено
 @property {PaymentStatus} timeout - просрочено
 @property {PaymentStatus} hold - средства захолдированы
 @property {PaymentStatus} error - ошибка
 */
/**
 @typedef PaymentStatus - статус оплаты
 @type {Object}
 @property {integer} id
 @property {string} name
 */
/**
 @typedef PaymentMethods - способы оплаты
 @type {Object}
 @property {PaymentMethod} online - онлайн
 */
/**
 @typedef PaymentMethod - способ оплаты
 @type {Object}
 @property {integer} id
 @property {string} name
 */
/**
 @typedef DeliveryStatuses - статусы доставки
 @type {Object}
 @property {DeliveryStatus} created - оформлена
 @property {DeliveryStatus} awaitingCheck - ожидает проверки АОЗ
 @property {DeliveryStatus} checking - проверка АОЗ
 @property {DeliveryStatus} awaitingConfirmation - ожидает подтверждения Мерчантом
 @property {DeliveryStatus} assembling - на комплектации
 @property {DeliveryStatus} assembled - готова к отгрузке
 @property {DeliveryStatus} shipped - передана Логистическому Оператору
 @property {DeliveryStatus} onPointIn - принята логистическим оператором (принята на склад в пункте отправления)
 @property {DeliveryStatus} arrivedAtDestinationCity - прибыла в город назначения
 @property {DeliveryStatus} onPointOut - принята в пункте назначения (принята на складе в пункте назначения)
 @property {DeliveryStatus} readyForRecipient - находится в Пункте Выдачи (готова к выдаче в пункте назначения)
 @property {DeliveryStatus} delivering - выдана курьеру для доставки (передана на доставку в пункте назначения)
 @property {DeliveryStatus} done - доставлена получателю
 @property {DeliveryStatus} cancellationExpected - ожидается отмена
 @property {DeliveryStatus} returnExpectedFromCustomer - ожидается возврат от клиента
 @property {DeliveryStatus} returned - возвращена
 @property {DeliveryStatus} preOrder - предзаказ: ожидаем поступления товара
 */
/**
 @typedef DeliveryStatus - статус доставки
 @type {Object}
 @property {integer} id
 @property {string} name - название в админке
 @property {string} display_name - название для клиента на витрине
 */
/**
 @typedef ShipmentStatuses - статусы отправления
 @type {Object}
 @property {ShipmentStatus} created - оформлено
 @property {ShipmentStatus} awaitingCheck - ожидает проверки АОЗ
 @property {ShipmentStatus} checking - проверка АОЗ
 @property {ShipmentStatus} awaitingConfirmation - ожидает подтверждения Мерчантом
 @property {ShipmentStatus} assembling - на комплектации
 @property {ShipmentStatus} assembled - готова к отгрузке
 @property {ShipmentStatus} shipped - передано Логистическому Оператору
 @property {ShipmentStatus} onPointIn - принято логистическим оператором (принята на склад в пункте отправления)
 @property {ShipmentStatus} arrivedAtDestinationCity - прибыло в город назначения
 @property {ShipmentStatus} onPointOut - принято в пункте назначения (принята на складе в пункте назначения)
 @property {ShipmentStatus} readyForRecipient - находится в Пункте Выдачи (готова к выдаче в пункте назначения)
 @property {ShipmentStatus} delivering - выдано курьеру для доставки (передана на доставку в пункте назначения)
 @property {ShipmentStatus} done - доставлено получателю
 @property {ShipmentStatus} cancellationExpected - ожидается отмена
 @property {ShipmentStatus} returnExpectedFromCustomer - ожидается возврат от клиента
 @property {ShipmentStatus} returned - возвращено
 @property {ShipmentStatus} preOrder - предзаказ: ожидаем поступления товара
 */
/**
 @typedef ShipmentStatus - статус отправления
 @type {Object}
 @property {integer} id
 @property {string} name
 */
/**
 @typedef CargoStatuses - статусы груза
 @type {Object}
 @property {CargoStatus} created - сформирован
 @property {CargoStatus} shipped - передан логистическому оператору
 @property {CargoStatus} taken - принят Логистическим Оператором
 */
/**
 @typedef CargoStatus - статус груза
 @type {Object}
 @property {integer} id
 @property {string} name
 */
/**
 @typedef DeliveryTypes - типы доставки
 @type {Object}
 @property {DeliveryType} split - несколькими доставками
 @property {DeliveryType} consolidation - одной доставкой
 */
/**
 @typedef DeliveryType - тип доставки
 @type {Object}
 @property {integer} id
 @property {string} name
 */
/**
 @typedef DeliveryMethods - методы доставки
 @type {Object}
 @property {DeliveryMethod} delivery - курьерская доставка
 @property {DeliveryMethod} pickup - самовывоз
 */
/**
 @typedef DeliveryMethod - метод доставки
 @type {Object}
 @property {integer} id
 @property {string} name
 */
/**
 @typedef DeliveryServices - службы доставки
 @type {Object}
 @property {DeliveryService} b2cpl
 @property {DeliveryService} cdek
 */
/**
 @typedef DeliveryService - служба доставки
 @type {Object}
 @property {integer} id
 @property {string} name
 */
/**
 @typedef OfferAllSaleStatuses - все статусы оффера
 @type {Object}
 @property {OfferSaleStatus} onSale - в продаже
 @property {OfferSaleStatus} preOrder - предзаказ
 @property {OfferSaleStatus} outSale - снято с продажи
 @property {OfferSaleStatus} availableSale - доступен к продаже
 @property {OfferSaleStatus} notAvailableSale - недоступен к продаже
 */
/**
 @typedef OfferCreateSaleStatuses - доступные при создании оффера статусы
 @type {Object}
 @property {OfferSaleStatus} onSale - в продаже
 @property {OfferSaleStatus} preOrder - предзаказ
 */
/**
 @typedef OfferEditSaleStatuses - доступные при редактировании оффера статусы
 @type {Object}
 @property {OfferSaleStatus} preOrder - предзаказ
 @property {OfferSaleStatus} outSale - снято с продажи
 */
/**
 @typedef OfferSaleStatus - статус оффера
 @type {Object}
 @property {integer} id
 @property {string} name
 */
/**
 @typedef OfferCountdownSaleStatuses - статусы оффера, для которых необходимо указать дату начала продажи
 @type {Object}
 @property {integer} id
 */