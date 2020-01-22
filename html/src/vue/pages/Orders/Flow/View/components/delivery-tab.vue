<template>
    <div class="shadow mt-3 p-3 w-100">
        <table class="table table-condensed">
            <thead>
                <tr>
                    <th></th>
                    <th>Дата</th>
                    <th>Статус</th>
                    <th>Служба</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <template v-for="(delivery, key) in deliveries">
                    <tr>
                        <td>
                            <b>Доставка {{ delivery.number }}</b>
                        </td>
                        <td>
                            {{ delivery.delivery_at }}
                        </td>
                        <td>
                            {{ statusName(delivery.status) }}
                        </td>
                    </tr>
                    <tr v-if="delivery.shipments" v-for="(shipment, key) in delivery.shipments">
                        <td colspan="5">
                            Отправление {{ shipment.number }}
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>

        <ul v-for="(delivery, key) in deliveries" class="list-group list-group-flush">
            <li class="list-group-item">
                Доставка {{ delivery.number }}
                {{ delivery }}
                <ul v-if="delivery.shipments" v-for="(shipment, key) in delivery.shipments">
                    <li>
                        Отправление {{ shipment.number }}
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</template>
<script>
    import {mapGetters} from "vuex";

    export default {
        data() {
            return {

            }
        },
        props: {
            deliveries: {},
            statuses: {},
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
            statusName(id) {
                let status = this.statuses[id];
                return status ? status.name : 'N/A';
            },
        },
        computed: {
            ...mapGetters(['getRoute']),
        },
        mounted() {
            console.log(this.statuses);
        }
    }
</script>

<style scoped>

</style>
