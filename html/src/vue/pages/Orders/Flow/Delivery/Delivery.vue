<template>
    <layout-main back hide-title>
        <div class="align-items-stretch justify-content-start order-header mt-3">
            <div class="shadow p-3 height-100">
                <div class="row">
                    <div class="col-sm-12">
                        <h4>Доставка {{ delivery.number }}
                            <span class="badge ml-2">
                                {{ statusName(delivery.status) }}
                            </span>
                        </h4>

                        <p class="text-secondary mt-3">
                            Последнее изменение:<span class="float-right">{{ delivery.updated_at }}</span>
                        </p>

                        <p class="text-secondary mt-3">
                            Служба:<span class="float-right">{{ serviceName(delivery.delivery_service) }}</span>
                        </p>
                        <p class="text-secondary">
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <f-select class="col-4" v-model="delivery.delivery_service" :options="avaliableServices" without_none>
            Служба
        </f-select>

        delivery detail
        <br><br>
        {{ delivery }}
        <br><br>
        {{ deliveryStatuses }}
        <br><br>
        {{ deliveryServices }}
        <br><br>
        {{ shipments }}
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
        deliveryStatuses: {},
        deliveryServices: {},
    },
    data() {
        return {
            delivery: this.iDelivery,
            shipments: this.iShipments,
        };
    },

    methods: {
        statusName(id) {
            let status = this.deliveryStatuses[id];
            return status ? status.name : 'N/A';
        },
        serviceName(id) {
            let service = this.deliveryServices[id];
            return service ? service.name : 'N/A';
        }
    },
    computed: {
        ...mapGetters(['getRoute']),
        avaliableServices() {
            let arr = [];
            for (const [key, value] of Object.entries(this.deliveryServices)) {
                arr.push({
                    value: key,
                    text: value.name
                });
            }

            return arr;
        },
    },
    mounted() {
    },
};
</script>
<style scoped>
</style>
