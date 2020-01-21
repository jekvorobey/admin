<template>
    <div class="shadow mt-3 p-3 w-100">
        <table class="table table-condensed">
            <thead>
            <tr>
                <th>ID</th>
                <th>Фото</th>
                <th class="with-small">Название <small>Артикул</small></th>
                <th></th>
                <th class="with-small">Категория <small>Бренд</small></th>
                <th>Количество</th>
                <th>Цена без скидки</th>
                <th>Скидка</th>
                <th>Цена со скидкой</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
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
