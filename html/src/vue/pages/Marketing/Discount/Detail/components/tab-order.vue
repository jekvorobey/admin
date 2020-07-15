<template>
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
        <tbody>
            <template v-for="order in orders">
                <tr v-for="delivery in order.deliveries">
                    <td>{{ order.number }} / {{ delivery.number }}</td>
                    <td>{{ getDate(order.created_at) }}</td>
                    <td>{{ getDate(delivery.delivery_at) }}</td>
                    <td>{{ parseInt(order.cost) }} ₽</td>
                    <td>{{ delivery.receiver_name }}</td>
                    <td>{{ getAddress(delivery.delivery_address) }}</td>
                    <td>{{ order.fsd }}</td>
                    <td>{{ orderStatusesNames[order.status] }}</td>
                    <td>{{ delivery.status_xml_id }}</td>
                </tr>
            </template>
        </tbody>
    </table>
</template>

<script>
    import Services from "../../../../../../scripts/services/services";

    export default {
        name: 'tab-order',
        components: {

        },
        props: {
            model: Object,
        },
        data() {
            return {
                orders: [],
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
                    names[status.id] = status.display_name;
                }
                return names;
            }
        },
        methods: {
            getDate(str) {
                if (!str) {
                    return '–';
                }

                let date = new Date(str);
                return date.toLocaleString('ru', {
                    day: 'numeric',
                    month: 'numeric',
                    year: 'numeric'
                });
            },
            getAddress(address) {
                if (!address || address.length <= 0) {
                    return "Без адреса";
                }

                let fullAddress = [];
                if (address.region) {
                    fullAddress.push('Регион: ' + address.region);
                }
                if (address.city) {
                    fullAddress.push('Город: ' + address.city);
                }
                if (address.street) {
                    fullAddress.push('Улица: ' + address.street);
                }
                if (address.house) {
                    fullAddress.push('Дом: ' + address.house);
                }
                if (address.floor) {
                    fullAddress.push('Этаж: ' + address.floor);
                }
                if (address.flat) {
                    fullAddress.push('Квартира: ' + address.flat);
                }

                if (fullAddress.length <= 0) {
                    return "Без адреса";
                }

                return fullAddress.join(', ');
            },
        },
        mounted() {

        },
        created() {
            Services.showLoader();
            Services.net().get(
                this.getRoute('discount.orders', {id: this.discount.id})
            ).then(data => {
                this.orders = data.orders;
                console.log(data);
            }, () => {
                Services.msg('Ошибка при загрузке заказов', 'danger');
            }).finally(() => {
                Services.hideLoader();
            });
        }
    };
</script>

<style scoped>
    .condition-list {
        line-height: 0.9;
        margin-top: 10px;
    }
</style>
