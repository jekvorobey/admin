<template>
    <table class="table table-sm">
        <thead>
        <tr>
            <th colspan="1">
                <h3>Текущий отчетный период</h3>
            </th>
            <th colspan="1" style="text-align: right">
                <a href="#" class="btn btn-warning btn-md">
                    Войти под мерчантом <fa-icon icon="eye"/>
                </a>
                <a href="#" class="btn btn-info btn-md">
                    Поиск информации <fa-icon icon="question-circle"/>
                </a>
                <a href="#" class="btn btn-success btn-md">
                    Сохранить изменения <fa-icon icon="check"/>
                </a>
            </th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <th width="400px">Товаров на витрине</th>
            <td>###</td>
        </tr>
        <tr>
            <th>Принято заказов</th>
            <td>###</td>
        </tr>
        <tr>
            <th>Доставлено заказов</th>
            <td>###</td>
        </tr>
        <tr>
            <th>Продано товаров</th>
            <td>Продано <b>###</b> товаров на сумму <b>### руб.</b></td>
        </tr>
        <tr>
            <th>Начислено комиссии</th>
            <td>### руб.</td>
        </tr>
        <tr>
            <th>Примечание к мерчанту</th>
            <td><textarea class="form-control" placeholder="Примечание к мерчанту"/></td>
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