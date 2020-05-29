<template>
    <div>
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
            <tbody v-for="(order, index) in orderList">
            <tr class="table-primary">
                <td>{{ order.number }}</td>
                <td>{{ order.created_at }}</td>
                <td></td>
                <td>{{ parseInt(order.cost) }} р.</td>
                <td></td>
                <td></td>
                <td></td>
                <td>{{ getStatusName(order.status) }}</td>
                <td></td>
            </tr>
                <tr v-for="delivery in order.deliveries">
                    <td>{{ delivery.number }}</td>
                    <td></td>
                    <td>{{ getDate(delivery.delivery_at) }}</td>
                    <td></td>
                    <td>{{ delivery.receiver_name }}</td>
                    <td>{{ getAdress(delivery.delivery_address) }}</td>
                    <td>{{ delivery.fsd }}</td>
                    <td></td>
                    <td>{{ delivery.status_xml_id }}</td>
                </tr>
            </tbody>
        </table>
        <b-button variant="dark" v-if="page > 1" v-on:click="prevItems()">Назад</b-button>
        <b-button variant="dark" v-if="orderList.length === 5" v-on:click="nextItems()">Вперед</b-button>
    </div>
</template>

<script>
    import FInput from "../../../../components/filter/f-input.vue";
    import Services from "../../../../../scripts/services/services";

    export default {
        components:{
            FInput,
        },
        data: function() {
            return {
                page: 1,
                orderList: this.orders,
            }
        },
        props: {
            orders: Array,
            orderStatuses: Object,
            offersIds: Array,
        },
        methods: {
            getDate(str) {
                let date = new Date(str);
                return date.toLocaleString('ru', {
                    day: 'numeric',
                    month: 'numeric',
                    year: 'numeric'
                });
            },
            getStatusName(id) {
                return this.orderStatuses[id].display_name;
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
                this.page++;
                Services.net().post(this.getRoute('orders.byOffers'), {}, {page: this.page, offersIds: this.offersIds})
                    .then(data => { this.orderList = data });
            },
            prevItems() {
                this.page--;
                Services.net().post(this.getRoute('orders.byOffers'), {}, {page: this.page, offersIds: this.offersIds})
                    .then(data => { this.orderList = data });
            },
        },
        computed: {
        },
    }
</script>
