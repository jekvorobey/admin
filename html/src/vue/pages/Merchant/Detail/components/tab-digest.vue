<template>
    <table class="table table-sm">
        <thead>
        <tr>
            <th colspan="2">
                Я им моя компания. Я и мой сын
                <button @click="saveMerchant" class="btn btn-success" :disabled="!showBtn">Сохранить</button>
                <button @click="cancel" class="btn btn-outline-danger" :disabled="!showBtn">Отмена</button>
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
            <th>Адреса складов отгрузки</th>
            <td><textarea v-model="form.storage_address" class="form-control"/></td>
        </tr>
        <tr>
            <th>Бренды и товарные категории</th>
            <td><textarea v-model="form.sale_info" class="form-control"/></td>
        </tr>
        <tr>
            <th>Ставка НДС, тип налогообложения</th>
            <td><textarea v-model="form.vat_info" class="form-control"/></td>
        </tr>
        <tr>
            <th>Коммерческие условия</th>
            <td><textarea v-model="form.commercial_info" class="form-control"/></td>
        </tr>

        <tr class="table-secondary"><th colspan="2">Реквизиты юр лица</th></tr>
        <tr>
            <th>Юридический адрес</th>
            <td><input v-model="form.legal_address" class="form-control form-control-sm"/></td>
        </tr>
        <tr>
            <th>Фактический адрес</th>
            <td><input v-model="form.fact_address" class="form-control form-control-sm"/></td>
        </tr>
        <tr>
            <th>ИНН</th>
            <td><input v-model="form.inn" class="form-control form-control-sm"/></td>
        </tr>
        <tr>
            <th>КПП</th>
            <td><input v-model="form.kpp" class="form-control form-control-sm"/></td>
        </tr>

        <tr class="table-secondary"><th colspan="2">ФИО генерального</th></tr>
        <tr>
            <th>Фамилия</th>
            <td><input v-model="form.ceo_last_name" class="form-control form-control-sm"/></td>
        </tr>
        <tr>
            <th>Имя</th>
            <td><input v-model="form.ceo_first_name" class="form-control form-control-sm"/></td>
        </tr>
        <tr>
            <th>Отчество</th>
            <td><input v-model="form.ceo_middle_name" class="form-control form-control-sm"/></td>
        </tr>

        <tr class="table-secondary"><th colspan="2">Банковские реквизиты</th></tr>
        <tr>
            <th>Номер банковского счета</th>
            <td><input v-model="form.payment_account" class="form-control form-control-sm"/></td>
        </tr>
        <tr>
            <th>Банк</th>
            <td><input v-model="form.bank" class="form-control form-control-sm"/></td>
        </tr>
        <tr>
            <th>Номер корреспондентского счета банка</th>
            <td><input v-model="form.correspondent_account" class="form-control form-control-sm"/></td>
        </tr>
        <tr>
            <th>Юридический адрес банка</th>
            <td><input v-model="form.bank_address" class="form-control form-control-sm"/></td>
        </tr>
        <tr>
            <th>Бик банка</th>
            <td><input v-model="form.bank_bik" class="form-control form-control-sm"/></td>
        </tr>
        <tr class="table-secondary"><th colspan="2">Документы</th></tr>
        <tr>
            <th>Номер Договора</th>
            <td><input v-model="form.contract_number" class="form-control form-control-sm"/></td>
        </tr>
        <tr>
            <th>Дата договора</th>
            <td><date-picker v-model="form.contract_at" value-type="format" format="YYYY-MM-DD" input-class="form-control form-control-sm" class="w-100"/></td>
        </tr>
        <tr>
            <th>Документы</th>
            <td>
                <div v-for="(document, i) in documents" class="mb-1">
                    <a :href="media.file(document.file_id)" target="_blank">{{ document.name }}</a>
                    <v-delete-button btn-class="btn-danger btn-sm" @delete="deleteDocument(document.file_id, i)"/>
                </div>
                <div v-if="!documents.length">-</div>

                <div>
                    <file-input destination="merchantDocument" v-if="!form.file" @uploaded="(data) => form.file = data" class="mb-3"></file-input>
                    <div v-else class="alert alert-success py-1 px-3" role="alert">
                        Файл <a :href="form.file.url" target="_blank" class="alert-link">{{ form.file.name }}</a> загружен
                        <v-delete-button @delete="form.file = null" btn-class="btn-danger btn-sm"/>
                        <button class="btn btn-success btn-sm" @click="createDocument"><fa-icon icon="plus"/></button>
                    </div>
                </div>
            </td>
        </tr>
        </tbody>
    </table>
</template>

<script>
import Services from '../../../../../scripts/services/services.js';
import VDeleteButton from '../../../../components/controls/VDeleteButton/VDeleteButton.vue';
import FileInput from '../../../../components/controls/FileInput/FileInput.vue';
import DatePicker from 'vue2-datepicker';
import 'vue2-datepicker/index.css';
import 'vue2-datepicker/locale/ru.js';

export default {
    name: 'tab-digest',
    components: {FileInput, VDeleteButton, DatePicker},
    props: ['model'],
    data() {
        return {
            form: {
                legal_address: this.model.legal_address,
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
                storage_address: this.model.storage_address,
                sale_info: this.model.sale_info,
                vat_info: this.model.vat_info,
                commercial_info: this.model.commercial_info,
                contract_number: this.model.contract_number,
                contract_at: this.model.contract_at ? this.model.contract_at : '',

                file: null,
            },
            documents: [],
        }
    },
    methods: {
        saveMerchant() {
            Services.showLoader();
            Services.net().post(this.getRoute('merchant.detail.edit', {id: this.merchant.id}), {
                merchant: this.form
            }).then(() => {
                this.merchant.legal_address = this.form.legal_address;
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
                this.merchant.storage_address = this.form.storage_address;
                this.merchant.sale_info = this.form.sale_info;
                this.merchant.vat_info = this.form.vat_info;
                this.merchant.commercial_info = this.form.commercial_info;
                this.merchant.contract_number = this.form.contract_number;
                this.merchant.contract_at = this.form.contract_at;
                Services.msg("Изменения сохранены");
            }).finally(() => {
                Services.hideLoader();
            })
        },
        cancel() {
            this.form.legal_address = this.merchant.legal_address;
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
            this.form.storage_address = this.merchant.storage_address;
            this.form.sale_info = this.merchant.sale_info;
            this.form.vat_info = this.merchant.vat_info;
            this.form.commercial_info = this.merchant.commercial_info;
            this.form.contract_number = this.merchant.contract_number;
            this.form.contract_at = this.merchant.contract_at;
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
        createDocument() {
            Services.showLoader();
            Services.net().post(this.getRoute('merchant.detail.main.document.create', {
                id: this.merchant.id,
            }), {
                file_id: this.form.file.id,
            }).then(data => {
                this.$set(this.documents, this.documents.length, {
                    file_id: this.form.file.id,
                    name: this.form.file.name,
                });
                this.form.file = null;
                Services.msg("Изменения сохранены");
            }).finally(() => {
                Services.hideLoader();
            })
        },
    },
    computed: {
        merchant: {
            get() {return this.model},
            set(value) {this.$emit('update:model', value)},
        },
        showBtn() {
            return (this.merchant.legal_address || '') !== (this.form.legal_address || '') ||
                (this.merchant.inn || '') !== (this.form.inn || '') ||
                (this.merchant.kpp || '') !== (this.form.kpp || '') ||
                (this.merchant.fact_address || '') !== (this.form.fact_address || '') ||
                (this.merchant.ceo_last_name || '') !== (this.form.ceo_last_name || '') ||
                (this.merchant.ceo_first_name || '') !== (this.form.ceo_first_name || '') ||
                (this.merchant.ceo_middle_name || '') !== (this.form.ceo_middle_name || '') ||
                (this.merchant.payment_account || '') !== (this.form.payment_account || '') ||
                (this.merchant.correspondent_account || '') !== (this.form.correspondent_account || '') ||
                (this.merchant.bank || '') !== (this.form.bank || '') ||
                (this.merchant.bank_address || '') !== (this.form.bank_address || '') ||
                (this.merchant.bank_bik || '') !== (this.form.bank_bik || '') ||
                (this.merchant.storage_address || '') !== (this.form.storage_address || '') ||
                (this.merchant.sale_info || '') !== (this.form.sale_info || '') ||
                (this.merchant.vat_info || '') !== (this.form.vat_info || '') ||
                (this.merchant.commercial_info || '') !== (this.form.commercial_info || '') ||
                (this.merchant.contract_number || '') !== (this.form.contract_number || '') ||
                (this.merchant.contract_at || '') !== (this.form.contract_at || '');
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

<style scoped>

</style>