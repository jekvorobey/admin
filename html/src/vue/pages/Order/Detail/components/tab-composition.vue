<template>
    <b-card>
        <ul v-for="delivery in order.deliveries" class="list-group list-group-flush">
            <li class="list-group-item border-top">
                <fa-icon icon="truck"></fa-icon>
                <button @click="openTab('deliveries')" class="btn btn-link">Доставка {{ delivery.number }}</button>
                <ul v-for="shipment in delivery.shipments" class="list-group list-group-flush">
                    <li class="list-group-item border-bottom">
                        <fa-icon icon="truck-loading"></fa-icon>
                        <button @click="openTab('shipments')" class="btn btn-link">Отправление {{ shipment.number }}</button>
                        <ul v-if="shipment.nonPackedBasketItems" v-for="nonPackedBasketItem in shipment.nonPackedBasketItems" class="list-group list-group-flush">
                            <li class="list-group-item border-top">
                                <img :src="productPhoto(nonPackedBasketItem.product)" class="preview" :alt="nonPackedBasketItem.name"
                                     v-if="nonPackedBasketItem.product.mainImage">
                                <a :href="getRoute('products.detail', {id: nonPackedBasketItem.product.id})" target="_blank">
                                    {{ nonPackedBasketItem.name }}
                                </a>,
                                {{nonPackedBasketItem.qty | integer}} шт.
                            </li>
                        </ul>

                        <ul v-if="shipment.packages" v-for="(shipmentPackage, key) in shipment.packages" class="list-group list-group-flush">
                            <li class="list-group-item border-top">
                                <fa-icon icon="box"></fa-icon> Коробка #{{ key+1 }} ({{ shipmentPackage.package ? shipmentPackage.package.name + ', ' : ''}}вес брутто {{shipmentPackage.weight}} г, вес пустой коробки {{shipmentPackage.wrapper_weight}} г)
                                <ul v-for="packageItem in shipmentPackage.items" class="list-group list-group-flush">
                                    <li class="list-group-item border-top">
                                        <img :src="productPhoto(packageItem.basketItem.product)" class="preview" :alt="packageItem.basketItem.name"
                                             v-if="packageItem.basketItem.product.mainImage">
                                        <a :href="getRoute('products.detail', {id: packageItem.basketItem.product.id})"  target="_blank">
                                            {{ packageItem.basketItem.name }}
                                        </a>,
                                        {{packageItem.qty | integer}} шт.
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
    import Services from "../../../../../scripts/services/services";

    export default {
        name: "tab-composition",
        props: [
            'model',
        ],
        methods: {
            openTab(tab) {
                Services.event().$emit('showTab', tab);
            },
            productPhoto(product) {
                return '/files/compressed/' + product.mainImage.file_id + '/50/50/webp';
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

<style scoped>

</style>