<template>
    <div>
        <div class="card">
            <div class="card-header">
                Фильтр
            </div>
            <div class="card-body">
                <div class="row">
                    <f-input v-model="filter.orderNum" class="col-md-2 col-sm-12">Номер заказа</f-input>
                    <f-input v-model="filter.buyer" class="col-md-2 col-sm-12">Покупатель</f-input>
                    <f-input v-model="filter.addr" class="col-md-2 col-sm-12">Адрес</f-input>
                </div>
                <div class="row">
                    <f-date v-model="filter.orderDateFrom"
                            class="col-lg-2 col-md-3 col-sm-12"
                    >Дата заказа от</f-date>
                    <f-date v-model="filter.orderDateTo"
                            class="col-lg-2 col-md-3 col-sm-12"
                    >Дата заказа до</f-date>
                    <f-date v-model="filter.deliveryDateFrom"
                            class="col-lg-2 col-md-3 col-sm-12"
                    >Дата доставки от</f-date>
                    <f-date v-model="filter.deliveryDateTo"
                            class="col-lg-2 col-md-3 col-sm-12"
                    >Дата доставки до</f-date>
                </div>
            </div>
            <div class="card-footer">
                <button class="btn btn-sm btn-dark">Применить</button>
                <button class="btn btn-sm btn-outline-dark">Очистить</button>
            </div>
        </div>
        <table class="table table-condensed">
            <thead>
                <tr>
                    <th>№ заказа/№ отправления</th>
                    <th>Дата заказа</th>
                    <th>Дата доставки</th>
                    <th>Сумма</th>
                    <th>Покупатель</th>
                    <th>Адрес доставки</th>
                    <th>Дата отгрузки</th>
                    <th>Статус заказа</th>
                    <th>Статус отправления</th>
                </tr>
            </thead>
            <tbody v-for="(order, index) in orderList" class="table table-striped">
            <tr class="table">
                <td>{{ order.number }} / {{ order.delivery_number }}</td>
                <td>{{ getDate(order.created_at) }}</td>
                <td>{{ getDate(order.delivery_at) }}</td>
                <td>{{ parseInt(order.cost) }} р.</td>
                <td>{{ order.receiver_name }}</td>
                <td>{{ getAdress(JSON.parse(order.delivery_address)) }}</td>
                <td>{{ order.fsd }}</td>
                <td>{{ getStatusName(order.status) }}</td>
                <td>{{ order.status_xml_id }}</td>
            </tr>
            </tbody>
        </table>
        <b-button variant="dark" v-if="page > 1" v-on:click="prevItems()">Назад</b-button>
        <b-button variant="dark" v-if="orderList.length === 5" v-on:click="nextItems()">Вперед</b-button>
    </div>
</template>

<script>
    import FInput from "../../../../components/filter/f-input.vue";
    import FDate from '../../../../components/filter/f-date.vue';
    import Services from "../../../../../scripts/services/services";
    import moment from 'moment';

    export default {
        components:{
            FInput,
            FDate,
        },
        data: function() {
            let filter = {
                orderDateFrom: null,
                orderDateTo: null,
                deliveryDateFrom: null,
                deliveryDateTo: null,
                orderNum: null,
                buyer: null,
                addr: null,
            }
            return {
                filter: filter,
                page: 1,
                orderList: this.orders,
            }
        },
        props: {
            orders: Array,
            allStatuses: Object,
            offersIds: Array,
        },
        methods: {
            getDate(str) {
                return moment(str).format('DD.MM.YYYY');
            },
            getStatusName(id) {
                return this.allStatuses[id].display_name;
            },
            getAdress(addressObj) {
                if (!addressObj) {
                    return "Без адреса";
                } else {
                    return "Регион: " + addressObj.region + ", Город: " + addressObj.city + ", улица: " +
                        addressObj.street + ", дом: " + addressObj.house + ", этаж: " + addressObj.floor +
                        ", квартира: " + addressObj.flat;
                }
            },
            nextItems() {
                Services.showLoader();
                this.page++;
                Services.net().post(this.getRoute('orders.byOffers'), {}, {page: this.page, offersIds: this.offersIds})
                    .then(data => {
                        this.orderList = data
                        Services.hideLoader()
                    });
            },
            prevItems() {
                Services.showLoader();
                this.page--;
                Services.net().post(this.getRoute('orders.byOffers'), {}, {page: this.page, offersIds: this.offersIds})
                    .then(data => {
                        this.orderList = data
                        Services.hideLoader()
                    });
            },
            filterToSend() {
                let result = {};
                let clearfltr = JSON.parse(JSON.stringify(this.filter));
                for (let [key, value] of Object.entries(clearfltr)) {
                    if (value !== undefined && value !== null && value !== '') {
                        result[key] = value;
                    }
                }
                return result;
            },
        },
        computed: {
        },
    }
</script>
