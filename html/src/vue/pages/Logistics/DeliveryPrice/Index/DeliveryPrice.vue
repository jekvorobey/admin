<template>
    <layout-main back>
        <div>
            <button class="btn btn-primary mb-3"
                    v-if="!isEdit"
                    @click="edit()">
                <fa-icon icon="edit"></fa-icon> Редактировать
            </button>
            <button class="btn btn-primary mb-3"
                    v-if="isEdit"
                    @click="edit(false)">
                <fa-icon icon="save"></fa-icon> Сохранить
            </button>
            <ul>
                <li v-for="deliveryMethod in deliveryMethods">{{deliveryMethod.name}}</li>
            </ul>
            <table class="table table-bordered table-responsive table-hover">
                <thead>
                    <tr>
                        <th>Федеральный округ / Регион</th>
                        <th v-for="deliveryService in deliveryServices">{{deliveryService.name}}</th>
                    </tr>
                </thead>
                <template v-for="(federalDistrict, districtKey) in data">
                    <tbody>
                        <tr>
                            <td aria-hidden="true">
                                {{federalDistrict.name}}
                                <fa-icon icon="angle-down" class="cursor-pointer" v-on:click="toggle(federalDistrict.id)"></fa-icon>
                            </td>
                            <td v-for="deliveryService in deliveryServices">
                                <div v-for="deliveryMethod in deliveryMethods">
                                    <template v-if="isEdit">
                                        <v-input
                                                v-model="$v.data[districtKey]['deliveryPrices'][deliveryService.id][deliveryMethod.id]['price'].$model"
                                                @change="saveDistrictPrice(districtKey, deliveryService.id, deliveryMethod.id)"
                                                :error="errorFederalDistrictPrice(districtKey, deliveryService.id, deliveryMethod.id)">
                                            {{deliveryMethod.name}}
                                        </v-input>
                                    </template>
                                    <template v-else>
                                        {{ federalDistrict['deliveryPrices'].hasOwnProperty(deliveryService.id) ?
                                            federalDistrict['deliveryPrices'][deliveryService.id].hasOwnProperty(deliveryMethod.id) ?
                                                federalDistrict['deliveryPrices'][deliveryService.id][deliveryMethod.id]['price'] : '-' : '-'}}
                                    </template>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                    <tbody v-if="opened.includes(federalDistrict.id)">
                        <tr v-for="(region, regionKey) in federalDistrict['regions']">
                            <td class="pl-5">{{region.name}}</td>
                            <td v-for="deliveryService in deliveryServices">
                                <div v-for="deliveryMethod in deliveryMethods">
                                    <template v-if="isEdit">
                                        <v-input
                                                v-model="$v.data[districtKey]['regions'][regionKey]['deliveryPrices'][deliveryService.id][deliveryMethod.id]['price'].$model"
                                                @change="saveRegionPrice(districtKey, regionKey, deliveryService.id, deliveryMethod.id)"
                                                :error="errorRegionPrice(districtKey, regionKey, deliveryService.id, deliveryMethod.id)">
                                            {{deliveryMethod.name}}
                                        </v-input>
                                    </template>
                                    <template v-else>
                                        {{region['deliveryPrices'].hasOwnProperty(deliveryService.id) ?
                                            region['deliveryPrices'][deliveryService.id].hasOwnProperty(deliveryMethod.id) ?
                                                region['deliveryPrices'][deliveryService.id][deliveryMethod.id]['price'] : '' : ''}}
                                    </template>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </template>
            </table>
        </div>
    </layout-main>
</template>

<script>
    import Service from '../../../../../scripts/services/services';

    import { validationMixin } from 'vuelidate';
    import { decimal, minValue } from 'vuelidate/lib/validators';
    import VInput from '../../../../components/controls/VInput/VInput.vue';

    export default {
        name: 'page-delivery-price',
        components: {
            VInput,
        },
        mixins: [validationMixin],
        props: {
            iData: [Array],
            deliveryServices: [Object],
            deliveryMethods: [Object],
        },
        data() {
            return {
                opened: [],
                data: this.iData,
                isEdit: false,
            };
        },
        mounted() {
            let districtKey = 0;
            for (let federalDistrict of this.data) {
                for (let deliveryServiceId in this.deliveryServices) {
                    for (let deliveryMethodId in this.deliveryMethods) {
                        this.$v.data[districtKey]['deliveryPrices'][deliveryServiceId][deliveryMethodId]['price'].$model =
                            federalDistrict['deliveryPrices'].hasOwnProperty(deliveryServiceId) ?
                                (federalDistrict['deliveryPrices'][deliveryServiceId].hasOwnProperty(deliveryMethodId) ?
                                    federalDistrict['deliveryPrices'][deliveryServiceId][deliveryMethodId]['price'] :
                                    '')
                                : '';
                    }
                }

                let regionKey = 0;
                for (let region of federalDistrict['regions']) {
                    for (let deliveryServiceId in this.deliveryServices) {
                        for (let deliveryMethodId in this.deliveryMethods) {
                            this.$v.data[districtKey]['regions'][regionKey]['deliveryPrices'][deliveryServiceId][deliveryMethodId]['price'].$model =
                                region['deliveryPrices'].hasOwnProperty(deliveryServiceId) ?
                                    (region['deliveryPrices'][deliveryServiceId].hasOwnProperty(deliveryMethodId) ?
                                        region['deliveryPrices'][deliveryServiceId][deliveryMethodId]['price'] : '')
                                    : '';
                        }
                    }

                    regionKey++;
                }

                districtKey++;
            }
        },
        validations() {
            let form = {};

            let districtKey = 0;
            for (let federalDistrict of this.data) {
                for (let deliveryServiceId in this.deliveryServices) {
                    for (let deliveryMethodId in this.deliveryMethods) {
                        if (!form.hasOwnProperty(districtKey)) {
                            form[districtKey] = {};
                        }
                        if (!form[districtKey].hasOwnProperty('deliveryPrices')) {
                            form[districtKey]['deliveryPrices'] = {};
                        }
                        if (!form[districtKey]['deliveryPrices'].hasOwnProperty(deliveryServiceId)) {
                            form[districtKey]['deliveryPrices'][deliveryServiceId] = {};
                        }
                        if (!form[districtKey]['deliveryPrices'][deliveryServiceId].hasOwnProperty(deliveryMethodId)) {
                            form[districtKey]['deliveryPrices'][deliveryServiceId][deliveryMethodId] = {};
                        }

                        form[districtKey]['deliveryPrices'][deliveryServiceId][deliveryMethodId]['price'] = {
                            decimal,
                            minValue: minValue(0),
                        };
                    }
                }

                let regionKey = 0;
                for (let region of federalDistrict['regions']) {
                    for (let deliveryServiceId in this.deliveryServices) {
                        for (let deliveryMethodId in this.deliveryMethods) {
                            if (!form[districtKey].hasOwnProperty('regions')) {
                                form[districtKey]['regions'] = {};
                            }
                            if (!form[districtKey]['regions'].hasOwnProperty(regionKey)) {
                                form[districtKey]['regions'][regionKey] = {};
                            }
                            if (!form[districtKey]['regions'][regionKey].hasOwnProperty('deliveryPrices')) {
                                form[districtKey]['regions'][regionKey]['deliveryPrices'] = {};
                            }
                            if (!form[districtKey]['regions'][regionKey]['deliveryPrices'].hasOwnProperty(deliveryServiceId)) {
                                form[districtKey]['regions'][regionKey]['deliveryPrices'][deliveryServiceId] = {};
                            }
                            if (!form[districtKey]['regions'][regionKey]['deliveryPrices'][deliveryServiceId].hasOwnProperty(deliveryMethodId)) {
                                form[districtKey]['regions'][regionKey]['deliveryPrices'][deliveryServiceId][deliveryMethodId] = {};
                            }

                            form[districtKey]['regions'][regionKey]['deliveryPrices'][deliveryServiceId][deliveryMethodId]['price'] = {
                                decimal,
                                minValue: minValue(0),
                            };
                        }
                    }

                    regionKey++;
                }

                districtKey++;
            }

            return {data: form};
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
            edit(isEdit = true) {
                this.isEdit = isEdit;
            },
            saveDistrictPrice(districtKey, deliveryServiceId, deliveryMethodId) {
                let deliveryPrice =
                    this.data[districtKey]['deliveryPrices'][deliveryServiceId][deliveryMethodId];
                this.$v.$touch();
                if (this.$v.$invalid) {
                    return;
                }

                Service.net().put(
                    this.getRoute('deliveryPrice.save'),
                    null,
                    deliveryPrice
                ).then(data => {
                    this.data[districtKey]['deliveryPrices'][deliveryServiceId][deliveryMethodId] = data.deliveryPrice;
                });
            },
            saveRegionPrice(districtKey, regionKey, deliveryServiceId, deliveryMethodId) {
                let deliveryPrice =
                    this.data[districtKey]['regions'][regionKey]['deliveryPrices'][deliveryServiceId][deliveryMethodId];
                this.$v.$touch();
                if (this.$v.$invalid) {
                    return;
                }

                Service.net().put(
                    this.getRoute('deliveryPrice.save'),
                    null,
                    deliveryPrice
                ).then(data => {
                    this.data[districtKey]['regions'][regionKey]['deliveryPrices'][deliveryServiceId][deliveryMethodId] = data.deliveryPrice;
                });
            },
            errorFederalDistrictPrice(federalDistrictId, deliveryServiceId, deliveryMethodId) {
                if (this.$v.data[federalDistrictId]['deliveryPrices'][deliveryServiceId][deliveryMethodId]['price'].$dirty) {
                    if (this.$v.data[federalDistrictId]['deliveryPrices'][deliveryServiceId][deliveryMethodId]['price'].decimal === false) {
                        return "Только числа!";
                    }
                    if (this.$v.data[federalDistrictId]['deliveryPrices'][deliveryServiceId][deliveryMethodId]['price'].minValue === false) {
                        return "Больше, либо равно 0!";
                    }
                }
            },
            errorRegionPrice(federalDistrictId, regionId, deliveryServiceId, deliveryMethodId) {
                if (this.$v.data[federalDistrictId]['regions'][regionId]['deliveryPrices'][deliveryServiceId][deliveryMethodId]['price'].$dirty) {
                    if (this.$v.data[federalDistrictId]['regions'][regionId]['deliveryPrices'][deliveryServiceId][deliveryMethodId]['price'].decimal === false) {
                        return "Только числа!";
                    }
                    if (this.$v.data[federalDistrictId]['regions'][regionId]['deliveryPrices'][deliveryServiceId][deliveryMethodId]['price'].minValue === false) {
                        return "Больше, либо равно 0!";
                    }
                }
            },
        },
    };
</script>
