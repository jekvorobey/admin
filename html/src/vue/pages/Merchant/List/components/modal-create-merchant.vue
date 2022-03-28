<template>
    <b-modal :id="id" title="Создание мерчанта" hide-footer ref="modal">
        <template v-slot:default="{close}">
            <div class="mb-3">
                <v-dadata
                    :value.sync="$v.form.legal_name.$model"
                    :error="errorLegalName"
                    type="PARTY"
                    @onSelect="fillOrganizationFromDaData"
                >
                    Юридическое наименование организации
                </v-dadata>
            </div>
            <div class="row">
                <v-input v-model="$v.form.inn.$model" :error="errorInn" class="col-md-6 col-12">ИНН</v-input>
                <v-input v-model="$v.form.kpp.$model" :error="errorKpp" class="col-md-6 col-12">КПП</v-input>
            </div>
            <div class="mb-3">
                <v-input v-model="$v.form.legal_address.$model" :error="errorLegalAddress">Юридический адрес</v-input>
            </div>
            <div class="mb-3">
                <v-input v-model="$v.form.fact_address.$model" :error="errorFactAddress">Фактический адрес</v-input>
            </div>

            <hr/>

            <div class="mb-3">
                <v-input v-model="$v.form.payment_account.$model" :error="errorPaymentAccount">Номер банковского счета</v-input>
            </div>

            <div class="mb-3">
                <v-dadata
                    :value.sync="$v.form.bank.$model"
                    :error="errorBank"
                    type="BANK"
                    @onSelect="fillBankDetailsFromDaData"
                >
                    Наименование банка
                </v-dadata>
            </div>

            <div class="mb-3">
                <v-input v-model="$v.form.bank_bik.$model" :error="errorBankBik">БИК банка</v-input>
            </div>
            <div class="mb-3">
                <v-input v-model="$v.form.correspondent_account.$model" :error="errorCorrespondentAccount">Номер корреспондентского счета</v-input>
            </div>
            <div class="mb-3">
                <v-input v-model="$v.form.bank_address.$model" :error="errorBankAddress">Юридический адрес банка</v-input>
            </div>

            <hr/>

            <div class="row">
                <v-input v-model="$v.form.last_name.$model" :error="errorLastName" class="col-md-4 col-12">Фамилия</v-input>
                <v-input v-model="$v.form.first_name.$model" :error="errorFirstName" class="col-md-4 col-12">Имя</v-input>
                <v-input v-model="$v.form.middle_name.$model" :error="errorMiddleName" class="col-md-4 col-12">Отчество</v-input>
            </div>
            <div class="row">
                <v-input
                        v-model="$v.form.email.$model"
                        :placeholder="emailPlaceholder"
                        :error="errorEmail"
                        class="col-md-4 col-12"
                        autocomplete="off"
                >E-mail</v-input>
                <v-input
                        v-model="$v.form.phone.$model"
                        :placeholder="telPlaceholder"
                        :error="errorPhone"
                        v-mask="telMask"
                        class="col-md-4 col-12"
                        autocomplete="off"
                >Телефон</v-input>
                <v-select
                        v-model="$v.form.communication_method.$model"
                        :error="errorCommunicationMethod"
                        :options="communicationMethodOptions"
                        class="col-md-4 col-12"
                >Способ связи</v-select>
            </div>

            <hr/>

            <div class="mb-3">
                <v-input v-model="$v.form.storage_address.$model" :error="errorStorageAddress" tag="textarea">Адреса складов отгрузки</v-input>
            </div>
            <div class="mb-3">
                <v-input v-model="$v.form.site.$model" :error="errorSite">Сайт компании</v-input>
            </div>
            <div class="mb-3">
                <input v-model="$v.form.can_integration.$model" type="checkbox">
                Подтверждение о возможности работы с Платформой с использованием автоматических механизмов интеграции
            </div>
            <div class="mb-3">
                <v-input v-model="$v.form.sale_info.$model" :error="errorSaleInfo" tag="textarea">Бренды и товарные категории, которыми вы торгуете</v-input>
            </div>

            <hr/>

            <div class="float-right mt-3">
                <b-button @click="close()" variant="outline-primary">Отмена</b-button>
                <button class="btn btn-info" @click="save">Сохранить</button>
            </div>
        </template>
    </b-modal>
</template>

<script>
    import Services from "../../../../../scripts/services/services";
    import {telMask} from '../../../../../scripts/mask';
    import {emailPlaceholder, telPlaceholder} from '../../../../../scripts/placeholder';
    import {validationMixin} from 'vuelidate';
    import {email, maxLength, minLength, required, sameAs} from 'vuelidate/lib/validators';

    import VInput from '../../../../components/controls/VInput/VInput.vue';
    import VSelect from '../../../../components/controls/VSelect/VSelect.vue';
    import VDadata from '../../../../components/controls/VDaData/VDaData.vue';

    const cleanForm = {
        legal_name: '',
        inn: '',
        kpp: '',
        legal_address: '',
        fact_address: '',

        payment_account: '',
        bank: '',
        bank_address: '',
        bank_bik: '',
        correspondent_account: '',

        last_name: '',
        first_name: '',
        middle_name: '',
        email: '',
        phone: '',
        communication_method: '',

        storage_address: '',
        site: '',
        can_integration: false,
        sale_info: '',
    };

    export default {
        name: 'modal-create-merchant',
        mixins: [validationMixin],
        props: [
            'id',
            'communicationMethods',
        ],
        components: {VInput, VSelect, VDadata},
        data() {
            let form = JSON.parse(JSON.stringify(cleanForm));
            return {
                form: form,
            };
        },
        validations: {
            form: {
                legal_name: {required},
                inn: {
                    required,
                    inn(value) {
                        if (value.length === 10 || value.length === 12) {
                            return true;
                        }

                        return false;
                    }
                },
                kpp: {
                    minLength: minLength(9),
                    maxLength: maxLength(9),
                },

                legal_address: {required},
                fact_address: {required},

                payment_account: {
                    required,
                    minLength: minLength(20),
                    maxLength: maxLength(20),
                },
                bank: {required},
                bank_address: {required},
                bank_bik: {
                    required,
                    minLength: minLength(9),
                    maxLength: maxLength(9),
                },
                correspondent_account: {
                    required,
                    minLength: minLength(20),
                    maxLength: maxLength(20),
                },

                first_name: {},
                last_name: {},
                middle_name: {},
                email: {
                    email,
                    isUnique: function() {
                        return this.isFieldUnique(this.form.email, 'email');
                    },
                    $lazy: true
                },
                phone: {
                    isUnique: function() {
                        let phone = this.form.phone.replace(/[()]|\s|-/g, '');
                        return this.isFieldUnique(phone, 'phone');
                    },
                    $lazy: true
                },
                communication_method: {},
                storage_address: {required},
                site: {required},
                can_integration: {},
                sale_info: {required},
            },
        },
        methods: {
            waitForValidation () {
                return new Promise((resolve) => {
                    const unwatch = this.$watch(() => !this.$v.$pending, (isNotPending) => {
                        if (isNotPending) {
                            resolve(!this.$v.$invalid)
                        }
                    }, {immediate: true})
                })
            },
            async save() {
                await this.$v.$touch();
                const isValid = await this.waitForValidation();
                if (!isValid) {
                    return;
                }
                if (this.form.phone) {
                    let phoneNumber = this.form.phone.replace(/[()]|\s|-/g, '');
                    this.form.phone = phoneNumber;
                    this.form.login = phoneNumber;
                }
                if (this.form.email) {
                    this.form.login_email = this.form.email;
                }
                Services.showLoader();
                Services.net().post(this.route('merchant.create'), null, this.form
                ).then(data => {
                    window.location = data.redirect;
                }).finally(() => {
                    Services.hideLoader();
                });
            },

            cancel() {
                this.$set(this, 'form', JSON.parse(JSON.stringify(cleanForm)));
                this.$v.$reset();
            },

            fillOrganizationFromDaData(suggestion) {
                const { data } = suggestion;

                if (typeof data.inn !== 'undefined') {
                    this.$v.form.inn.$model = data.inn;
                }

                if (typeof data.kpp !== 'undefined') {
                    this.$v.form.kpp.$model = data.kpp;
                }

                if (typeof data.name !== 'undefined') {
                    this.$v.form.legal_name.$model = data.name.full_with_opf;
                }

                if (typeof data.address !== 'undefined') {
                    this.$v.form.legal_address.$model = data.address.unrestricted_value;
                }
            },

            fillBankDetailsFromDaData(suggestion) {
                const { data, unrestricted_value } = suggestion;

                if (typeof unrestricted_value !== 'undefined') {
                    this.$v.form.bank.$model = unrestricted_value;
                }

                if (typeof data.bic !== 'undefined') {
                    this.$v.form.bank_bik.$model = data.bic;
                }

                if (typeof data.correspondent_account !== 'undefined') {
                    this.$v.form.correspondent_account.$model = data.correspondent_account;
                }

                if (typeof data.address !== 'undefined') {
                    this.$v.form.bank_address.$model = data.address.unrestricted_value;
                }
            },
            isFieldUnique(data, field) {
                return Services.net().get(this.getRoute('user.isUnique'), {data: data, field: field})
                    .then(data => data.isUnique);
            },
        },
        computed: {
            telMask() {
                return telMask;
            },
            telPlaceholder() {
                return telPlaceholder;
            },
            emailPlaceholder() {
                return emailPlaceholder;
            },
            communicationMethodOptions() {
                return Object.values(this.communicationMethods).map(
                    communication_method => ({value: communication_method.id, text: communication_method.name})
                );
            },
            // ===============================
            errorLegalName() {
                if (this.$v.form.legal_name.$dirty) {
                    if (!this.$v.form.legal_name.required) return "Обязательное поле!";
                }
            },
            errorInn() {
                if (this.$v.form.inn.$dirty) {
                    if (!this.$v.form.inn.required) return "Обязательное поле!";
                    if (!this.$v.form.inn.inn) return "Должно быть 10 или 12 символов!";
                }
            },
            errorKpp() {
                if (this.$v.form.kpp.$dirty) {
                    if (!this.$v.form.kpp.minLength) return "Должно быть 9 символов!";
                    if (!this.$v.form.kpp.maxLength) return "Должно быть 9 символов!";
                }
            },
            errorLegalAddress() {
                if (this.$v.form.legal_address.$dirty) {
                    if (!this.$v.form.legal_address.required) return "Обязательное поле!";
                }
            },
            errorFactAddress() {
                if (this.$v.form.fact_address.$dirty) {
                    if (!this.$v.form.fact_address.required) return "Обязательное поле!";
                }
            },
            errorPaymentAccount() {
                if (this.$v.form.payment_account.$dirty) {
                    if (!this.$v.form.payment_account.required) return "Обязательное поле!";
                    if (!this.$v.form.payment_account.minLength) return "Должно быть 20 символов!";
                    if (!this.$v.form.payment_account.maxLength) return "Должно быть 20 символов!";
                }
            },
            errorBank() {
                if (this.$v.form.bank.$dirty) {
                    if (!this.$v.form.bank.required) return "Обязательное поле!";
                }
            },
            errorBankAddress() {
                if (this.$v.form.bank_address.$dirty) {
                    if (!this.$v.form.bank_address.required) return "Обязательное поле!";
                }
            },
            errorBankBik() {
                if (this.$v.form.bank_bik.$dirty) {
                    if (!this.$v.form.bank_bik.required) return "Обязательное поле!";
                    if (!this.$v.form.bank_bik.minLength) return "Должно быть 9 символов!";
                    if (!this.$v.form.bank_bik.maxLength) return "Должно быть 9 символов!";
                }
            },
            errorCorrespondentAccount() {
                if (this.$v.form.correspondent_account.$dirty) {
                    if (!this.$v.form.correspondent_account.required) return "Обязательное поле!";
                    if (!this.$v.form.correspondent_account.minLength) return "Должно быть 20 символов!";
                    if (!this.$v.form.correspondent_account.maxLength) return "Должно быть 20 символов!";
                }
            },
            errorFirstName() {},
            errorLastName() {},
            errorMiddleName() {},
            errorEmail() {
                if (this.$v.form.email.$dirty) {
                    if (!this.$v.form.email.email) return "Введите валидный e-mail!";
                    if (!this.$v.form.email.isUnique) return "Пользователь с таким E-mail уже существует";
                }
            },
            errorPhone() {
                if (this.$v.form.phone.$dirty) {
                    if (!this.$v.form.phone.isUnique) return "Пользователь с таким телефоном уже существует";
                }
            },
            errorCommunicationMethod() {},
            errorStorageAddress() {
                if (this.$v.form.storage_address.$dirty) {
                    if (!this.$v.form.storage_address.required) return "Обязательное поле!";
                }
            },
            errorSite() {
                if (this.$v.form.site.$dirty) {
                    if (!this.$v.form.site.required) return "Обязательное поле!";
                }
            },
            errorSaleInfo() {
                if (this.$v.form.sale_info.$dirty) {
                    if (!this.$v.form.sale_info.required) return "Обязательное поле!";
                }
            },
        },
    }
</script>
