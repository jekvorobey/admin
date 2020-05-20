<template>
    <div class="shadow mt-3 p-3 w-100">
        <table class="table table-condensed">
            <thead>
                <tr>
                    <th>Доставка</th>
                    <th>Отправления</th>
                    <th>Дата</th>
                    <th>Служба</th>
                    <th>Статус</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(delivery, key) in deliveries">
                    <td>
                        <a :href="getRoute('orders.delivery', {id: delivery.order_id, deliveryId: delivery.id})">
                            Доставка {{ delivery.number }}
                        </a>
                    </td>
                    <td>
                        <template v-if="delivery.shipments" v-for="(shipment, key) in delivery.shipments">
                            Отправление {{ shipment.number }}<br>
                        </template>
                    </td>
                    <td>{{ delivery.delivery_at }}</td>
                    <td>{{ delivery.delivery_service.name }}</td>
                    <td>{{ delivery.status.name }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
<script>

    export default {
        data() {
            return {

            }
        },
        props: {
            model: {},
        },
        methods: {
            editDelivery(id) {

            },
            deleteDelivery(id) {

            },
            editShipment(id) {

            },
            deleteShipment(id) {

            },
        },
        computed: {
            deliveries: {
                get() {return this.model},
                set(value) {this.$emit('update:model', value)},
            },
        }
    }
</script>

<style scoped>
table tr td:first-child {
    vertical-align:middle;
}
</style>
