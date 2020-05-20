<template>
    <div class="col-4">
        <div class="shadow p-3 w-100">
            <ul v-for="(delivery, key) in order.deliveries" class="list-group list-group-flush">
                <li class="list-group-item">
                    Доставка {{ delivery.number }}
                    <ul v-if="delivery.shipments" v-for="(shipment, key) in delivery.shipments">
                        <li>
                            Отправление {{ shipment.number }}
                            <ul v-if="shipment.packages" v-for="(pack, key) in shipment.packages">
                                <li>
                                    Коробка #{{ key+1 }}
                                </li>
                            </ul>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</template>

<script>
    export default {
        name: "tab-composition",
        props: [
            'model',
        ],
        computed: {
            order: {
                get() {
                    return this.model
                },
                set(value) {
                    this.$emit('update:model', value)
                },
            },
        }
    }
</script>

<style scoped>

</style>