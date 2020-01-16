<template>
    <layout-main back>
        <div>
            <button class="btn btn-primary mb-3"
                    @click="edit()">
                <fa-icon icon="edit"></fa-icon> Редактировать
            </button>
            <table class="table table-bordered table-responsive table-hover">
                <thead>
                    <tr>
                        <th>Федеральный округ / Регион</th>
                        <th v-for="deliveryService in deliveryServices">{{deliveryService.name}}</th>
                    </tr>
                </thead>
                <template v-for="deliveryPrice in deliveryPrices">
                    <tbody>
                        <tr>
                            <td aria-hidden="true">
                                {{deliveryPrice.name}}
                                <fa-icon icon="angle-down" class="cursor-pointer" v-on:click="toggle(deliveryPrice.id)"></fa-icon>
                            </td>
                            <td v-for="deliveryService in deliveryServices">
                                <template v-if="isEdit">
                                    <v-input
                                            v-model="$v.deliveryPrices[deliveryPrice.id]['deliveryPrices'][deliveryService['id']]['price'].$model"
                                            :error="errorFederalDistrictPrice(deliveryPrice.id, deliveryService['id'])">
                                    </v-input>
                                </template>
                                <template v-else>
                                    {{ deliveryPrice['deliveryPrices'].hasOwnProperty(deliveryService['id']) ?
                                    deliveryPrice['deliveryPrices'][deliveryService['id']]['price'] : ''}}
                                </template>
                            </td>
                        </tr>
                    </tbody>
                    <tbody v-if="opened.includes(deliveryPrice.id)">
                        <tr v-for="deliveryPriceAtRegion in deliveryPrice['regions']">
                            <td class="pl-5">{{deliveryPriceAtRegion.name}}</td>
                            <td v-for="deliveryService in deliveryServices">
                                <template v-if="isEdit">
                                    <v-input
                                            v-model="$v.deliveryPrices[deliveryPrice.id]['regions'][deliveryPriceAtRegion.id]['deliveryPrices'][deliveryService['id']]['price'].$model"
                                            :error="errorRegionPrice(deliveryPrice.id, deliveryPriceAtRegion.id, deliveryService['id'])">
                                    </v-input>
                                </template>
                                <template v-else>
                                    {{deliveryPriceAtRegion['deliveryPrices'].hasOwnProperty(deliveryService['id']) ?
                                    deliveryPriceAtRegion['deliveryPrices'][deliveryService['id']]['price'] : ''}}
                                </template>
                            </td>
                        </tr>
                    </tbody>
                </template>
            </table>
        </div>
    </layout-main>
</template>

<script>
    import Service from "../../../../../scripts/services/services";

    import {validationMixin} from 'vuelidate';
    import {integer} from 'vuelidate/lib/validators';
    import VInput from "../../../../components/controls/VInput/VInput.vue";


    export default {
        name: 'page-delivery-price',
        components: {
            VInput,
        },
        mixins: [validationMixin],
        props: {
            iDeliveryPrices: [Array],
            deliveryServices: [Object],
        },
        data() {
            return {
                opened: [],
                deliveryPrices: this.iDeliveryPrices,
                isEdit: false,
            };
        },
        mounted() {
            for (let deliveryPrice of this.deliveryPrices) {
                for (let deliveryServiceId in this.deliveryServices) {
                    this.$v.deliveryPrices[deliveryPrice.id]['deliveryPrices'][deliveryServiceId]['price'].$model =
                        deliveryPrice['deliveryPrices'].hasOwnProperty(deliveryServiceId) ?
                            deliveryPrice['deliveryPrices'][deliveryServiceId]['price'] : '';
                }

                for (let deliveryPriceAtRegion of deliveryPrice['regions']) {
                    for (let deliveryServiceId in this.deliveryServices) {
                        this.$v.deliveryPrices[deliveryPrice.id]['regions'][deliveryPriceAtRegion.id]['deliveryPrices'][deliveryServiceId]['price'].$model =
                            deliveryPriceAtRegion['deliveryPrices'].hasOwnProperty(deliveryServiceId) ?
                                deliveryPriceAtRegion['deliveryPrices'][deliveryServiceId]['price'] : '';
                    }
                }
            }
        },
        validations() {
            let form = {};

            for (let deliveryPrice of this.deliveryPrices) {
                for (let deliveryServiceId in this.deliveryServices) {
                    if (!form.hasOwnProperty(deliveryPrice.id)) {
                        form[deliveryPrice.id] = {};
                    }
                    if (!form[deliveryPrice.id].hasOwnProperty('deliveryPrices')) {
                        form[deliveryPrice.id]['deliveryPrices'] = {};
                    }
                    if (!form[deliveryPrice.id]['deliveryPrices'].hasOwnProperty(deliveryServiceId)) {
                        form[deliveryPrice.id]['deliveryPrices'][deliveryServiceId] = {};
                    }

                    form[deliveryPrice.id]['deliveryPrices'][deliveryServiceId]['price'] = {
                        integer,
                    };
                }

                for (let deliveryPriceAtRegion of deliveryPrice['regions']) {
                    for (let deliveryServiceId in this.deliveryServices) {
                        if (!form[deliveryPrice.id].hasOwnProperty('regions')) {
                            form[deliveryPrice.id]['regions'] = {};
                        }
                        if (!form[deliveryPrice.id]['regions'].hasOwnProperty(deliveryPriceAtRegion.id)) {
                            form[deliveryPrice.id]['regions'][deliveryPriceAtRegion.id] = {};
                        }
                        if (!form[deliveryPrice.id]['regions'][deliveryPriceAtRegion.id].hasOwnProperty('deliveryPrices')) {
                            form[deliveryPrice.id]['regions'][deliveryPriceAtRegion.id]['deliveryPrices'] = {};
                        }
                        if (!form[deliveryPrice.id]['regions'][deliveryPriceAtRegion.id]['deliveryPrices'].hasOwnProperty(deliveryServiceId)) {
                            form[deliveryPrice.id]['regions'][deliveryPriceAtRegion.id]['deliveryPrices'][deliveryServiceId] = {};
                        }

                        form[deliveryPrice.id]['regions'][deliveryPriceAtRegion.id]['deliveryPrices'][deliveryServiceId]['price'] = {
                            integer,
                        };
                    }
                }
            }

            return {deliveryPrices: form};
        },
        methods: {
            toggle(id) {
                const index = this.opened.indexOf(id);
                if (index > -1) {
                    this.opened.splice(index, 1)
                } else {
                    this.opened.push(id)
                }
            },
            edit() {
                this.isEdit = true;
            },
            errorFederalDistrictPrice(federalDistrictId, deliveryServiceId) {
                if (this.$v.deliveryPrices[federalDistrictId]['deliveryPrices'][deliveryServiceId]['price'].$dirty) {
                    if (this.$v.deliveryPrices[federalDistrictId]['deliveryPrices'][deliveryServiceId]['price'].integer === false) {
                        return "Только целые числа!";
                    }
                }
            },
            errorRegionPrice(federalDistrictId, regionId, deliveryServiceId) {
                if (this.$v.deliveryPrices[federalDistrictId]['regions'][regionId]['deliveryPrices'][deliveryServiceId]['price'].$dirty) {
                    if (this.$v.deliveryPrices[federalDistrictId]['regions'][regionId]['deliveryPrices'][deliveryServiceId]['price'].integer === false) {
                        return "Только целые числа!";
                    }
                }
            },
        },
    };
</script>
<style>
    tbody.collapse.in {
        display: table-row-group;
    }
</style>
