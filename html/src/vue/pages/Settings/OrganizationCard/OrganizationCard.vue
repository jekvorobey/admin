<template>
    <layout-main>
        <form v-on:submit.prevent.stop="update">
            <h3 class="mb-3">Сведения об организации</h3>
            <div class="mb-3">
                <v-input v-model="$v.form.short_name.$model"
                         :error="errorShortName"
                         @change="() => {updateInput('short_name')}"
                >Краткое наименование организации</v-input>
            </div>
            <div class="mb-3">
                <v-input v-model="$v.form.full_name.$model"
                         :error="errorFullName"
                         @change="() => {updateInput('full_name')}"
                >Полное наименование организации</v-input>
            </div>

            <hr/>

            <div class="row">
                <v-input v-model="$v.form.inn.$model"
                         :error="errorInn"
                         class="col-md-6 col-12"
                         @change="() => {updateInput('inn')}"
                >ИНН</v-input>
                <v-input v-model="$v.form.kpp.$model"
                         :error="errorKpp"
                         class="col-md-6 col-12"
                         @change="() => {updateInput('kpp')}"
                >КПП</v-input>
            </div>
            <div class="row">
                <v-input v-model="$v.form.okpo.$model"
                         :error="errorOkpo"
                         class="col-md-6 col-12"
                         @change="() => {updateInput('okpo')}"
                >ОКПО</v-input>
                <v-input v-model="$v.form.ogrn.$model"
                         :error="errorOgrn"
                         class="col-md-6 col-12"
                         @change="() => {updateInput('ogrn')}"
                >ОГРН</v-input>
            </div>

            <hr/>


            <div class="mb-3">
                <v-dadata
                        :value="$v.form.fact_address.$model"
                        :error="errorFactAddress"
                        @onSelect="onFactAddressAdd"
                >Фактический адрес</v-dadata>
            </div>
            <div class="mb-3">
                <v-dadata
                        :value="$v.form.legal_address.$model"
                        :error="errorLegalAddress"
                        @onSelect="onLegalAddressAdd"
                >Юридический адрес</v-dadata>
            </div>

            <hr/>

            <div class="mb-3">
                <v-input v-model="$v.form.payment_account.$model"
                         :error="errorPaymentAccount"
                         @change="() => {updateInput('payment_account')}"
                >Номер банковского счета</v-input>
            </div>
            <div class="row">
                <v-input v-model="$v.form.bank_bik.$model"
                         :error="errorBankBik"
                         class="col-md-3 col-12"
                         @change="() => {updateInput('bank_bik')}"
                >БИК банка</v-input>
                <v-input v-model="$v.form.bank_name.$model"
                         :error="errorBankName"
                         class="col-md-9 col-12"
                         @change="() => {updateInput('bank_name')}"
                >Наименование банка</v-input>
            </div>
            <div class="mb-3">
                <v-input v-model="$v.form.correspondent_account.$model"
                         :error="errorCorrespondentAccount"
                         @change="() => {updateInput('correspondent_account')}"
                >Номер корреспондентского счета</v-input>
            </div>


            <h3 class="mb-3">Сведения о генеральном директоре</h3>
            <div class="row">
                <v-input v-model="$v.form.ceo_last_name.$model"
                         :error="errorCeoLastName"
                         class="col-md-4 col-12"
                         @change="() => {updateInput('ceo_last_name')}"
                >Фамилия</v-input>
                <v-input v-model="$v.form.ceo_first_name.$model"
                         :error="errorCeoFirstName"
                         class="col-md-4 col-12"
                         @change="() => {updateInput('ceo_first_name')}"
                >Имя</v-input>
                <v-input v-model="$v.form.ceo_middle_name.$model"
                         :error="errorCeoMiddleName"
                         class="col-md-4 col-12"
                         @change="() => {updateInput('ceo_middle_name')}"
                >Отчество</v-input>
            </div>
            <div class="mb-3">
                <v-input v-model="$v.form.ceo_document_number.$model"
                         :error="errorCeoDocumentNumber"
                         @change="() => {updateInput('ceo_document_number')}"
                >Номер документа, подтверждающий полномочия</v-input>
            </div>


            <h3 class="mb-3">Сведения о менеджере по логистике</h3>
            <div class="row">
                <v-input v-model="$v.form.logistics_manager_last_name.$model"
                         :error="errorLogisticsManagerLastName"
                         class="col-md-4 col-12"
                         @change="() => {updateInput('logistics_manager_last_name')}"
                >Фамилия</v-input>
                <v-input v-model="$v.form.logistics_manager_first_name.$model"
                         :error="errorLogisticsManagerFirstName"
                         class="col-md-4 col-12"
                         @change="() => {updateInput('logistics_manager_first_name')}"
                >Имя</v-input>
                <v-input v-model="$v.form.logistics_manager_middle_name.$model"
                         :error="errorLogisticsManagerMiddleName"
                         class="col-md-4 col-12"
                         @change="() => {updateInput('logistics_manager_middle_name')}"
                >Отчество</v-input>
            </div>
            <div class="row">
                <v-input
                        v-model="$v.form.logistics_manager_phone.$model"
                        :placeholder="telPlaceholder"
                        :error="errorLogisticsManagerPhone"
                        v-mask="telMask"
                        class="col-md-6 col-12"
                        autocomplete="off"
                        @change="() => {updateInput('logistics_manager_phone')}"
                >Контактный телефон</v-input>
                <v-input
                        v-model="$v.form.logistics_manager_email.$model"
                        :placeholder="emailPlaceholder"
                        :error="errorLogisticsManagerEmail"
                        class="col-md-6 col-12"
                        autocomplete="off"
                        @change="() => {updateInput('logistics_manager_email')}"
                >Контактный e-mail</v-input>
            </div>


            <h3 class="mb-3">Контактная информация</h3>
            <div class="row">
                <v-input
                        v-model="$v.form.contact_centre_phone.$model"
                        :placeholder="telPlaceholder"
                        :error="errorContactCentrePhone"
                        v-mask="telMask"
                        class="col-md-6 col-12"
                        autocomplete="off"
                        @change="() => {updateInput('contact_centre_phone')}"
                >Телефон контактного-центра</v-input>
                <v-input
                        v-model="$v.form.social_phone.$model"
                        :placeholder="telPlaceholder"
                        :error="errorSocialPhone"
                        v-mask="telMask"
                        class="col-md-6 col-12"
                        autocomplete="off"
                        @change="() => {updateInput('social_phone')}"
                >Мобильный телефон для мессенджеров</v-input>
            </div>
            <v-input
                    v-model="$v.form.email_for_merchant.$model"
                    :placeholder="emailPlaceholder"
                    :error="errorEmailForMerchant"
                    autocomplete="off"
                    @change="() => {updateInput('email_for_merchant')}"
            >E-mail для мерчантов</v-input>
            <v-input
                    v-model="$v.form.common_email.$model"
                    :placeholder="emailPlaceholder"
                    :error="errorCommonEmail"
                    autocomplete="off"
                    @change="() => {updateInput('common_email')}"
            >Общий e-mail</v-input>
            <v-input
                    v-model="$v.form.email_for_claim.$model"
                    :placeholder="emailPlaceholder"
                    :error="errorEmailForClaim"
                    autocomplete="off"
                    @change="() => {updateInput('email_for_claim')}"
            >E-mail для заявок</v-input>


            <div class="form-group mt-3" v-if="canUpdate(blocks.settings)">
                <button type="submit" class="btn btn-primary">Сохранить</button>
            </div>
        </form>
    </layout-main>
</template>

<script>
import Services from '../../../../scripts/services/services';
import {telMask} from '../../../../scripts/mask';
import {emailPlaceholder, telPlaceholder} from '../../../../scripts/placeholder';
import {validationMixin} from 'vuelidate';
import {required, minLength, maxLength, email} from 'vuelidate/lib/validators';

import VInput from '../../../components/controls/VInput/VInput.vue';
import VDadata from '../../../components/controls/VDaData/VDaData.vue';

export default {
    name: 'page-organization-card',
    components: {
        VInput,
        VDadata
    },
    props: {
        short_name: String,
        full_name: String,

        inn: String,
        kpp: String,
        okpo: String,
        ogrn: String,

        fact_address: String,
        legal_address: String,

        payment_account: String,
        bank_bik: String,
        bank_name: String,
        correspondent_account: String,

        ceo_last_name: String,
        ceo_first_name: String,
        ceo_middle_name: String,
        ceo_document_number: String,

        logistics_manager_last_name: String,
        logistics_manager_first_name: String,
        logistics_manager_middle_name: String,
        logistics_manager_phone: String,
        logistics_manager_email: String,

        contact_centre_phone: String,
        social_phone: String,
        email_for_merchant: String,
        common_email: String,
        email_for_claim: String,
    },
    mixins: [validationMixin],
    data() {
        return {
            form: {
                short_name: this.short_name,
                full_name: this.full_name,

                inn: this.inn,
                kpp: this.kpp,
                okpo: this.okpo,
                ogrn: this.ogrn,

                fact_address: this.fact_address,
                legal_address: this.legal_address,

                payment_account: this.payment_account,
                bank_bik: this.bank_bik,
                bank_name: this.bank_name,
                correspondent_account: this.correspondent_account,

                ceo_last_name: this.ceo_last_name,
                ceo_first_name: this.ceo_first_name,
                ceo_middle_name: this.ceo_middle_name,
                ceo_document_number: this.ceo_document_number,

                logistics_manager_last_name: this.logistics_manager_last_name,
                logistics_manager_first_name: this.logistics_manager_first_name,
                logistics_manager_middle_name: this.logistics_manager_middle_name,
                logistics_manager_phone: this.logistics_manager_phone,
                logistics_manager_email: this.logistics_manager_email,

                contact_centre_phone: this.contact_centre_phone,
                social_phone: this.social_phone,
                email_for_merchant: this.email_for_merchant,
                common_email: this.common_email,
                email_for_claim: this.email_for_claim,
            },
            requestData: {},
        };
    },
    validations: {
        form: {
            short_name: {required},
            full_name: {required},

            inn: {
                required,
                minLength: minLength(10),
                maxLength: maxLength(10),
            },
            kpp: {
                required,
                minLength: minLength(9),
                maxLength: maxLength(9),
            },
            okpo: {
                required,
                minLength: minLength(8),
                maxLength: maxLength(8),
            },
            ogrn: {
                required,
                minLength: minLength(13),
                maxLength: maxLength(13),
            },

            fact_address: {required},
            legal_address: {required},


            payment_account: {
                required,
                minLength: minLength(20),
                maxLength: maxLength(20),
            },
            bank_bik: {
                required,
                minLength: minLength(9),
                maxLength: maxLength(9),
            },
            bank_name: {required},
            correspondent_account: {
                required,
                minLength: minLength(20),
                maxLength: maxLength(20),
            },

            ceo_last_name: {required},
            ceo_first_name: {required},
            ceo_middle_name: {required},
            ceo_document_number: {required},

            logistics_manager_last_name: {required},
            logistics_manager_first_name: {required},
            logistics_manager_middle_name: {required},
            logistics_manager_phone: {required},
            logistics_manager_email: {required, email},

            contact_centre_phone: {required},
            social_phone: {required},
            email_for_merchant: {required, email},
            common_email: {required, email},
            email_for_claim: {required, email},
        },
    },
    methods: {
        updateInput(type) {
            this.requestData[type] = this.form[type];
        },
        update() {
            this.$v.$touch();

            if (this.$v.$invalid) {
                return;
            }

            Services.showLoader();
            Services.net().put(
                this.route('settings.organizationCard.update'),
                null,
                this.requestData
            ).then(() => {
                Services.msg('Данные успешно изменены');
            }).finally(() => {
                Services.hideLoader();
            });
        },
        onFactAddressAdd(suggestion) {
            this.form.fact_address = suggestion.unrestricted_value;
            this.requestData['fact_address'] = this.form['fact_address'];
        },
        onLegalAddressAdd(suggestion) {
            this.form.legal_address = suggestion.unrestricted_value;
            this.requestData['legal_address'] = this.form['legal_address'];
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

        // ===============================
        errorShortName() {
            if (this.$v.form.short_name.$dirty) {
                if (!this.$v.form.short_name.required) return "Обязательное поле!";
            }
        },
        errorFullName() {
            if (this.$v.form.full_name.$dirty) {
                if (!this.$v.form.full_name.required) return "Обязательное поле!";
            }
        },

        errorInn() {
            if (this.$v.form.inn.$dirty) {
                if (!this.$v.form.inn.required) return "Обязательное поле!";
                if (!this.$v.form.inn.minLength) return "Должно быть 10 символов!";
                if (!this.$v.form.inn.maxLength) return "Должно быть 10 символов!";
            }
        },
        errorKpp() {
            if (this.$v.form.kpp.$dirty) {
                if (!this.$v.form.kpp.required) return "Обязательное поле!";
                if (!this.$v.form.kpp.minLength) return "Должно быть 9 символов!";
                if (!this.$v.form.kpp.maxLength) return "Должно быть 9 символов!";
            }
        },
        errorOkpo() {
            if (this.$v.form.okpo.$dirty) {
                if (!this.$v.form.okpo.required) return "Обязательное поле!";
                if (!this.$v.form.okpo.minLength) return "Должно быть 8 символов!";
                if (!this.$v.form.okpo.maxLength) return "Должно быть 8 символов!";
            }
        },
        errorOgrn() {
            if (this.$v.form.ogrn.$dirty) {
                if (!this.$v.form.ogrn.required) return "Обязательное поле!";
                if (!this.$v.form.ogrn.minLength) return "Должно быть 13 символов!";
                if (!this.$v.form.ogrn.maxLength) return "Должно быть 13 символов!";
            }
        },

        errorFactAddress() {
            if (this.$v.form.fact_address.$dirty) {
                if (!this.$v.form.fact_address.required) return "Обязательное поле!";
            }
        },
        errorLegalAddress() {
            if (this.$v.form.legal_address.$dirty) {
                if (!this.$v.form.legal_address.required) return "Обязательное поле!";
            }
        },

        errorPaymentAccount() {
            if (this.$v.form.payment_account.$dirty) {
                if (!this.$v.form.payment_account.required) return "Обязательное поле!";
                if (!this.$v.form.payment_account.minLength) return "Должно быть 20 символов!";
                if (!this.$v.form.payment_account.maxLength) return "Должно быть 20 символов!";
            }
        },
        errorBankBik() {
            if (this.$v.form.bank_bik.$dirty) {
                if (!this.$v.form.bank_bik.required) return "Обязательное поле!";
                if (!this.$v.form.bank_bik.minLength) return "Должно быть 9 символов!";
                if (!this.$v.form.bank_bik.maxLength) return "Должно быть 9 символов!";
            }
        },
        errorBankName() {
            if (this.$v.form.bank_name.$dirty) {
                if (!this.$v.form.bank_name.required) return "Обязательное поле!";
            }
        },
        errorCorrespondentAccount() {
            if (this.$v.form.correspondent_account.$dirty) {
                if (!this.$v.form.correspondent_account.required) return "Обязательное поле!";
                if (!this.$v.form.correspondent_account.minLength) return "Должно быть 20 символов!";
                if (!this.$v.form.correspondent_account.maxLength) return "Должно быть 20 символов!";
            }
        },

        errorCeoLastName() {
            if (this.$v.form.ceo_last_name.$dirty) {
                if (!this.$v.form.ceo_last_name.required) return "Обязательное поле!";
            }
        },
        errorCeoFirstName() {
            if (this.$v.form.ceo_first_name.$dirty) {
                if (!this.$v.form.ceo_first_name.required) return "Обязательное поле!";
            }
        },
        errorCeoMiddleName() {
            if (this.$v.form.ceo_middle_name.$dirty) {
                if (!this.$v.form.ceo_middle_name.required) return "Обязательное поле!";
            }
        },
        errorCeoDocumentNumber() {
            if (this.$v.form.ceo_document_number.$dirty) {
                if (!this.$v.form.ceo_document_number.required) return "Обязательное поле!";
            }
        },
        errorLogisticsManagerLastName() {
            if (this.$v.form.logistics_manager_last_name.$dirty) {
                if (!this.$v.form.logistics_manager_last_name.required) return "Обязательное поле!";
            }
        },
        errorLogisticsManagerFirstName() {
            if (this.$v.form.logistics_manager_first_name.$dirty) {
                if (!this.$v.form.logistics_manager_first_name.required) return "Обязательное поле!";
            }
        },
        errorLogisticsManagerMiddleName() {
            if (this.$v.form.logistics_manager_middle_name.$dirty) {
                if (!this.$v.form.logistics_manager_middle_name.required) return "Обязательное поле!";
            }
        },
        errorLogisticsManagerPhone() {
            if (this.$v.form.logistics_manager_phone.$dirty) {
                if (!this.$v.form.logistics_manager_phone.required) return "Обязательное поле!";
            }
        },
        errorLogisticsManagerEmail() {
            if (this.$v.form.logistics_manager_email.$dirty) {
                if (!this.$v.form.logistics_manager_email.required) return "Обязательное поле!";
                if (!this.$v.form.logistics_manager_email.email) return "Введите валидный e-mail!";
            }
        },
        errorContactCentrePhone() {
            if (this.$v.form.contact_centre_phone.$dirty) {
                if (!this.$v.form.contact_centre_phone.required) return "Обязательное поле!";
            }
        },
        errorSocialPhone() {
            if (this.$v.form.social_phone.$dirty) {
                if (!this.$v.form.social_phone.required) return "Обязательное поле!";
            }
        },
        errorEmailForMerchant() {
            if (this.$v.form.email_for_merchant.$dirty) {
                if (!this.$v.form.email_for_merchant.required) return "Обязательное поле!";
                if (!this.$v.form.email_for_merchant.email) return "Введите валидный e-mail!";
            }
        },
        errorCommonEmail() {
            if (this.$v.form.common_email.$dirty) {
                if (!this.$v.form.common_email.required) return "Обязательное поле!";
                if (!this.$v.form.common_email.email) return "Введите валидный e-mail!";
            }
        },
        errorEmailForClaim() {
            if (this.$v.form.email_for_claim.$dirty) {
                if (!this.$v.form.email_for_claim.required) return "Обязательное поле!";
                if (!this.$v.form.email_for_claim.email) return "Введите валидный e-mail!";
            }
        },
    }
};
</script>
