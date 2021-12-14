<template>
    <b-card>
        <b-card v-for="delivery in order.deliveries" class="mb-4" v-bind:key="delivery.id">
            <fa-icon icon="truck"></fa-icon>
            <button @click="openTab('deliveries')" class="btn btn-link">Доставка {{ delivery.number }}</button>
            <b-card v-for="shipment in delivery.shipments" class="mb-4" v-bind:key="shipment.id">
                <fa-icon icon="truck-loading"></fa-icon>
                <button @click="openTab('shipments')" class="btn btn-link">Отправление {{ shipment.number }}</button>

                <shipment-items
                    :model-order.sync="order"
                    :model-shipment.sync="shipment" class="mt-4"
                    :returnable="true"
                    :basketItemsToReturn="basketItemsToReturn"
                    @toggleBasketItemReturn="toggleBasketItemReturn"
                />
            </b-card>
        </b-card>

      <button @click="returnBasketItems()" v-if="orderStatuses.done.id === order.status.id && basketItemsToReturn.length" class="btn btn-info float-right">
          Возврат
      </button>
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
        data() {
            return {
                basketItemsToReturn: [],
            };
        },
        methods: {
            openTab(tab) {
                Services.event().$emit('showTab', tab);
            },
            toggleBasketItemReturn(basketItemId) {
                this.basketItemsToReturn = this.basketItemsToReturn.includes(basketItemId)
                    ? this.basketItemsToReturn.filter(id => id !== basketItemId)
                    : [ ...this.basketItemsToReturn, basketItemId ];
            },
            returnBasketItems() {
                if (this.basketItemsToReturn.length <= 0) {
                    return;
                }

                Services.net().put(this.getRoute('orders.return', {id: this.order.id}), null, {
                    basketItemIds: this.basketItemsToReturn
                })
                    .then(data => {
                        if (!data.order) {
                            Services.msg(errorMessage, 'danger');
                        }

                        this.$set(this, 'order', data.order);
                        this.$set(this.order, 'shipments', data.order.shipments);
                        Services.msg("Изменения сохранены");
                    }, () => {
                        Services.msg(errorMessage, 'danger');
                    }).finally(() => {
                        Services.hideLoader();
                    });
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
