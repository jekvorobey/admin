<template>
    <layout-main back>
        <form v-on:submit.prevent.stop="add" class="mt-3">
            <div class="row">
                <div class="form-group col-lg-6 col-12">
                    <f-custom-search-select
                            v-model="$v.store.merchant_id.$model"
                            :options="merchantOptions"
                            :error="error_merchant_id"
                    >Мерчант</f-custom-search-select>
                </div>
            </div>
            <div class="row">
                <v-input
                    v-model="$v.store.name.$model"
                    :error="error_name"
                    class="col-lg-4 col-8">Название склада</v-input>
                <v-input
                        v-model="store.xml_id"
                        class="col-lg-2 col-4">
                    Внешний код
                </v-input>
            </div>
            <div class="row">
                <v-dadata
                        :value="$v.store.address.address_string.$model"
                        :error="error_address"
                        @onSelect="onStoreAddressAdd"
                        class="col-lg-6 col-12">
                    Адрес
                </v-dadata>
            </div>
            <div class="row">
                <v-input
                        v-model="store.address.porch"
                        class="col-lg-2 col-4">Подъезд</v-input>
                <v-input
                        v-model="store.address.floor"
                        class="col-lg-2 col-4">Этаж</v-input>
                <v-input
                        v-model="store.address.intercom"
                        class="col-lg-2 col-4">Домофон</v-input>
            </div>
            <div class="row">
                <v-input
                        type="textarea"
                        v-model="store.address.comment"
                        class="col-lg-6 col-12">Комментарий к адресу</v-input>
            </div>

            <div class="col-lg-6 col-12 form-group">
                <div class="row">
                    <label>
                        Всегда сплитом
                        <input type="checkbox" v-model="store.is_always_splitted"/>
                    </label>
                </div>
            </div>

            <button
                    @click="add"
                    type="button"
                    class="btn btn-success mb-3"
                    :disabled="!$v.store.$anyDirty">Добавить</button>
        </form>
    </layout-main>
</template>

<script>
    import Services from '../../../../../scripts/services/services';
    import VInput from '../../../../components/controls/VInput/VInput.vue';
    import VSelect from '../../../../components/controls/VSelect/VSelect.vue';
    import VDadata from '../../../../components/controls/VDaData/VDaData.vue';
    import FCustomSearchSelect from '../../../../components/filter/f-custom-search-select.vue';

    import {validationMixin} from 'vuelidate';
    import {integer, required, requiredIf} from 'vuelidate/lib/validators';

    export default {
    name: 'page-stores-detail',
    components: {
        VInput,
        VSelect,
        VDadata,
        FCustomSearchSelect,
    },
    mixins: [validationMixin],
    props: {
        merchants: [Array]
    },
    data() {
        return {
            store: {
                merchant_id: null,
                name: null,
                xml_id: null,
                address: {
                    address_string: null,
                    country_code: null,
                    post_index: null,
                    region: null,
                    region_guid: null,
                    area: null,
                    area_guid: null,
                    city: null,
                    city_guid: null,
                    street: null,
                    house: null,
                    block: null,
                    flat: null,
                    porch: null,
                    floor: null,
                    intercom: null,
                    comment: null,
                },
            },
        }
    },
    validations() {
        let self = this;

        return {
            store: {
                merchant_id: {integer, required},
                name: {required},
                address: {
                    address_string: {required},
                    country_code: {required},
                    post_index: {required},
                    region: {required},
                    region_guid: {required},
                    city: {required},
                    city_guid: {required},
                    house: {
                        required: requiredIf(() => {
                            return !self.store.address.block;
                        })
                    },
                    block: {
                        required: requiredIf(() => {
                            return !self.store.address.house;
                        })
                    },
                }
            }
        };
    },
    methods: {
        onStoreAddressAdd(suggestion) {
            let address = suggestion.data;

            this.store.address.address_string = suggestion.unrestricted_value;
            this.store.address.country_code = address.country_iso_code;
            this.store.address.post_index = address.postal_code;
            this.store.address.region = address.region_with_type;
            this.store.address.region_guid = address.region_fias_id;
            this.store.address.area = address.area_with_type;
            this.store.address.area_guid = address.area_fias_id;

            let assignSettlementAsCity = address.settlement_with_type && !['автодорога', 'мкр'].includes(address.settlement_type);
            this.store.address.city = assignSettlementAsCity ? address.settlement_with_type : address.city_with_type;
            this.store.address.city_guid = assignSettlementAsCity ? address.settlement_fias_id : address.city_fias_id;

            this.store.address.street = address.street_with_type;
            this.store.address.house = address.house ? [address.house_type, address.house].join(' ') : '';
            this.store.address.block = address.block ? [address.block_type, address.block].join(' ') : '';
            this.store.address.flat = address.flat ? [address.flat_type, address.flat].join(' ') : '';
        },
        add() {
            let self = this;
            this.$v.$touch();
            if (this.$v.$invalid) {
                return;
            }

            Services.showLoader();
            Services.net().post(
                this.getRoute('merchantStore.create'),
                null,
                this.store
            ).then(data => {
                if (data.id) {
                    Services.msg("Изменения сохранены");
                    window.location.href = self.getRoute('merchantStore.edit', {id: data.id});
                }
            }).finally(() => {
                Services.hideLoader();
            });
        },
    },
    computed: {
        merchantOptions() {
            return Object.values(this.merchants).map(merchant => ({value: merchant.id, text: merchant.name}));
        },

        error_merchant_id() {
            if (this.$v.store.merchant_id.$dirty) {
                if (!this.$v.store.merchant_id.required) {
                    return "Обязательное поле!";
                }
                if (!this.$v.store.merchant_id.integer) {
                    return "Только целое число!";
                }
            }
        },
        error_name() {
            if (this.$v.store.name.$dirty) {
                if (!this.$v.store.name.required) {
                    return "Обязательное поле!";
                }
            }
        },
        error_address() {
            if (this.$v.store.address.address_string.$dirty) {
                if (!this.$v.store.address.address_string.required) {
                    return "Введите адрес и выберите его из подсказки ниже";
                }
            }
            if (this.$v.store.address.post_index.$dirty) {
                if (!this.$v.store.address.post_index.required) {
                    return "Введите почтовый индекс";
                }
            }
            if (this.$v.store.address.region.$dirty) {
                if (!this.$v.store.address.region.required) {
                    return "Введите регион";
                }
            }
            if (this.$v.store.address.city.$dirty) {
                if (!this.$v.store.address.city.required) {
                    return "Введите город/населенный пункт";
                }
            }
            if (this.$v.store.address.house.$dirty || this.$v.store.address.block.$dirty) {
                if (!this.$v.store.address.house.required || !this.$v.store.address.block.required) {
                    return "Введите дом/строение/корпус";
                }
            }
        },
    }
};
</script>
