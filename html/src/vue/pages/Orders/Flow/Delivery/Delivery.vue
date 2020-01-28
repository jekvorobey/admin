<template>
    <layout-main back hide-title>
        <div class="align-items-stretch justify-content-start order-header mt-3">
            <div class="shadow p-3 height-100">
                <div class="row">
                    <div class="col-sm-12">
                        <h4>Доставка {{ delivery.number }}</h4>

                        <div class="row align-items-center">
                            <p class="col-10 text-secondary">Последнее изменение:</p>
                            <p class="col-2">{{ delivery.updated_at }}</p>

                            <p class="col-10 text-secondary">Статус:</p>
                            <f-select class="col-2" v-model="delivery.status" :options="avaliableDeliveryStatuses" />

                            <p class="col-10 text-secondary">Служба:</p>
                            <f-select class="col-2 float-right" v-model="delivery.delivery_service" :options="avaliableServices" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="shadow mt-3 p-3 w-100">
            <table class="table table-condensed">
                <thead>
                <tr>
                    <th>Отправление</th>
                    <th>Статус</th>
                    <th>Мерчант</th>
                    <th>Служба</th>
                </tr>
                </thead>
                <tbody>
                <template v-for="(shipment, key) in shipments">
                    <tr>
                        <td>
                            <b>Отправление {{ shipment.number }}</b>
                        </td>
                        <td>
                            {{ shipmentStatusName(shipment.status) }}
                        </td>
                        <td>
                            {{ merchantName(shipment.merchant_id) }} (ID: {{ shipment.merchant_id }})
                        </td>
                        <td class="service">
                            <template v-if="shipment.cargo">
                                Можно изменить на <a :href="getRoute('cargo.detail', {id: shipment.cargo.id})">странице груза</a>
                            </template>
                            <f-select
                                class="col-4"
                                v-model="shipment.delivery_service_zero_mile"
                                :options="avaliableServices"
                                v-if="canEditShipmentDelivery(shipment)"
                            />
                        </td>
                    </tr>
                </template>
                </tbody>
            </table>
        </div>
    </layout-main>
</template>

<script>

import {mapGetters} from "vuex";
import Services from "../../../../../scripts/services/services";
import FSelect from '../../../../components/filter/f-select.vue';

export default {
    components: {
        FSelect
    },
    props: {
        iDelivery: {},
        iShipments: {},
        iMerchants: {},
        deliveryStatuses: {},
        deliveryServices: {},
        shipmentStatuses: {},
        shipmentNotEditableStatuses: {},
    },
    data() {
        return {
            delivery: this.iDelivery,
            shipments: this.iShipments,
            merchants: this.iMerchants,
        };
    },

    methods: {
        statusName(id) {
            let status = this.deliveryStatuses[id];
            return status ? status.name : 'N/A';
        },
        shipmentStatusName(id) {
            let status = this.shipmentStatuses[id];
            return status ? status.name : 'N/A';
        },
        serviceName(id) {
            let service = this.deliveryServices[id];
            return service ? service.name : 'N/A';
        },
        merchantName(id) {
            let merchant = this.merchants[id];
            return merchant ? merchant.display_name : 'N/A';
        },
        segmentClass(segment) {
            return segment ? `segment-${segment.toLowerCase()}` : '';
        },
        canEditShipmentDelivery(shipment) {
            return !this.shipmentNotEditableStatuses.includes(shipment.status);
        },
        // Маппим объект для опций селекта
        mapForSelect(object, valueName) {
            let arr = [];
            for (const [key, value] of Object.entries(object)) {
                arr.push({
                    value: key,
                    text: value[valueName]
                });
            }

            return arr;
        }
    },
    computed: {
        ...mapGetters(['getRoute']),
        avaliableServices() {
            return this.mapForSelect(this.deliveryServices, 'name');
        },
        avaliableDeliveryStatuses() {
            return this.mapForSelect(this.deliveryStatuses, 'name');
        },
    },
    mounted() {
        console.log(this.shipmentNotEditableStatuses);
    },
};
</script>
<style scoped>
    td {
        vertical-align:middle;
    }

    table td.service {
        padding: 0;
    }

    table td.service label {
        display: none;
    }
</style>
