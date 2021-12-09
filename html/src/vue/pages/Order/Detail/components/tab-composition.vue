<template>
    <b-card>
        <b-card v-for="delivery in order.deliveries" class="mb-4" v-bind:key="delivery.id">
            <fa-icon icon="truck"></fa-icon>
            <button @click="openTab('deliveries')" class="btn btn-link">Доставка {{ delivery.number }}</button>
            <b-card v-for="shipment in delivery.shipments" class="mb-4" v-bind:key="shipment.id">
                <fa-icon icon="truck-loading"></fa-icon>
                <button @click="openTab('shipments')" class="btn btn-link">Отправление {{ shipment.number }}</button>

                <shipment-items :returnable="true" :model-order.sync="order" :model-shipment.sync="shipment" class="mt-4"/>
            </b-card>
        </b-card>

      <button @click="" v-if="orderStatuses.done.id === order.status.id" class="btn btn-info float-right">Возврат</button>
    </b-card>
</template>

<script>
    import Services from "../../../../../scripts/services/services";
    import ShipmentItems from "./forms/shipment-items.vue";

    export default {
        name: "tab-composition",
        components: {ShipmentItems},
        props: [
            'model',
        ],
        methods: {
            openTab(tab) {
                Services.event().$emit('showTab', tab);
            },
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