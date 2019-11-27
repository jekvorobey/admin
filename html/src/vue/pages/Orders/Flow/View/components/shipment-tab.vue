<template>
    <div class="shadow mt-3 p-3 w-100">
        <ul v-for="(shipment, key) in shipments" class="list-group list-group-flush">
            <li class="list-group-item">
                <div class="d-flex justify-content-start">
                    <div>
                        Отправление {{ shipment.number }}
                    </div>
                    <div class="pl-3">
                        <fa-icon icon="pencil-alt" title="Изменить" class="cursor-pointer mr-3"
                                 @click="editShipment(item.id)"
                        />
                        <fa-icon icon="times" title="Удалить" class="cursor-pointer"
                                 @click="deleteShipment(item.id)"
                        />
                    </div>
                </div>
                <template v-if="shipment.packages.length > 0">
                    <ul v-for="(pack, key) in shipment.packages">
                        <li>
                            <div class="d-flex justify-content-start">
                                <div>
                                    Коробка #{{ key+1 }}
                                </div>
                                <div class="pl-3">
                                    <fa-icon icon="pencil-alt" title="Изменить" class="cursor-pointer mr-3"
                                             @click="editPackage(item.id)"
                                    />
                                    <fa-icon icon="times" title="Удалить" class="cursor-pointer"
                                             @click="deletePackage(item.id)"
                                    />
                                </div>
                            </div>
                            <ul v-for="(item, key) in pack.items">
                                <li>
                                    <div class="d-flex justify-content-start">
                                        <div>
                                            {{ item.name }}
                                        </div>
                                        <div class="pl-3">
                                            {{ item.product.vendor_code }}
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </template>
                <template v-else>
                    <ul v-for="(item, key) in shipment.basketItems">
                        <li>
                            <div class="d-flex justify-content-start">
                                <div>
                                    {{ item.name }}
                                </div>
                                <div class="pl-3">
                                    {{ item.product.vendor_code }}
                                </div>
                            </div>
                        </li>
                    </ul>
                </template>
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
        shipments: {},
    },
    methods: {
        editShipment(id) {

        },
        deleteShipment(id) {

        },
        editProduct(id) {

        },
        deleteProduct(id) {

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
