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
            <td><input type="date" v-model="form.birthday" class="form-control form-control-sm"/></td>
        </tr>
        <tr>
            <th>Возраст</th>
            <td>{{ age }}</td>
        </tr>
        <tr>
            <th>Город</th>
            <td></td>
        </tr>
        <tr>
            <th>Сумма покупок накопительным итогом</th>
            <td><a tabindex @click="openOrder">{{ order.price }}</a></td>
        </tr>
        <tr>
            <th>Подписка на рассылку</th>
            <td></td>
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
                    <file-input v-if="!form.file" @uploaded="(data) => form.file = data" class="mb-3"></file-input>
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
        </tbody>
    </table>
</template>

<script>
import { mapGetters } from 'vuex';
import Services from '../../../../../scripts/services/services.js';
import moment from 'moment';
import VDeleteButton from '../../../../components/controls/VDeleteButton/VDeleteButton.vue';
import FileInput from '../../../../components/controls/FileInput/FileInput.vue';

export default {
    name: 'info-main',
    components: {FileInput, VDeleteButton},
    props: ['model', 'order'],
    data() {
        return {
            certificates: [],
            managers: [],
            activitiesAll: [],
            savedActivities: [],

            form: {
                comment_internal: this.model.comment_internal,
                manager_id: this.model.manager_id,
                gender: this.model.gender,
                birthday: this.model.birthday,
                activities: [],
                file: null
            }
        }
    },
    computed: {
        ...mapGetters(['getRoute']),
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
                (this.customer.comment_internal || '') !== (this.form.comment_internal || '') ||
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
                    birthday: this.form.birthday,
                },
                activities: this.form.activities,
            }).then(data => {
                this.customer.comment_internal = this.form.comment_internal;
                this.customer.manager_id = this.form.manager_id;
                this.customer.gender = this.form.gender;
                this.customer.birthday = this.form.birthday;
                this.savedActivities = this.form.activities;
                Services.hideLoader();
                Services.msg("Изменения сохранены");
            })
        },
        cancel() {
            this.form.comment_internal = this.customer.comment_internal;
            this.form.manager_id = this.customer.manager_id;
            this.form.gender = this.customer.gender;
            this.form.birthday = this.customer.birthday;
            this.form.activities = this.savedActivities;
        },
        deleteCertificate(certificate_id, index) {
            Services.showLoader();
            Services.net().delete(this.getRoute('customers.detail.certificate.delete', {
                id: this.customer.id,
                certificate_id: certificate_id
            })).then(data => {
                this.$delete(this.certificates, index);
                Services.hideLoader();
                Services.msg("Изменения сохранены");
            })
        },
        createCertificate() {
            Services.showLoader();
            Services.net().post(this.getRoute('customers.detail.certificate.create', {
                id: this.customer.id,
                file_id: this.form.file.id,
            })).then(data => {
                this.$set(this.certificates, this.certificates.length, {
                    id: data.id,
                    name: this.form.file.name,
                    url: this.form.file.url,
                });
                this.form.file = null;
                Services.hideLoader();
                Services.msg("Изменения сохранены");
            })
        },
        openOrder() {
            Services.event().$emit('showTab', 'order');
        }
    },
    created() {
        Services.showLoader();
        Services.net().get(this.getRoute('customers.detail.main', {id: this.model.id}), {user_id: this.model.user_id}).then(data => {
            this.managers = data.managers;
            this.certificates = data.certificates;
            this.activitiesAll = data.activitiesAll;
            this.form.activities = data.activities;
            this.savedActivities = data.activities;
            Services.hideLoader();
        })
    }
};
</script>

<style scoped>

</style>