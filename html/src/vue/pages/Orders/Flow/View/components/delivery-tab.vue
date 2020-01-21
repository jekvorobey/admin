<template>
    <div class="shadow mt-3 p-3 w-100">
        <ul v-for="(delivery, key) in deliveries" class="list-group list-group-flush">
            <li class="list-group-item">
                Доставка {{ delivery.number }}
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
        },
        methods: {
            editDelivery(id) {

            },
            deleteDelivery(id) {

            },
            editShipment(id) {

            },
            deleteShipment(id) {

            }
        },
        computed: {
            ...mapGetters(['getRoute']),
        },
        mounted() {
        }
    }
</script>

<style scoped>

</style>
