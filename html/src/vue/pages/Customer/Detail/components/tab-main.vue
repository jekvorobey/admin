<template>
     <table class="table table-sm">
        <thead>
        <tr>
            <th colspan="2">
                Основная информация
                <button @click="save" class="btn btn-success" :disabled="!showBtn">Сохранить</button>
                <button @click="cancel" class="btn btn-outline-danger" :disabled="!showBtn">Отмена</button>
            </th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <th>Профессиональная деятельность</th>
            <td>
                <select v-model="form.activities" multiple class="form-control form-control-sm">
                    <optgroup label="Активные">
                        <option v-for="activity in activitiesAll" v-if="activity.active" :value="activity.id">{{ activity.name }}</option>
                    </optgroup>
                    <optgroup label="Неактивные">
                        <option v-for="activity in activitiesAll" v-if="!activity.active" :value="activity.id">{{ activity.name }}</option>
                    </optgroup>
                </select>
            </td>
        </tr>
        <tr>
            <th>Пол</th>
            <td>
                <select class="form-control form-control-sm" v-model="form.gender">
                    <option :value="null">-</option>
                    <option :value="1">Женский</option>
                    <option :value="2">Мужской</option>
                </select>
            </td>
        </tr>
        <tr>
            <th>Дата рождения</th>
            <td>
                <date-picker
                    class="w-100"
                    v-model="form.birthday"
                    value-type="YYYY-MM-DD"
                    format="DD.MM.YYYY"
                    input-class="form-control form-control-sm"
                />
            </td>
        </tr>
        <tr>
            <th>Возраст</th>
            <td>{{ age }}</td>
        </tr>
        <tr>
            <th>Город</th>
            <td><input v-model="form.city" class="form-control form-control-sm"/></td>
        </tr>
        <tr>
            <th>Сумма покупок накопительным итогом</th>
            <td><a tabindex @click="openOrder">{{ order.price }}</a></td>
        </tr>
        <tr>
            <th>Доступных бонусов</th>
            <td>{{ customer.bonus }}</td>
        </tr>
        <tr>
            <th>Подписка на рассылку</th>
            <td></td>
        </tr>
        <tr>
            <th>Регистрация по ссылке</th>
            <td>
                <a v-if="customer.referrer" :href="getRoute('customers.detail', {id: customer.referrer.id})">Ссылка с ID {{ customer.referrer.id }}</a>
                <template v-else >нет</template>
            </td>
        </tr>
        <tr>
            <th>Дата регистрации</th>
            <td>{{ datetimePrint(customer.created_at) }}</td>
        </tr>
        <tr>
            <th>Профили в социальных сетях</th>
            <td>
                <div v-for="social in customer.socials">
                    {{ social.driver }}: {{ social.name }}
                </div>
                <div v-if="!customer.socials.length">-</div>
            </td>
        </tr>
        <tr>
            <th>Сертификаты</th>
            <td>
                <div v-for="(certificate, i) in certificates" class="mb-1">
                    <a :href="certificate.url" target="_blank">{{ certificate.name }}</a>
                    <v-delete-button btn-class="btn-danger btn-sm" @delete="deleteCertificate(certificate.id, i)"/>
                </div>
                <div v-if="!certificates.length">-</div>

                <div>
                    <file-input v-if="!form.file" destination='certificate' @uploaded="(data) => form.file = data" class="mb-3"></file-input>
                    <div v-else class="alert alert-success py-1 px-3" role="alert">
                        Файл <a :href="form.file.url" target="_blank" class="alert-link">{{ form.file.name }}</a> загружен
                        <v-delete-button @delete="form.file = null" btn-class="btn-danger btn-sm"/>
                        <button class="btn btn-success btn-sm" @click="createCertificate"><fa-icon icon="plus"/></button>
                    </div>
                </div>
            </td>
        </tr>
        <tr>
            <th>Персональный Менеджер</th>
            <td>
                <select class="form-control form-control-sm" v-model="form.manager_id">
                    <option :value="null">-</option>
                    <option v-for="(manager, id) in managers" :value="id">{{ manager }}</option>
                </select>
            </td>
        </tr>
        <tr>
            <th><label for="comment_internal">Служебный комментарий</label></th>
            <td>
                <textarea class="form-control" id="comment_internal" v-model="form.comment_internal"/>
            </td>
        </tr>
        <tr v-if="customer.referral">
            <th>Реферальный код</th>
            <td>
                <input class="form-control form-control-sm" v-model="form.referral_code"/>
            </td>
        </tr>
        <tr v-if="customer.referral">
            <th>Личный промокод</th>
            <td>{{personalDiscount || '-'}}</td>
        </tr>
        <template v-if="customer.referral">
            <tr class="table-secondary">
                <th colspan="2">Платежные реквизиты</th>
            </tr>
            <tr>
                <th>Наименование ИП</th>
                <td>
                    <input class="form-control form-control-sm" v-model="form.legal_info_company_name"/>
                </td>
            </tr>
            <tr>
                <th>ИНН</th>
                <td>
                    <input class="form-control form-control-sm" v-model="form.legal_info_inn"/>
                </td>
            </tr>
            <tr>
                <th>Расчетный счет</th>
                <td>
                    <input class="form-control form-control-sm" v-model="form.legal_info_payment_account"/>
                </td>
            </tr>
            <tr>
                <th>БИК</th>
                <td>
                    <input class="form-control form-control-sm" v-model="form.legal_info_bik"/>
                </td>
            </tr>
            <tr>
                <th>Банк</th>
                <td>
                    <input class="form-control form-control-sm" v-model="form.legal_info_bank"/>
                </td>
            </tr>
            <tr>
                <th>Город, в котором находится отделение банка</th>
                <td>
                    <input class="form-control form-control-sm" v-model="form.legal_info_bank_city"/>
                </td>
            </tr>
            <tr>
                <th>Корреспондентский счет банка</th>
                <td>
                    <input class="form-control form-control-sm" v-model="form.legal_info_bank_correspondent_account"/>
                </td>
            </tr>

            <tr class="table-secondary">
                <th colspan="2">Паспортные данные</th>
            </tr>
            <tr>
                <th>Фамилия</th>
                <td>
                    <input class="form-control form-control-sm" v-model="form.pdr_lastName"/>
                </td>
            </tr>
            <tr>
                <th>Имя</th>
                <td>
                    <input class="form-control form-control-sm" v-model="form.pdr_firstName"/>
                </td>
            </tr>
            <tr>
                <th>Отчество. Обязательно, если есть в паспорте.</th>
                <td>
                    <input class="form-control form-control-sm" v-model="form.pdr_middleName"/>
                </td>
            </tr>
            <tr>
                <th>Серия паспорта гражданина РФ</th>
                <td>
                    <input class="form-control form-control-sm" v-model="form.pdr_docSerial"/>
                </td>
            </tr>
            <tr>
                <th>Номер паспорта гражданина РФ</th>
                <td>
                    <input class="form-control form-control-sm" v-model="form.pdr_docNumber"/>
                </td>
            </tr>
            <tr>
                <th>Дата выдачи паспорта</th>
                <td>
                    <date-picker
                        class="w-100"
                        v-model="form.pdr_docIssueDate"
                        format="DD.MM.YYYY"
                        value-type="DD.MM.YYYY"
                        input-class="form-control form-control-sm"
                    />
                </td>
            </tr>
            <tr>
                <th>Адрес регистрации</th>
                <td>
                    <input class="form-control form-control-sm" v-model="form.legal_info_company_address"/>
                </td>
            </tr>

            <tr class="table-secondary">
                <th colspan="2">Договор реферального партнера</th>
            </tr>
            <tr>
                <th>Номер договора</th>
                <td>
                    <input class="form-control form-control-sm" v-model="form.referral_contract_number"/>
                </td>
            </tr>
            <tr>
                <th>Дата договора</th>
                <td>
                    <date-picker
                        class="w-100"
                        v-model="form.referral_contract_at"
                        value-type="YYYY-MM-DD"
                        format="DD.MM.YYYY"
                        input-class="form-control form-control-sm"
                    />
                </td>
            </tr>
            <tr>
                <th>Документы</th>
                <td>
                    <div v-for="(document, i) in referralContracts" class="mb-1">
                        <a :href="document.url" target="_blank">{{ document.name }}</a>
                        <v-delete-button btn-class="btn-danger btn-sm" @delete="deleteReferralContract(document.id, i)"/>
                    </div>
                    <div v-if="!referralContracts.length">-</div>

                    <div>
                        <file-input destination="referralContract" v-if="!form.file" @uploaded="(data) => form.file = data" class="mb-3"></file-input>
                        <div v-else class="alert alert-success py-1 px-3" role="alert">
                            Файл <a :href="form.file.url" target="_blank" class="alert-link">{{ form.file.name }}</a> загружен
                            <v-delete-button @delete="form.file = null" btn-class="btn-danger btn-sm"/>
                            <button class="btn btn-success btn-sm" @click="createReferralContract"><fa-icon icon="plus"/></button>
                        </div>
                    </div>
                </td>
            </tr>
        </template>
        </tbody>
    </table>
</template>

<script>
import Services from '../../../../../scripts/services/services.js';
import moment from 'moment';
import VDeleteButton from '../../../../components/controls/VDeleteButton/VDeleteButton.vue';
import FileInput from '../../../../components/controls/FileInput/FileInput.vue';
import DatePicker from 'vue2-datepicker';
import 'vue2-datepicker/index.css';

export default {
    name: 'tab-main',
    components: {DatePicker, FileInput, VDeleteButton},
    props: ['model', 'order'],
    data() {
        return {
            certificates: [],
            referralContracts: [],
            managers: [],
            activitiesAll: [],
            savedActivities: [],
            personalDiscount: '',

            form: {
                comment_internal: this.model.comment_internal,
                manager_id: this.model.manager_id,
                gender: this.model.gender,
                city: this.model.city,
                birthday: this.model.birthday,
                activities: [],
                file: null,
                legal_info_company_name: this.model.legal_info_company_name,
                legal_info_company_address: this.model.legal_info_company_address,
                legal_info_inn: this.model.legal_info_inn,
                legal_info_payment_account: this.model.legal_info_payment_account,
                legal_info_bik: this.model.legal_info_bik,
                legal_info_bank: this.model.legal_info_bank,
                legal_info_bank_correspondent_account: this.model.legal_info_bank_correspondent_account,
                legal_info_bank_city: this.model.legal_info_bank_city,
                referral_code: this.model.referral_code,
                referral_contract_number: this.model.referral_contract_number,
                referral_contract_at: this.model.referral_contract_at,
                pdr_lastName: this.model.passport.surname,
                pdr_firstName: this.model.passport.name,
                pdr_middleName: this.model.passport.patronymic,
                pdr_docNumber: this.model.passport.no,
                pdr_docSerial: this.model.passport.serial,
                pdr_docIssueDate: this.model.passport.issue_date,
            }
        }
    },
    computed: {
        customer: {
            get() {return this.model},
            set(value) {this.$emit('update:model', value)},
        },
        age() {
            if (!this.customer.birthday) {
                return '-';
            }

            return moment().diff(moment(this.customer.birthday, 'YYYY-MM-DD'), 'years');
        },
        showBtn() {
            return this.customer.manager_id !== this.form.manager_id ||
                this.customer.gender !== this.form.gender ||
                (this.customer.city || '') !== (this.form.city || '') ||
                (this.customer.comment_internal || '') !== (this.form.comment_internal || '') ||
                (this.customer.legal_info_company_name || '') !== (this.form.legal_info_company_name || '') ||
                (this.customer.legal_info_company_address || '') !== (this.form.legal_info_company_address || '') ||
                (this.customer.legal_info_inn || '') !== (this.form.legal_info_inn || '') ||
                (this.customer.legal_info_payment_account || '') !== (this.form.legal_info_payment_account || '') ||
                (this.customer.legal_info_bik || '') !== (this.form.legal_info_bik || '') ||
                (this.customer.legal_info_bank || '') !== (this.form.legal_info_bank || '') ||
                (this.customer.legal_info_bank_correspondent_account || '') !== (this.form.legal_info_bank_correspondent_account || '') ||
                (this.customer.legal_info_bank_city || '') !== (this.form.legal_info_bank_city || '') ||
                (this.customer.passport.surname || '') !== (this.form.pdr_lastName || '') ||
                (this.customer.passport.name || '') !== (this.form.pdr_firstName || '') ||
                (this.customer.passport.patronymic || '') !== (this.form.pdr_middleName || '') ||
                (this.customer.passport.no || '') !== (this.form.pdr_docNumber || '') ||
                (this.customer.passport.serial || '') !== (this.form.pdr_docSerial || '') ||
                (this.customer.passport.issue_date || '') !== (this.form.pdr_docIssueDate || '') ||
                (this.customer.referral_code || '') !== (this.form.referral_code || '') ||
                (this.customer.referral_contract_number || '') !== (this.form.referral_contract_number || '') ||
                (this.customer.referral_contract_at || '') !== (this.form.referral_contract_at || '') ||
                JSON.stringify(this.savedActivities) !== JSON.stringify(this.form.activities) ||
                (this.customer.birthday || '') !== (this.form.birthday || '');
        },
    },
    methods: {
        save() {
            Services.showLoader();
            Services.net().put(this.getRoute('customers.detail.save', {id: this.customer.id}), {}, {
                customer: {
                    comment_internal: this.form.comment_internal,
                    manager_id: this.form.manager_id,
                    gender: this.form.gender,
                    city: this.form.city,
                    birthday: this.form.birthday,
                    legal_info_company_name: this.form.legal_info_company_name,
                    legal_info_company_address: this.form.legal_info_company_address,
                    legal_info_inn: this.form.legal_info_inn,
                    legal_info_payment_account: this.form.legal_info_payment_account,
                    legal_info_bik: this.form.legal_info_bik,
                    legal_info_bank: this.form.legal_info_bank,
                    legal_info_bank_correspondent_account: this.form.legal_info_bank_correspondent_account,
                    legal_info_bank_city: this.form.legal_info_bank_city,
                    referral_code: this.form.referral_code,
                    referral_contract_number: this.form.referral_contract_number,
                    referral_contract_at: this.form.referral_contract_at,
                    passport: {
                        surname: this.form.pdr_lastName,
                        name: this.form.pdr_firstName,
                        patronymic: this.form.pdr_middleName,
                        no: this.form.pdr_docNumber,
                        serial: this.form.pdr_docSerial,
                        issue_date: this.form.pdr_docIssueDate,
                        address: this.form.legal_info_company_address
                    }
                },
                activities: this.form.activities,
            }).then(data => {
                this.customer.comment_internal = this.form.comment_internal;
                this.customer.manager_id = this.form.manager_id;
                this.customer.gender = this.form.gender;
                this.customer.city = this.form.city;
                this.customer.birthday = this.form.birthday;
                this.customer.legal_info_company_name = this.form.legal_info_company_name;
                this.customer.legal_info_company_address = this.form.legal_info_company_address;
                this.customer.legal_info_inn = this.form.legal_info_inn;
                this.customer.legal_info_payment_account = this.form.legal_info_payment_account;
                this.customer.legal_info_bik = this.form.legal_info_bik;
                this.customer.legal_info_bank = this.form.legal_info_bank;
                this.customer.legal_info_bank_correspondent_account = this.form.legal_info_bank_correspondent_account;
                this.customer.legal_info_bank_city = this.form.legal_info_bank_city;
                this.customer.referral_code = this.form.referral_code;
                this.customer.referral_contract_number = this.form.referral_contract_number;
                this.customer.referral_contract_at = this.form.referral_contract_at;
                this.savedActivities = this.form.activities;
                this.customer.passport.surname = this.form.pdr_lastName;
                this.customer.passport.name = this.form.pdr_firstName;
                this.customer.passport.patronymic = this.form.pdr_lastName;
                this.customer.passport.serial = this.form.pdr_docSerial;
                this.customer.passport.no = this.form.pdr_docNumber;
                this.customer.passport.issue_date = this.form.pdr_docIssueDate;
                this.customer.passport.address = this.form.legal_info_company_address;
                Services.msg("Изменения сохранены");
            }).finally(() => {
                Services.hideLoader();
            })
        },
        cancel() {
            this.form.comment_internal = this.customer.comment_internal;
            this.form.manager_id = this.customer.manager_id;
            this.form.gender = this.customer.gender;
            this.form.city = this.customer.city;
            this.form.birthday = this.customer.birthday;
            this.form.legal_info_company_name = this.customer.legal_info_company_name;
            this.form.legal_info_company_address = this.customer.legal_info_company_address;
            this.form.legal_info_inn = this.customer.legal_info_inn;
            this.form.legal_info_payment_account = this.customer.legal_info_payment_account;
            this.form.legal_info_bik = this.customer.legal_info_bik;
            this.form.legal_info_bank = this.customer.legal_info_bank;
            this.form.legal_info_bank_correspondent_account = this.customer.legal_info_bank_correspondent_account;
            this.form.legal_info_bank_city = this.customer.legal_info_bank_city;
            this.form.referral_code = this.customer.referral_code;
            this.form.referral_contract_number = this.customer.referral_contract_number;
            this.form.referral_contract_at = this.customer.referral_contract_at;
            this.form.activities = this.savedActivities;
            this.form.pdr_lastName = this.customer.passport.surname;
            this.form.pdr_firstName = this.customer.passport.name;
            this.form.pdr_middleName = this.customer.passport.patronymic;
            this.form.pdr_docSerial = this.customer.passport.serial;
            this.form.pdr_docNumber = this.customer.passport.no;
            this.form.pdr_docIssueDate = this.customer.passport.issue_date;
        },
        deleteCertificate(certificate_id, index) {
            Services.showLoader();
            Services.net().delete(this.getRoute('customers.detail.main.certificate.delete', {
                id: this.customer.id,
                certificate_id: certificate_id
            })).then(data => {
                this.$delete(this.certificates, index);
                Services.msg("Изменения сохранены");
            }).finally(() => {
                Services.hideLoader();
            })
        },
        createCertificate() {
            Services.showLoader();
            Services.net().post(this.getRoute('customers.detail.main.certificate.create', {
                id: this.customer.id,
                file_id: this.form.file.id,
            })).then(data => {
                this.$set(this.certificates, this.certificates.length, {
                    id: data.id,
                    name: this.form.file.name,
                    url: this.form.file.url,
                });
                this.form.file = null;
                Services.msg("Изменения сохранены");
            }).finally(() => {
                Services.hideLoader();
            })
        },
        deleteReferralContract(contract_id, index) {
            Services.showLoader();
            Services.net().delete(this.getRoute('customers.detail.main.referralContract.delete', {
                id: this.customer.id,
                referral_contract_id: contract_id
            })).then(data => {
                this.$delete(this.referralContracts, index);
                Services.msg("Изменения сохранены");
            }).finally(() => {
                Services.hideLoader();
            })
        },
        createReferralContract() {
            Services.showLoader();
            Services.net().post(this.getRoute('customers.detail.main.referralContract.create', {
                id: this.customer.id,
                file_id: this.form.file.id,
            })).then(data => {
                this.$set(this.referralContracts, this.referralContracts.length, {
                    id: data.id,
                    name: this.form.file.name,
                    url: this.form.file.url,
                });
                this.form.file = null;
                Services.msg("Изменения сохранены");
            }).finally(() => {
                Services.hideLoader();
            })
        },
        openOrder() {
            Services.event().$emit('showTab', 'order');
        }
    },
    created() {
        Services.showLoader();
        Services.net().get(this.getRoute('customers.detail.main', {id: this.model.id}), {
            user_id: this.model.user_id,
            isReferral: this.model.referral,
        }).then(data => {
            this.managers = data.managers;
            this.certificates = data.certificates;
            this.referralContracts = data.referralContracts;
            this.activitiesAll = data.activitiesAll;
            this.form.activities = data.activities;
            this.savedActivities = data.activities;
            this.personalDiscount = data.personalDiscount;
        }).finally(() => {
            Services.hideLoader();
        })
    }
};
</script>

<style scoped>
.w-100 {

}
</style>
