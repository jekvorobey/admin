<template>
    <div>
        <div v-if="isMoySklad">
            <div class="row">
                <v-input v-model="$v.form.token.$model" :error="errorToken" class="col-md-4 col-12"><h5>Токен</h5>
                </v-input>
                <v-input v-model="$v.form.login.$model" :error="errorLogin" class="col-md-4 col-12"><h5>Логин</h5>
                </v-input>
                <v-input v-model="$v.form.password.$model" :error="errorPassword" class="col-md-4 col-12"><h5>
                    Пароль</h5>
                </v-input>
            </div>
            <div class="row">
                <v-input v-model="$v.form.merchantPriceSetting.$model" :error="errorSettingPriceValue"
                         class="col-md-4 col-12">
                    <h5>
                        Значение настройки цены</h5></v-input>
                <v-input v-model="$v.form.merchantOrganizationSetting.$model" :error="errorSettingOrganizationValue"
                         class="col-md-4 col-12">
                    <h5>ID
                        организации</h5></v-input>
            </div>

            <div class="row">
                <v-input v-model="$v.form.paramPeriodPrice.$model" :error="errorParamPeriodPrice"
                         class="col-md-6 col-12"><h5>
                    Импорт цен.Период
                    проверки
                    (мин)</h5></v-input>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" v-model="$v.form.paramActivePrice.$model" id="paramActivePrice"
                           type="checkbox">
                    <label class="form-check-label" for="paramActivePrice">Активировать</label>
                </div>
            </div>
            <div class="row">
                <v-input v-model="$v.form.paramPeriodStock.$model" :error="errorParamPeriodStock"
                         class="col-md-6 col-12"><h5>
                    Импорт остатков.Период
                    проверки
                    (мин)</h5></v-input>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" v-model="$v.form.paramActiveStock.$model" id="paramActiveStock"
                           type="checkbox">
                    <label class="form-check-label" for="paramActiveStock">Активировать</label>
                </div>
            </div>
            <div class="row">
                <v-input v-model="$v.form.paramPeriodOrder.$model" :error="errorParamPeriodOrder"
                         class="col-md-6 col-12"><h5>
                    Экспорт заказов.Период
                    проверки
                    (мин)</h5></v-input>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" v-model="$v.form.paramActiveOrder.$model" id="paramActiveOrder"
                           type="checkbox">
                    <label class="form-check-label" for="paramActiveOrder">Активировать</label>
                </div>
            </div>
        </div>
        <div v-else-if="isFileSharing(extSystemsSelect.driver_id)">
            <div class="row">
                <v-input v-model="$v.form.host.$model" :error="errorHost" class="col-md-6 col-12"><h5>Хост</h5>
                </v-input>
                <v-input v-model="$v.form.port.$model" :error="errorPort" class="col-md-6 col-12"><h5>Порт</h5>
                </v-input>
            </div>
            <div class="row">
                <v-input v-model="$v.form.login.$model" :error="errorLogin" class="col-md-6 col-12"><h5>Логин</h5>
                </v-input>
                <v-input v-model="$v.form.password.$model" :error="errorPassword" class="col-md-6 col-12"><h5>
                    Пароль</h5>
                </v-input>
            </div>
            <div class="row">
                <v-input v-model="$v.form.fileName.$model" :error="errorFileName" class="col-md-6 col-12"><h5>
                    Наименование
                    файла</h5></v-input>
                <v-input v-model="$v.form.paramPeriodPriceStock.$model" :error="errorParamPeriodPriceStock" class="col-md-6 col-12"><h5>
                    Период
                    проверки
                    (мин)</h5></v-input>
            </div>
            <input v-model="$v.form.paramActivePriceStock.$model" type="checkbox">Активировать
        </div>
        <button @click="save" class="btn mt-4 btn-dark">Сохранить</button>
    </div>
</template>

<script>
import Services from '../../../../../../scripts/services/services';

import VInput from '../../../../../components/controls/VInput/VInput.vue';

import {validationMixin} from 'vuelidate';
import {requiredIf} from 'vuelidate/lib/validators';

import modalMixin from "../../../../../mixins/modal.js";
import modal from "../../../../../components/controls/modal/modal.vue";

export default {
    mixins: [modalMixin, validationMixin],
    components: {
        VInput,
        modal
    },
    props: {
        id: null,
        extSystem: {
            id: '',
            name: '',
            driver: null,
            connection_params: {},
        },
        extSystemsSelect: Object,
        options: Object,
    },
    data() {
        return {
            form: {
                token: this.extSystem ? this.extSystem.connection_params.token : '',
                login: this.extSystem ? this.extSystem.connection_params.login : '',
                password: this.extSystem ? this.extSystem.connection_params.password : '',
                host: this.options ? this.options.host : '',
                port: this.options ? this.options.port : '',
                merchantPriceSetting: this.options ? this.options.merchantPriceSetting : '',
                merchantOrganizationSetting: this.options ? this.options.merchantOrganizationSetting : '',
                paramPeriodPrice: this.options.paramPrice ? this.options.paramPrice.params.period : '',
                paramActivePrice: this.options.paramPrice ? this.options.paramPrice.active : true,
                paramPeriodStock: this.options.paramStock ? this.options.paramStock.params.period : '',
                paramActiveStock: this.options.paramStock ? this.options.paramStock.active : true,
                paramPeriodOrder: this.options.paramOrder ? this.options.paramOrder.params.period : '',
                paramActiveOrder: this.options.paramOrder ? this.options.paramOrder.avtive : true,
                paramPeriodPriceStock: this.options.paramPriceStock ? this.options.paramPriceStock.params.period : '',
                paramActivePriceStock: this.options.paramPriceStock ? this.options.paramPriceStock.active : true,
                fileName: this.options ? this.options.fileName : '',
            },
        };
    },
    validations() {
        return {
            form: {
                token: {
                    required: requiredIf(function (form) {
                        return form.login === '' && this.isMoySklad
                    }),
                },
                login: {
                    required: requiredIf(function (form) {
                        return form.token === ''
                    }),
                },
                password: {
                    required: requiredIf(function (form) {
                        return form.login !== ''
                    }),
                },
                host: {
                    required: requiredIf(function () {
                        return this.isFileSharing
                    }),
                },
                port: {
                    required: requiredIf(function () {
                        return this.isFileSharing
                    }),
                },
                fileName: {
                    required: requiredIf(function () {
                        return this.isFileSharing
                    }),
                },
                merchantPriceSetting: {
                    required: requiredIf(function () {
                        return this.isMoySklad
                    }),
                },
                merchantOrganizationSetting: {
                    required: requiredIf(function () {
                        return this.isMoySklad
                    }),
                },
                paramPeriodPrice: {
                    required: requiredIf(function () {
                        return this.isMoySklad
                    }),
                },
                paramPeriodStock: {
                    required: requiredIf(function () {
                        return this.isMoySklad
                    }),
                },
                paramPeriodOrder: {
                    required: requiredIf(function () {
                        return this.isMoySklad
                    }),
                },
                paramPeriodPriceStock: {
                    required: requiredIf(function () {
                        return this.isFileSharing
                    }),
                },
                paramActivePrice: {},
                paramActiveStock: {},
                paramActiveOrder: {},
                paramActivePriceStock: {},
            }
        };
    },
    methods: {
        save() {
            this.$v.$touch();
            if (this.$v.$invalid) {
                return;
            }
            Services.showLoader();
            let formData = {
                merchantId: this.id,
                login: this.form.login,
                password: this.form.password,
                driver: this.checkExtSystemDriver(),
                integrationParams: {
                    paramPeriodPrice: this.form.paramPeriodPrice,
                    paramPeriodStock: this.form.paramPeriodStock,
                    paramPeriodOrder: this.form.paramPeriodOrder,
                    paramPeriodPriceStock: this.form.paramPeriodPriceStock,
                    paramActivePrice: this.form.paramActivePrice,
                    paramActiveStock: this.form.paramActiveStock,
                    paramActiveOrder: this.form.paramActiveOrder,
                    paramActivePriceStock: this.form.paramActivePriceStock,
                },
            };
            if (this.form.token) {
                formData.token = this.form.token;
            }
            if (this.form.merchantPriceSetting) {
                formData.settingPriceValue = this.form.merchantPriceSetting;
            }
            if (this.form.merchantOrganizationSetting) {
                formData.settingOrganizationValue = this.form.merchantOrganizationSetting;
            }
            if (this.form.fileName) {
                formData.fileName = this.form.fileName;
            }
            if (this.form.port) {
                formData.port = this.form.port;
            }
            if (this.form.host) {
                formData.host = this.form.host;
            }
            if (!this.extSystem) {
                Services.net().post(
                    this.getRoute('merchant.detail.extSystems.store', {id: this.id}), {}, formData
                ).then(() => {
                    Services.msg('Интеграция успешно создана');
                    this.$emit('onSave');
                }).finally(() => {
                    Services.hideLoader();
                    this.closeModal();
                })
            }
            if (this.extSystem) {
                Services.net().put(
                    this.getRoute('merchant.detail.extSystems.update', {id: this.extSystem.id}), {}, formData
                ).then(() => {
                    Services.msg('Интеграция успешно обновлена');
                    this.$emit('onSave');
                }).finally(() => {
                    Services.hideLoader();
                    this.closeModal();
                })
            }
        },
    },
    computed: {
        is1C() {
            let driverId = this.checkExtSystemDriver;
            return parseInt(driverId) === this.merchantExtSystemDrivers.one_c
        },
        isMoySklad() {
            let driverId = this.checkExtSystemDriver;
            return parseInt(driverId) === this.merchantExtSystemDrivers.moysklad
        },
        isFileSharing() {
            let driverId = this.checkExtSystemDriver;
            return parseInt(driverId) === this.merchantExtSystemDrivers.filesharing
        },
        checkExtSystemDriver() {
            if (this.extSystem && this.extSystem.driver) {
                return this.extSystem.driver;
            }

            return this.extSystemsSelect.driver_id;
        },
        errorToken() {
            if (this.$v.form.token.$dirty) {
                if (!this.$v.form.token.required) return "Обязательное поле!";
            }
        },
        errorLogin() {
            if (this.$v.form.login.$dirty) {
                if (!this.$v.form.login.required) return "Обязательное поле!";
            }
        },
        errorPassword() {
            if (this.$v.form.password.$dirty) {
                if (!this.$v.form.password.required) return "Обязательное поле!";
            }
        },
        errorSettingPriceValue() {
            if (this.$v.form.merchantPriceSetting.$dirty) {
                if (!this.$v.form.merchantPriceSetting.required) return "Обязательное поле!";
            }
        },
        errorSettingOrganizationValue() {
            if (this.$v.form.merchantOrganizationSetting.$dirty) {
                if (!this.$v.form.merchantOrganizationSetting.required) return "Обязательное поле!";
            }
        },
        errorHost() {
            if (this.$v.form.host.$dirty) {
                if (!this.$v.form.host.required) return "Обязательное поле!";
            }
        },
        errorPort() {
            if (this.$v.form.port.$dirty) {
                if (!this.$v.form.port.required) return "Обязательное поле!";
            }
        },
        errorFileName() {
            if (this.$v.form.fileName.$dirty) {
                if (!this.$v.form.fileName.required) return "Обязательное поле!";
            }
        },
        errorParamPeriodPrice() {
            if (this.$v.form.paramPeriodPrice.$dirty) {
                if (!this.$v.form.paramPeriodPrice.required) return "Обязательное поле!";
            }
        },
        errorParamPeriodStock() {
            if (this.$v.form.paramPeriodStock.$dirty) {
                if (!this.$v.form.paramPeriodStock.required) return "Обязательное поле!";
            }
        },
        errorParamPeriodOrder() {
            if (this.$v.form.paramPeriodOrder.$dirty) {
                if (!this.$v.form.paramPeriodOrder.required) return "Обязательное поле!";
            }
        },
        errorParamPeriodPriceStock() {
            if (this.$v.form.paramPeriodPriceStock.$dirty) {
                if (!this.$v.form.paramPeriodPriceStock.required) return "Обязательное поле!";
            }
        },
    },
}
</script>