<template>
    <div>
        <div class="card">
            <div class="card-header">
                Фильтр
            </div>
            <div class="card-body">
                <div class="row">
                    <f-input v-model="filter.orderNum" class="col-md-4 col-sm-12">Номер заказа</f-input>
                    <f-input v-model="filter.buyer" class="col-md-8 col-sm-12">Покупатель</f-input>
                </div>
                <div class="row">
                    <f-date v-model="filter.orderDateFrom" class="col-lg-2 col-md-3 col-sm-12">Дата заказа от</f-date>
                    <f-date v-model="filter.orderDateTo" class="col-lg-2 col-md-3 col-sm-12">Дата заказа до</f-date>
                    <f-input v-model="filter.orderCostFrom" class="col-lg-3 col-md-3 col-sm-12">Сумма заказа до скидки (от...)</f-input>
                    <f-input v-model="filter.orderCostTo" class="col-lg-3 col-md-3 col-sm-12">Сумма заказа до скидки (до...)</f-input>
                </div>
                <div class="row">
                    <f-select
                            v-model="filter.orderStatus"
                            :options="orderStatusesOptions"
                            class="col-md-4 col-sm-12"
                    >Статус заказа</f-select>
                    <f-input v-model="filter.orderPriceFrom" class="col-lg-3 col-md-3 col-sm-12">Сумма заказа после скидки (от...)</f-input>
                    <f-input v-model="filter.orderPriceTo" class="col-lg-3 col-md-3 col-sm-12">Сумма заказа после скидки (до...)</f-input>
                </div>
            </div>
            <div class="card-footer">
                <button class="btn btn-sm btn-dark" @click="load">Применить</button>
                <button class="btn btn-sm btn-outline-dark" @click="clearFilter">Очистить</button>
            </div>
        </div>

        <table class="table table-condensed">
            <thead>
            <tr>
                <th>№ заказа</th>
                <th>Дата заказа</th>
                <th>Сумма до скидки</th>
                <th>Сумма после скидки</th>
                <th>Покупатель</th>
                <th>Статус заказа</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="order in orders">
                <td>{{ order.number }}</td>
                <td>{{ getDate(order.created_at) }}</td>
                <td>{{ parseInt(order.cost) }} ₽</td>
                <td>{{ parseInt(order.cost) - parseInt(order.discount) }} ₽</td>
                <td>{{ customerName(order.customer_id) }}</td>
                <td>{{ orderStatusesNames[order.status] }}</td>
            </tr>
            </tbody>
        </table>

        <b-pagination
                v-if="pager.count > pager.perPage"
                v-model="pager.page"
                :total-rows="pager.count"
                :per-page="pager.perPage"
                class="mt-3 float-right"
        />
    </div>

</template>

<script>
    import FInput from "../../../../../components/filter/f-input.vue";
    import FDate from '../../../../../components/filter/f-date.vue';
    import FSelect from '../../../../../components/filter/f-select.vue';
    import Services from "../../../../../../scripts/services/services";
    import moment from 'moment';

    export default {
        name: 'tab-order',
        components: {
            FInput,
            FDate,
            FSelect,
        },
        props: {
            model: Object,
        },
        data() {
            return {
                orders: [],
                customers: {},
                pager: {
                    page: 1,
                    count: 1,
                    perPage: 15,
                },
                filter: {
                    orderNum: null,
                    orderDateFrom: null,
                    orderDateTo: null,
                    orderStatus: null,
                    orderCostFrom: null,
                    orderCostTo: null,
                    orderPriceFrom: null,
                    orderPriceTo: null,
                    buyer: null,
                }
            }
        },
        computed: {
            discount: {
                get() {return this.model},
                set(value) {this.$emit('update:discount', value)},
            },
            orderStatusesNames() {
                let names = {};
                for (let k in this.orderStatuses) {
                    let status = this.orderStatuses[k];
                    names[status.id] = status.name;
                }
                return names;
            },
            orderStatusesOptions() {
                let options = [];
                for (let k in this.orderStatuses) {
                    let status = this.orderStatuses[k];
                    options.push({
                        value: status.id,
                        text: status.name,
                    });
                }
                return options;
            }
        },
        methods: {
            load() {
                let filter = {};
                for (let k in this.filter) {
                    if (this.filter[k]) {
                        filter[k] = this.filter[k];
                    }
                }

                Services.showLoader();
                Services.net().get(
                    this.getRoute('discount.orders', {id: this.discount.id}), {
                        page: this.pager.page,
                        perPage: this.pager.perPage,
                        filter: filter,
                    }
                ).then(data => {
                    this.orders = data.orders;
                    this.customers = data.customers;
                    this.pager.count = data.count.total;
                }, () => {
                    Services.msg('Ошибка при загрузке заказов', 'danger');
                }).finally(() => {
                    Services.hideLoader();
                });
            },
            clearFilter() {
                this.filter = {
                    orderNum: null,
                    orderDateFrom: null,
                    orderDateTo: null,
                    orderStatus: null,
                    orderCostFrom: null,
                    orderCostTo: null,
                    orderPriceFrom: null,
                    orderPriceTo: null,
                    buyer: null,
                }
            },
            customerName(customerId) {
                if (customerId in this.customers) {
                    return this.customers[customerId];
                }
                return '–';
            },
            getDate(str) {
                if (!str) {
                    return '–';
                }

                return moment(str).format('DD.MM.YYYY');
            },
        },
        mounted() {

        },
        created() {
            this.load();
        },
        watch: {
            'pager.page': 'load',
        },
    };
</script>

<style scoped>
    .condition-list {
        line-height: 0.9;
        margin-top: 10px;
    }
</style>
