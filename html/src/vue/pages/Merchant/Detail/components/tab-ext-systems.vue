<template>
    <div>
        <div v-if="!extSystem && canUpdate(blocks.merchants)">
            <v-select class="col-md-4 col-6" :options="extSystemOptions" v-model="extSystemsSelect.driver_id"><h4>Выберите интеграцию</h4></v-select>
            <div v-if="isMoySklad(extSystemsSelect.driver_id)" >
                <div class="row">
                    <v-input v-model="$v.form.token.$model" :error="errorToken" class="col-md-4 col-12"><h5>Токен</h5></v-input>
                    <v-input v-model="$v.form.login.$model" :error="errorLogin" class="col-md-4 col-12"><h5>Логин</h5></v-input>
                    <v-input v-model="$v.form.password.$model" :error="errorPassword" class="col-md-4 col-12"><h5>Пароль</h5></v-input>
                </div>
                <div class="row">
                    <v-input v-model="$v.form.settingName.$model" :error="errorSettingName" class="col-md-4 col-12"><h5>Наименование настройки</h5></v-input>
                    <v-input v-model="$v.form.settingValue.$model" :error="errorSettingValue" class="col-md-4 col-12"><h5>Значение настройки</h5></v-input>
                </div>
            </div>
            <button @click="create()" class="btn btn-success btn-md">
                Создать интеграцию
            </button>
        </div>

        <template v-if="extSystem && is1C(extSystem.driver)">
            <table class="table table-sm">
                <tbody>
                <tr>
                    <th width="400px">ID</th>
                    <td>{{ extSystem.id }}</td>
                </tr>
                <tr v-if="isMoySklad(extSystem.driver)">
                    <th width="400px">Токен</th>
                    <td>{{ extSystem.connection_params.token }}</td>
                </tr>
                <tr>
                    <th width="400px">Логин</th>
                    <td>{{ extSystem.connection_params.login }}</td>
                </tr>
                <tr>
                    <th width="400px">Пароль</th>
                    <td>{{ extSystem.connection_params.password }}</td>
                </tr>
                <tr>
                    <th width="400px">Хост</th>
                    <td>{{ host }}</td>
                </tr>
                <tr>
                    <th width="400px">Дата создания</th>
                    <td>{{ datetimePrint(extSystem.created_at) }}</td>
                </tr>
                </tbody>
            </table>
        </template>

        <template v-else-if="extSystem">
            <div class="row">
                <v-input v-model="$v.form.token.$model" :error="errorToken" class="col-md-4 col-12"><h5>Токен</h5></v-input>
                <v-input v-model="$v.form.host.$model" disabled="disabled" class="col-md-4 col-12"><h5>Хост</h5></v-input>
            </div>
            <div class="row">
                <v-input v-model="$v.form.login.$model" :error="errorLogin" class="col-md-4 col-12"><h5>Логин</h5></v-input>
                <v-input v-model="$v.form.password.$model" :error="errorPassword" class="col-md-4 col-12"><h5>Пароль</h5></v-input>
            </div>
            <div class="row">
                <v-input v-model="$v.form.settingName.$model" :error="errorSettingName" class="col-md-4 col-12"><h5>Наименование настройки</h5></v-input>
                <v-input v-model="$v.form.settingValue.$model" :error="errorSettingValue" class="col-md-4 col-12"><h5>Значение настройки</h5></v-input>
            </div>
            <button v-if="canUpdate(blocks.merchants)" @click="update()" class="btn btn-success btn-md">
                Сохранить
            </button>
        </template>
    </div>
</template>

<script>

import Services from "../../../../../scripts/services/services";
import {mapActions} from "vuex";
import {validationMixin} from 'vuelidate';
import {required, requiredIf} from 'vuelidate/lib/validators';
import VSelect from '../../../../components/controls/VSelect/VSelect.vue';
import VInput from '../../../../components/controls/VInput/VInput.vue';

export default {
    mixins: [validationMixin],
    components: {
        VSelect,
        VInput,
    },
    name: 'tab-ext-systems',
    props: ['id'],
    data() {
        return {
            form: {
                token: '',
                login: '',
                password: '',
                host: '',
                settingName: '',
                settingValue: '',
            },
            extSystem: {},
            host: '',
            extSystemsOptions: [],
            extSystemsSelect: {
                driver_id: this.extSystem ? this.extSystem.driver : null,
            },
        }
    },
    validations() {
        return {
            form: {
                token: {
                    required: requiredIf(function(form){
                        return form.login === ''
                    }),
                },
                login: {
                    required: requiredIf(function(form){
                        return form.token === ''
                    }),
                },
                password: {
                    required: requiredIf(function(form){
                        return form.login !== ''
                    }),
                },
                host: '',
                settingName: {
                    required: required,
                },
                settingValue: {
                    required: required,
                },
            }
        };
    },
    methods: {
        ...mapActions({
            showMessageBox: 'modal/showMessageBox',
        }),
        loadExtSystem() {
            Services.showLoader();

            Services.net().get(this.getRoute(
                'merchant.detail.extSystems',
                {id: this.id, settingName: this.form.settingName}
            )).then(data => {
                this.extSystem = data.extSystem;
                this.extSystemsOptions = data.extSystemsOptions;
                this.host = data.host;
                this.form.token = data.extSystem.connection_params.token;
                this.form.login = data.extSystem.connection_params.login;
                this.form.password = data.extSystem.connection_params.password;
                this.form.host = data.host;
                this.form.settingName = data.merchantSetting.name;
                this.form.settingValue = data.merchantSetting.value;
            }).finally(() => {
                Services.hideLoader();
            })
        },
        create() {
            Services.showLoader();
            let formData = {
                token: this.form.token,
                login: this.form.login,
                password: this.form.password,
                driver: this.extSystemsSelect.driver_id,
            };
            Services.net().post(
                this.getRoute('merchant.detail.extSystems.store', {id: this.id}), {}, formData
            ).then(() => {
                Services.msg('Интеграция успешно создана');
                this.loadExtSystem();
            }).finally(() => {
                Services.hideLoader();
            })
        },
        update() {
            Services.showLoader();
            let formData = {
                merchantId: this.id,
                token: this.form.token,
                login: this.form.login,
                password: this.form.password,
            };
            Services.net().put(
                this.getRoute('merchant.detail.extSystems.update', {id: this.extSystem.id}), {}, formData
            ).then(() => {
                Services.msg('Интеграция успешно обновлена');
                this.loadExtSystem();
            }).finally(() => {
                Services.hideLoader();
            })
        },
        is1C(driverId) {
            return driverId === 5 || driverId === '5'
        },
        isMoySklad(driverId) {
            return driverId === 1 || driverId === '1'
        },
    },
    computed: {
        extSystemOptions() {
            let options = Object.values(this.extSystemsOptions)
                .map(extSystem => ({ value: extSystem.id, text: extSystem.name }));
            options.unshift({ value: null, text: '-' });

            return options;
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
        errorSettingName() {
            if (this.$v.form.settingName.$dirty) {
                if (!this.$v.form.settingName.required) return "Обязательное поле!";
            }
        },
        errorSettingValue() {
            if (this.$v.form.settingValue.$dirty) {
                if (!this.$v.form.settingValue.required) return "Обязательное поле!";
            }
        },
    },
    created() {
        this.loadExtSystem();
    }
}
</script>
