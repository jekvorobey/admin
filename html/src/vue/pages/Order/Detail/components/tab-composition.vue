<template>
    <b-card>
        <b-card v-for="(delivery, dKey) in order.deliveries" class="mb-4" v-bind:key="delivery.id">
            <fa-icon icon="truck"></fa-icon>
            <button @click="openTab('deliveries')" class="btn btn-link">Доставка {{ delivery.number }}</button>
            <b-card v-for="(shipment, sKey) in delivery.shipments" class="mb-4" v-bind:key="shipment.id">
                <fa-icon icon="truck-loading"></fa-icon>
                <button @click="openTab('shipments')" class="btn btn-link">Отправление {{ shipment.number }}</button>

                <shipment-items :returnable="true" :model-order.sync="order" :model-shipment.sync="order.shipments[sKey]" class="mt-4"/>
            </b-card>
        </b-card>

      <button @click="returnItems()" v-if="orderStatuses.done.id === order.status.id && basketItemsToReturn.length" class="btn btn-info float-right">Возврат</button>
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
            returnItems() {
              Services.net().put(this.getRoute('orders.returnItems', {id: this.order.id}), null, {
                basketItemIds: this.basketItemsToReturn.map(el => el.id)
              })
                  .then(data => {
                    if (data.order) {
                      this.$set(this, 'order', data.order);
                      this.$set(this.order, 'shipments', data.order.shipments);
                      Services.msg("Изменения сохранены");
                    } else {
                      Services.msg(errorMessage, 'danger');
                    }
                  }, () => {
                    Services.msg(errorMessage, 'danger');
                  }).finally(data => {
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
            },
            basketItemsToReturn: {
                get() {
                    const basketItems = [];
                    this.order.shipments.map(shipment => {
                      shipment.packages.forEach(pkg => pkg.items.forEach(packageItem => {
                        if (packageItem.to_return) {
                          basketItems.push(packageItem['basketItem']);
                        }
                      }))
                    })
                    return basketItems;
              }
            }
        }
    }
</script>