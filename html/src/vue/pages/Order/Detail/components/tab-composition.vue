<template>
    <b-card>
        <ul v-for="delivery in order.deliveries" class="list-group list-group-flush">
            <li class="list-group-item">
                <a :href="deliveryLink(delivery)">Доставка {{ delivery.number }}</a>
                <ul v-for="shipment in delivery.shipments" class="list-group list-group-flush">
                    <li class="list-group-item">
                        Отправление {{ shipment.number }}
                        <ul v-if="shipment.packages" v-for="(shipmentPackage, key) in shipment.packages" class="list-group list-group-flush">
                            <li class="list-group-item">
                                Коробка #{{ key+1 }}
                                <ul v-for="packageItem in shipmentPackage.items" class="list-group list-group-flush">
                                    <li class="list-group-item">
                                        Товар {{ packageItem.basket_item_id }} {{packageItem.qty}} шт.
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </li>
                </ul>
            </li>
        </ul>
    </b-card>
</template>

<script>
    export default {
        name: "tab-composition",
        props: [
            'model',
        ],
        methods: {
            deliveryLink(delivery) {
                return '?tab=deliveries#' + delivery.id;
            }
        },
        computed: {
            order: {
                get() {
                    return this.model
                },
                set(value) {
                    this.$emit('update:model', value)
                },
            }
        }
    }
</script>

<style scoped>

</style>