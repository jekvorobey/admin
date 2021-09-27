<template>
    <layout-main back>
        <b-row class="mb-2">
            <b-col cols="7">
                <b-card>
                    <infopanel
                        :model.sync="discount"
                        :option-discount-types="optionDiscountTypes"
                        :discount-statuses="discountStatuses"
                        :merchants="merchants"
                        :author="author"
                        :categories="categories"
                        :brands="brands"
                        @initDiscount="initDiscount"
                    ></infopanel>
                </b-card>
            </b-col>
            <b-col cols="5">
                <b-card>
                    <table class="table table-sm">
                        <thead>
                        <tr>
                            <th colspan="4">KPIs</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <th>Сумма заказов с использованием скидки</th>
                            <td>{{ KPI.orders_sum_with_discount }} ₽</td>
                        </tr>
                        <tr>
                            <th>Сумма, которое сэкономили покупатели</th>
                            <td>{{ KPI.saved_sum }} ₽</td>
                        </tr>
                        <tr>
                            <th>Количество пользователей, <br/>которые воспользовались скидкой</th>
                            <td>{{ KPI.customers_count }}</td>
                        </tr>
                        <tr>
                            <th>Количество заказов со скидкой</th>
                            <td>{{ KPI.orders_count }}</td>
                        </tr>
                        </tbody>
                    </table>
                </b-card>
            </b-col>
        </b-row>

        <b-card no-body>
            <b-tabs lazy card v-model="tabIndex">
                <b-tab v-for='(tab, key) in tabs' :key="key" :title="tab.title">
                    <tab-main v-if="key === 'main'"
                              :model.sync="discount"
                              :discounts="discounts"
                              :i-condition-types="iConditionTypes"
                              :iDeliveryMethods="iDeliveryMethods"
                              :iPaymentMethods="iPaymentMethods"
                              :regions="regions"
                              :roles="roles"
                              :segments="segments"
                              :brands="brands"
                              :categories="categories"
                              :i-districts="iDistricts"
                    />
                    <tab-order v-else-if="key === 'order'"
                       :model.sync="discount"
                    ></tab-order>
                    <template v-else>
                        Заглушка
                    </template>
                </b-tab>
            </b-tabs>
        </b-card>
    </layout-main>
</template>

<script>
    import Infopanel from './components/infopanel.vue';
    import TabMain from './components/tab-main.vue';
    import TabOrder from './components/tab-order.vue';
    import tabsMixin from '../../../../mixins/tabs.js';

    export default {
        name: 'page-discount-detail',
        components: {
            Infopanel,
            TabMain,
            TabOrder,
        },
        mixins: [tabsMixin],
        props: {
            discounts: Array,
            iDiscount: Object,
            optionDiscountTypes: Object,
            discountStatuses: Object,
            iConditionTypes: Object,
            iDeliveryMethods: Array,
            iPaymentMethods: Array,
            merchants: Array,
            author: Object,
            iDistricts: Array,
            brands: Array,
            categories: Array,
            roles: Array,
            KPI: Object,
        },
        data() {
            return {
                discount: {
                    name: null,
                    type: null,
                    value: null,
                    value_type: null,
                    start_date: null,
                    end_date: null,
                    offers: null,
                    bundles: null,
                    bundleItems: null,
                    status: 1, // STATUS_ACTIVE
                    limit: null,
                    brands: [],
                    categories: [],
                    conditions: [],
                    created_at: null,
                },

                // Тип условия на скидку
                CONDITION_TYPE_FIRST_ORDER: 1,
                CONDITION_TYPE_MIN_PRICE_ORDER: 2,
                CONDITION_TYPE_MIN_PRICE_BRAND: 3,
                CONDITION_TYPE_MIN_PRICE_CATEGORY: 4,
                CONDITION_TYPE_EVERY_UNIT_PRODUCT: 5,
                CONDITION_TYPE_DELIVERY_METHOD: 6,
                CONDITION_TYPE_PAY_METHOD: 7,
                CONDITION_TYPE_REGION: 8,
                CONDITION_TYPE_USER: 9,
                CONDITION_TYPE_ORDER_SEQUENCE_NUMBER: 10,
                CONDITION_TYPE_DISCOUNT_SYNERGY: 11,
            };
        },
        methods: {
            initDiscount() {
                if (!this.iDiscount) {
                    return;
                }

                let discount = {...this.iDiscount};
                discount.offers = Object.values(discount.offers).map(offer => offer.offer_id).join(',');
                discount.bundleItems = Object.values(discount.bundleItems).map(item => item.item_id).join(',');
                discount.brands = Object.values(discount.brands).map(brand => brand.brand_id);
                discount.categories = Object.values(discount.categories).map(category => category.category_id);

                let roles = discount.roles.map(role => role.role_id);
                let segments = discount.segments.map(segment => segment.segment_id);
                let conditionToObject = item => {
                    let cond = item.condition ? item.condition : {};
                    return  {
                        categories: ('categories' in cond) ? cond.categories : [],
                        deliveryMethods: ('deliveryMethods' in cond) ? cond.deliveryMethods : [],
                        paymentMethods: ('paymentMethods' in cond) ? cond.paymentMethods : [],
                        regions: ('regions' in cond) ? cond.regions : [],
                        roles: (item.type === this.CONDITION_TYPE_USER) ? roles : [],
                        segments: (item.type === this.CONDITION_TYPE_USER) ? segments : [],
                        sum: ('minPrice' in cond) ? cond.minPrice : "",
                        synergy: ('synergy' in cond) ? cond.synergy : [],
                        type: item.type,
                        users: ('customerIds' in cond) ? cond.customerIds : [],
                        count: ('count' in cond) ? cond.count : '',
                        offer: ('offer' in cond) ? cond.offer : '',
                        brands: ('brands' in cond) ? cond.brands : '',
                        sequenceNumber: ('orderSequenceNumber' in cond) ? cond.orderSequenceNumber : '',
                    }
                };

                discount.conditions = Object.values(discount.conditions).map(item => conditionToObject(item));
                if (discount.conditions.filter(item => item.type === this.CONDITION_TYPE_USER).length === 0) {
                    if (roles.length > 0 || segments.length > 0) {
                        discount.conditions.push(conditionToObject({
                            'type': this.CONDITION_TYPE_USER,
                            'condition': {
                                'roles': roles,
                                'segments': segments,
                            }
                        }));
                    }
                }

                this.discount = discount;
            },
        },
        computed: {
            tabs() {
                let tabs = {};
                let i = 0;

                tabs.main = {i: i++, title: 'Информация'};
                tabs.order = {i: i++, title: 'Заказы'};
                tabs.report = {i: i++, title: 'Отчеты'};
                tabs.log = {i: i++, title: 'Логи'};

                return tabs;
            },
            segments() {
                return [
                    {text: 'A', value: 1},
                    {text: 'B', value: 2},
                    {text: 'C', value: 3}
                ];
            },
            regions() {
                let regions = [];
                for (let i in this.iDistricts) {
                    let district = this.iDistricts[i];
                    district.regions.map(r => {
                        regions.push({text: r.name, value: r.id, group: district.name});
                    });
                }
                return regions;
            },
        },
        mounted() {
            this.initDiscount();
        },
    };
</script>
