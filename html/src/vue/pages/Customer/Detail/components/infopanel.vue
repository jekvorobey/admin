<template>
    <table class="table table-sm">
        <thead>
        <tr>
            <th colspan="4">
                Инфопанель
                <button class="btn btn-success btn-sm" @click="saveCustomer" :disabled="!showBtn">
                    Сохранить
                </button>
                <button @click="cancel" class="btn btn-outline-danger btn-sm" :disabled="!showBtn">Отмена</button>

                <b-dropdown text="Действия" class="float-right" size="sm">
                    <b-dropdown-item-button v-if="customer.status != customerStatus.problem && !customer.referral" v-b-modal.modal-mark-status-problem>
                        Пометить проблемным
                    </b-dropdown-item-button>
                    <b-dropdown-item-button v-if="customer.status == customerStatus.potential_rp && !customer.referral" @click="makeReferral">
                        Сделать реферальным партнером
                    </b-dropdown-item-button>
                    <b-dropdown-item-button v-if="customer.referral" @click="makeProfessional">
                        Сделать профессионалом
                    </b-dropdown-item-button>
                    <b-dropdown-item-button v-if="customer.status != customerStatus.temporarily_suspended && customer.referral" v-b-modal.modal-mark-status-temporarily-suspended>
                        Приостановить сотрудничество
                    </b-dropdown-item-button>
                    <b-dropdown-item-button v-if="customer.status != customerStatus.block" v-b-modal.modal-mark-status-block>
                        Заблокировать
                    </b-dropdown-item-button>
                </b-dropdown>
            </th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <th>Роль</th>
            <td colspan="3">
                {{ customer.referral ? 'Реферальный Партнер' : 'Профессионал' }}
                ({{ datetimePrint(customer.role_date) }})
            </td>
        </tr>
        <tr>
            <th>Статус</th>
            <td>
                <div class="input-group input-group-sm">
                    <select class="form-control form-control-sm" v-model="form.status">
                        <option
                            v-for="id in customerStatusByRole[customer.referral ? userRoles.showcase.referral_partner : userRoles.showcase.professional]"
                            :value="id"
                            :disabled="disableStatus(id)"
                        >
                            {{ customerStatusName[id] }}
                        </option>
                    </select>
                    <div class="input-group-append" v-if="customer.comment_status">
                        <button class="btn btn-outline-info" v-b-tooltip.hover :title="customer.comment_status">
                            <fa-icon icon="info"/>
                        </button>
                    </div>
                </div>
            </td>
            <th>Сегмент</th>
            <td></td>
        </tr>
        <tr>
            <th>ФИО</th>
            <td>
                <input class="form-control form-control-sm" v-model="form.last_name" placeholder="Фамилия"/>
            </td>
            <td>
                <input class="form-control form-control-sm" v-model="form.first_name" placeholder="Имя"/>
            </td>
            <td>
                <input class="form-control form-control-sm" v-model="form.middle_name" placeholder="Отчество"/>
            </td>
        </tr>
        <tr>
            <th>ID</th>
            <td>{{ customer.id }}</td>
            <th>Фото</th>
            <td>
                <template v-if="!form.avatar">
                    <file-input v-if="!form.avatar" @uploaded="(data) => form.avatar = data" size="sm"/>
                </template>
                <template v-else>
                    <a :href="form.avatar.url">Посмотреть</a>
                    <v-delete-button @delete="form.avatar = null" btn-class="btn-danger btn-sm"/>
                </template>
            </td>
        </tr>
        <tr>
            <th>E-mail</th>
            <td>
                <input class="form-control form-control-sm" v-model="form.email" :disabled="!user.isSuper"/>
            </td>
            <th>Телефон</th>
            <td>
                <input class="form-control form-control-sm" v-model="form.phone" :disabled="!user.isSuper"/>
            </td>
        </tr>
        <tr>
            <th>Соцсети</th>
            <td>
                <div v-for="social in customer.socials">
                    {{ social.driver }}: {{ social.name }}
                </div>
                <div v-if="!customer.socials.length">-</div>
            </td>
            <th>Ссылка на портфолио</th>
            <td>
                <div v-for="portfolio in customer.portfolios">
                    <a :href="portfolio.link" target="_blank">{{ portfolio.name }}</a>
                </div>
                <div v-if="!customer.portfolios.length">-</div>
                <button class="btn btn-info btn-sm" v-b-modal.modal-portfolios><fa-icon icon="pencil-alt"/></button>
            </td>
        </tr>
        </tbody>
    </table>
</template>

<script>
import Services from '../../../../../scripts/services/services.js';
import { mapGetters } from 'vuex';
import FileInput from '../../../../components/controls/FileInput/FileInput.vue';
import VDeleteButton from '../../../../components/controls/VDeleteButton/VDeleteButton.vue';
import moment from 'moment';

export default {
    name: 'infopanel',
    components: {VDeleteButton, FileInput},
    props: ['model'],
    data() {
        return {
            editStatus: false,
            form: {
                status: this.model.status,
                last_name: this.model.last_name,
                first_name: this.model.first_name,
                middle_name: this.model.middle_name,
                email: this.model.email,
                phone: this.model.phone,
                avatar: this.model.avatar,
            }
        };
    },
    watch: {
        'model.status': function (val, oldVal) {
            this.form.status = this.model.status;
        }
    },
    computed: {
        ...mapGetters(['getRoute']),
        customer: {
            get() {return this.model},
            set(value) {this.$emit('update:model', value)},
        },
        showBtn() {
            return this.customer.status !== this.form.status ||
                (this.customer.last_name || '') !== (this.form.last_name || '') ||
                (this.customer.first_name || '') !== (this.form.first_name || '') ||
                (this.customer.middle_name || '') !== (this.form.middle_name || '') ||
                (this.customer.email || '') !== (this.form.email || '') ||
                (this.customer.phone || '') !== (this.form.phone || '') ||
                (
                    (!this.customer.avatar && this.form.avatar) ||
                    (this.customer.avatar && !this.form.avatar) ||
                    (this.customer.avatar && this.form.avatar && this.customer.avatar.id !== this.form.avatar.id)
                );
        },
    },
    methods: {
        saveCustomer() {
            Services.showLoader();
            Services.net().put(this.getRoute('customers.detail.save', {id: this.customer.id}), {}, {
                customer: {
                    status: this.form.status,
                    comment_status: "",
                    avatar: this.form.avatar ? this.form.avatar.id : null,
                },
                user: {
                    id: this.customer.user_id,
                    last_name: this.form.last_name,
                    first_name: this.form.first_name,
                    middle_name: this.form.middle_name,
                    email: this.form.email,
                    phone: this.form.phone,
                }
            }).then(data => {
                this.customer.status = this.form.status;
                this.customer.last_name = this.form.last_name;
                this.customer.first_name = this.form.first_name;
                this.customer.middle_name = this.form.middle_name;
                this.customer.email = this.form.email;
                this.customer.phone = this.form.phone;
                this.customer.avatar = this.form.avatar;
                this.customer.comment_status = "";
                Services.msg("Изменения сохранены");
            }).catch(errorMsg => {
                Services.msg(errorMsg || "Ошибка сохранения", 'danger');
            }).then(data => {
                Services.hideLoader();
            })
        },
        makeReferral() {
            Services.showLoader();
            Services.net().put(this.getRoute('customers.detail.referral', {id: this.customer.id, user_id: this.customer.user_id})).then(data => {
                this.customer.status = this.customerStatus.active;
                this.customer.referral = true;
                this.customer.role_date = moment().format('YYYY-MM-DD HH:mm:ss');
                Services.msg("Изменения сохранены");
            }).catch(errorMsg => {
                Services.msg(errorMsg || "Ошибка сохранения", 'danger');
            }).then(data => {
                Services.hideLoader();
            })
        },
        makeProfessional() {
            Services.showLoader();
            Services.net().put(this.getRoute('customers.detail.professional', {id: this.customer.id, user_id: this.customer.user_id})).then(data => {
                this.customer.status = this.customerStatus.active;
                this.customer.referral = false;
                this.customer.role_date = moment().format('YYYY-MM-DD HH:mm:ss');
                Services.msg("Изменения сохранены");
            }).catch(errorMsg => {
                Services.msg(errorMsg || "Ошибка сохранения", 'danger');
            }).then(data => {
                Services.hideLoader();
            })
        },
        cancel() {
            this.form.status = this.customer.status;
            this.form.last_name = this.customer.last_name;
            this.form.first_name = this.customer.first_name;
            this.form.middle_name = this.customer.middle_name;
            this.form.email = this.customer.email;
            this.form.phone = this.customer.phone;
            this.form.avatar = this.customer.avatar;
        },
        disableStatus(status_id) {
            return Number(status_id) === Number(this.customerStatus.problem) ||
                Number(status_id) === Number(this.customerStatus.temporarily_suspended) ||
                Number(status_id) === Number(this.customerStatus.block);
        }
    }
};
</script>

<style scoped>

</style>