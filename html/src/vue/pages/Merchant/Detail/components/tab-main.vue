<template>
    <div>
        <div class="row">
            <div class="col-12 col-md-5">
                <div class="card mb-3">
                    <div class="card-header">Загрузить данные организации из DaData</div>
                    <div class="card-body">
                        <v-dadata
                            :value.sync="daDataOrganization"
                            type="PARTY"
                            @onSelect="fillOrganizationFromDaData"
                        />
                        <p class="small mb-0">
                            Выберите организацию из выпадающего списка, чтобы актуализировать поля мерчанта
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <table class="table table-sm">
                    <thead>
                    <tr>
                        <th colspan="2">
                            Основная информация
                            <template v-if="canUpdate(blocks.merchants)">
                                <button @click="saveMerchant" class="btn btn-success" :disabled="!$v.form.$anyDirty">Сохранить</button>
                                <button @click="cancel" class="btn btn-outline-danger" :disabled="!$v.form.$anyDirty">Отмена</button>
                            </template>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <th width="400px">ID</th>
                        <td>{{ merchant.id }}</td>
                    </tr>
                    <tr>
                        <th>Дата регистрации</th>
                        <td>{{ datetimePrint(merchant.created_at) }}</td>
                    </tr>
                    <tr>
                        <th>Коммерческие условия</th>
                        <td><v-input tag="textarea" v-model="$v.form.commercial_info.$model"/></td>
                    </tr>

                    <tr class="table-secondary"><th colspan="2">Реквизиты юр лица</th></tr>
                    <tr>
                      <th>Название юрлица*</th>
                      <td>
                        <v-input v-model="$v.form.legal_name.$model" :error="errorLegalName" />
                      </td>
                    </tr>
                    <tr>
                        <th>Юридический адрес*</th>
                        <td>
                            <v-input v-model="$v.form.legal_address.$model" :error="errorLegalAddress" />
                        </td>
                    </tr>
                    <tr>
                        <th>Фактический адрес*</th>
                        <td><v-input v-model="$v.form.fact_address.$model" :error="errorFactAddress"/></td>
                    </tr>
                    <tr>
                        <th>ИНН*</th>
                        <td><v-input v-model="$v.form.inn.$model" :error="errorInn"/></td>
                    </tr>
                    <tr>
                        <th>КПП</th>
                        <td><v-input v-model="$v.form.kpp.$model"/></td>
                    </tr>

                    <tr class="table-secondary"><th colspan="2">ФИО генерального</th></tr>
                    <tr>
                        <th>Фамилия</th>
                        <td><v-input v-model="$v.form.ceo_last_name.$model"/></td>
                    </tr>
                    <tr>
                        <th>Имя</th>
                        <td><v-input v-model="$v.form.ceo_first_name.$model"/></td>
                    </tr>
                    <tr>
                        <th>Отчество</th>
                        <td><v-input v-model="$v.form.ceo_middle_name.$model"/></td>
                    </tr>

                    <tr class="table-secondary"><th colspan="2">Банковские реквизиты</th></tr>
                    <tr>
                        <th>Номер банковского счета*</th>
                        <td><v-input v-model="$v.form.payment_account.$model" :error="errorPaymentAccount"/></td>
                    </tr>
                    <tr>
                        <th>Банк*</th>
                        <td>
                            <v-dadata
                                type="BANK"
                                :value.sync="$v.form.bank.$model"
                                :error="errorBank"
                                @onSelect="fillBankDetailsFromDaData"
                            />
                        </td>
                    </tr>
                    <tr>
                        <th>Номер корреспондентского счета банка*</th>
                        <td><v-input v-model="$v.form.correspondent_account.$model" :error="errorCorrespondentAccount"/></td>
                    </tr>
                    <tr>
                        <th>Юридический адрес банка*</th>
                        <td><v-input v-model="$v.form.bank_address.$model" :error="errorBankAddress"/></td>
                    </tr>
                    <tr>
                        <th>БИК банка*</th>
                        <td><v-input v-model="$v.form.bank_bik.$model" :error="errorBankBik"/></td>
                    </tr>
                    <tr class="table-secondary"><th colspan="2">Документы для отчета комиссионера</th></tr>
                    <tr>
                        <th>Номер Договора</th>
                        <td><v-input v-model="$v.form.commissionaire_contract_number.$model"/></td>
                    </tr>
                    <tr>
                        <th>Дата договора</th>
                        <td><date-picker v-model="$v.form.commissionaire_contract_at.$model" value-type="format" format="YYYY-MM-DD" input-class="form-control form-control-sm" class="w-100"/></td>
                    </tr>
                    <tr>
                        <th>Документы</th>
                        <td>
                            <div v-for="(document, i) in commissionaireDocuments" class="mb-1">
                                <a :href="media.file(document.file_id)" target="_blank">{{ document.name }}</a>
                                <v-delete-button btn-class="btn-danger btn-sm" @delete="deleteDocument(document.file_id, i)"/>
                            </div>
                            <div v-if="!commissionaireDocuments.length">-</div>

                            <div>
                                <file-input destination="merchantDocument" v-if="!form.commissionaireFile" @uploaded="(data) => form.commissionaireFile = data" class="mb-3"></file-input>
                                <div v-else class="alert alert-success py-1 px-3" role="alert">
                                    Файл <a :href="form.commissionaireFile.url" target="_blank" class="alert-link">{{ form.commissionaireFile.name }}</a> загружен
                                    <v-delete-button @delete="form.commissionaireFile = null" btn-class="btn-danger btn-sm"/>
                                    <button class="btn btn-success btn-sm" @click="createDocument(merchantDocumentTypes.commissionaire, 'commissionaireFile')"><fa-icon icon="plus"/></button>
                                </div>
                            </div>
                        </td>
                    </tr>

                    <tr class="table-secondary"><th colspan="2">Агентские документы для МК</th></tr>
                    <tr>
                      <th>Номер Договора</th>
                      <td><v-input v-model="$v.form.agent_contract_number.$model"/></td>
                    </tr>
                    <tr>
                      <th>Дата договора</th>
                      <td><date-picker v-model="$v.form.agent_contract_at.$model" value-type="format" format="YYYY-MM-DD" input-class="form-control form-control-sm" class="w-100"/></td>
                    </tr>
                    <tr>
                      <th>Документы</th>
                      <td>
                        <div v-for="(document, i) in agentDocuments" class="mb-1">
                          <a :href="media.file(document.file_id)" target="_blank">{{ document.name }}</a>
                          <v-delete-button btn-class="btn-danger btn-sm" @delete="deleteDocument(document.file_id, i)"/>
                        </div>
                        <div v-if="!agentDocuments.length">-</div>

                        <div>
                          <file-input destination="merchantDocument" v-if="!form.agentFile" @uploaded="(data) => form.agentFile = data" class="mb-3"></file-input>
                          <div v-else class="alert alert-success py-1 px-3" role="alert">
                            Файл <a :href="form.agentFile.url" target="_blank" class="alert-link">{{ form.agentFile.name }}</a> загружен
                            <v-delete-button @delete="form.agentFile = null" btn-class="btn-danger btn-sm"/>
                            <button class="btn btn-success btn-sm" @click="createDocument(merchantDocumentTypes.agent, 'agentFile')"><fa-icon icon="plus"/></button>
                          </div>
                        </div>
                      </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script>
    import Services from '../../../../../scripts/services/services.js';
    import VDeleteButton from '../../../../components/controls/VDeleteButton/VDeleteButton.vue';
    import FileInput from '../../../../components/controls/FileInput/FileInput.vue';
    import VInput from '../../../../components/controls/VInput/VInput.vue';
    import DatePicker from 'vue2-datepicker';
    import 'vue2-datepicker/index.css';
    import 'vue2-datepicker/locale/ru.js';

    import {required, requiredIf, minLength} from 'vuelidate/lib/validators';
    import {validationMixin} from 'vuelidate';
    import VDadata from "../../../../components/controls/VDaData/VDaData.vue";

    export default {
    name: 'tab-main',
    components: {VDadata, VInput, FileInput, VDeleteButton, DatePicker},
    props: ['model', 'categoryList', 'brandList'],
    mixins: [
        validationMixin,
    ],
    data() {
        return {
            form: {
                legal_address: this.model.legal_address,
                legal_name: this.model.legal_name,
                inn: this.model.inn,
                kpp: this.model.kpp,
                fact_address: this.model.fact_address,
                ceo_last_name: this.model.ceo_last_name,
                ceo_first_name: this.model.ceo_first_name,
                ceo_middle_name: this.model.ceo_middle_name,
                payment_account: this.model.payment_account,
                correspondent_account: this.model.correspondent_account,
                bank: this.model.bank,
                bank_address: this.model.bank_address,
                bank_bik: this.model.bank_bik,
                commercial_info: this.model.commercial_info,
                commissionaire_contract_number: this.model.commissionaire_contract_number,
                commissionaire_contract_at: this.model.commissionaire_contract_at ? this.model.commissionaire_contract_at : '',
                agent_contract_number: this.model.agent_contract_number,
                agent_contract_at: this.model.agent_contract_at ? this.model.agent_contract_at : '',

                agentFile: null,
                commissionaireFile: null,
            },
            daDataOrganization: '',
            documents: [],
            selectedBrand: null,
            selectedCategory: null,
        }
    },
    validations() {
        const notRequired = {required: requiredIf(() => {return false;})};

        return {
            form: {
                legal_address: {required},
                legal_name: {required},
                inn: {required},
                kpp: {notRequired},
                fact_address: {required},
                ceo_last_name: {notRequired},
                ceo_first_name: {notRequired},
                ceo_middle_name: {notRequired},
                payment_account: {required},
                correspondent_account: {required},
                bank: {required},
                bank_address: {required},
                bank_bik: {required},
                commercial_info: {notRequired},
                commissionaire_contract_number: {notRequired},
                commissionaire_contract_at: {notRequired},
                agent_contract_number: {notRequired},
                agent_contract_at: {notRequired},
            },
        };
    },
    methods: {
        saveMerchant() {
            this.$v.$touch();
            if (this.$v.$invalid) {
                return;
            }

            Services.showLoader();
            Services.net().post(this.getRoute('merchant.detail.edit', {id: this.merchant.id}), {
                merchant: this.form
            }).then(() => {
                this.merchant.legal_address = this.form.legal_address;
                this.merchant.legal_name = this.form.legal_name;
                this.merchant.inn = this.form.inn;
                this.merchant.kpp = this.form.kpp;
                this.merchant.fact_address = this.form.fact_address;
                this.merchant.ceo_last_name = this.form.ceo_last_name;
                this.merchant.ceo_first_name = this.form.ceo_first_name;
                this.merchant.ceo_middle_name = this.form.ceo_middle_name;
                this.merchant.payment_account = this.form.payment_account;
                this.merchant.correspondent_account = this.form.correspondent_account;
                this.merchant.bank = this.form.bank;
                this.merchant.bank_address = this.form.bank_address;
                this.merchant.bank_bik = this.form.bank_bik;
                this.merchant.commercial_info = this.form.commercial_info;
                this.merchant.commissionaire_contract_number = this.form.commissionaire_contract_number;
                this.merchant.commissionaire_contract_at = this.form.commissionaire_contract_at;
                this.merchant.agent_contract_number = this.form.agent_contract_number;
                this.merchant.agent_contract_at = this.form.agent_contract_at;
                Services.msg("Изменения сохранены");
            }).finally(() => {
                Services.hideLoader();
                this.daDataOrganization = '';
            });
        },
        cancel() {
            this.form.legal_address = this.merchant.legal_address;
            this.form.legal_name = this.merchant.legal_name;
            this.form.inn = this.merchant.inn;
            this.form.kpp = this.merchant.kpp;
            this.form.fact_address = this.merchant.fact_address;
            this.form.ceo_last_name = this.merchant.ceo_last_name;
            this.form.ceo_first_name = this.merchant.ceo_first_name;
            this.form.ceo_middle_name = this.merchant.ceo_middle_name;
            this.form.payment_account = this.merchant.payment_account;
            this.form.correspondent_account = this.merchant.correspondent_account;
            this.form.bank = this.merchant.bank;
            this.form.bank_address = this.merchant.bank_address;
            this.form.bank_bik = this.merchant.bank_bik;
            this.form.commercial_info = this.merchant.commercial_info;
            this.form.commissionaire_contract_number = this.merchant.commissionaire_contract_number;
            this.form.commissionaire_contract_at = this.merchant.commissionaire_contract_at;
            this.form.agent_contract_number = this.merchant.agent_contract_number;
            this.form.agent_contract_at = this.merchant.agent_contract_at;
        },
        deleteDocument(file_id, index) {
            Services.showLoader();
            Services.net().delete(this.getRoute('merchant.detail.main.document.delete', {
                id: this.merchant.id,
            }), {
                file_id: file_id
            }).then(data => {
                this.$delete(this.documents, index);
                Services.msg("Изменения сохранены");
            }).finally(() => {
                Services.hideLoader();
            })
        },
        createDocument(type, fileType) {
            let file = this.form[fileType]
            Services.net().post(this.getRoute('merchant.detail.main.document.create', {
                id: this.merchant.id,
            }), {
                file_id: file.id,
                type
            }).then(data => {
                this.$set(this.documents, this.documents.length, {
                    file_id: file.id,
                    name: file.name,
                    type
                });
                this.form[fileType] = null;
                Services.msg("Изменения сохранены");
            }).finally(() => {
                Services.hideLoader();
            })
        },
        addItem(array, item) {
            array.$model.push(item);
            array.$touch();
        },
        removeItem(array, item) {
            let index = array.$model.indexOf(item);
            if (index !== -1){
                array.$model.splice(index, 1);
            }
            array.$touch();
        },

        fillOrganizationFromDaData(suggestion) {
            const { data } = suggestion;

            if (typeof data.inn !== 'undefined') {
                this.$v.form.inn.$model = data.inn;
            }

            if (typeof data.kpp !== 'undefined') {
                this.$v.form.kpp.$model = data.kpp;
            }

            if (typeof data.address !== 'undefined') {
                this.$v.form.legal_address.$model = data.address.unrestricted_value;
            }
        },

        fillBankDetailsFromDaData(suggestion) {
            const { data } = suggestion;

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
        }
    },
    computed: {
        merchant: {
            get() {return this.model},
            set(value) {this.$emit('update:model', value)},
        },
        errorLegalAddress() {
            if (this.$v.form.legal_address.$dirty) {
                if (!this.$v.form.legal_address.required) {
                    return "Обязательное поле";
                }
            }
        },
        errorLegalName() {
            if (this.$v.form.legal_name.$dirty) {
                if (!this.$v.form.legal_name.required) {
                    return "Обязательное поле";
                }
            }
        },
        errorFactAddress() {
            if (this.$v.form.fact_address.$dirty) {
                if (!this.$v.form.fact_address.required) {
                    return "Обязательное поле";
                }
            }
        },
        errorInn() {
            if (this.$v.form.inn.$dirty) {
                if (!this.$v.form.inn.required) {
                    return "Обязательное поле";
                }
            }
        },
        errorPaymentAccount() {
            if (this.$v.form.payment_account.$dirty) {
                if (!this.$v.form.payment_account.required) {
                    return "Обязательное поле";
                }
            }
        },
        errorBank() {
            if (this.$v.form.bank.$dirty) {
                if (!this.$v.form.bank.required) {
                    return "Обязательное поле";
                }
            }
        },
        errorCorrespondentAccount() {
            if (this.$v.form.correspondent_account.$dirty) {
                if (!this.$v.form.correspondent_account.required) {
                    return "Обязательное поле";
                }
            }
        },
        errorBankAddress() {
            if (this.$v.form.bank_address.$dirty) {
                if (!this.$v.form.bank_address.required) {
                    return "Обязательное поле";
                }
            }
        },
        errorBankBik() {
            if (this.$v.form.bank_bik.$dirty) {
                if (!this.$v.form.bank_bik.required) {
                    return "Обязательное поле";
                }
            }
        },
        commissionaireDocuments() {
          return this.documents.filter(doc => doc.type === this.merchantDocumentTypes.commissionaire)
        },
        agentDocuments() {
          return this.documents.filter(doc => doc.type === this.merchantDocumentTypes.agent)
        }
    },
    created() {
        Services.showLoader();
        Services.net().get(this.getRoute('merchant.detail.main', {id: this.model.id})).then(data => {
            this.documents = data.documents;
        }).finally(() => {
            Services.hideLoader();
        })
    }
};
</script>

<style>
    .modified-field input {
        background-color: #fffae0;
    }
</style>
