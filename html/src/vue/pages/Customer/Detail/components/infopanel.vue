<template>
    <table class="table table-sm">
        <thead>
        <tr>
            <th colspan="5">
                Инфопанель
                <button v-if="canUpdate(blocks.clients)" class="btn btn-success btn-sm" @click="saveCustomer" :disabled="!showBtn">
                    Сохранить
                </button>
                <button @click="cancel" v-if="canUpdate(blocks.clients)" class="btn btn-outline-danger btn-sm" :disabled="!showBtn">Отмена</button>

                <button v-if="canUpdate(blocks.clients)" @click="makeDial" class="btn btn-info btn-sm">Позвонить</button>
                <button v-if="canUpdate(blocks.clients)" @click="authByUser" class="btn btn-warning btn-sm">Войти под клиентом</button>
                <b-dropdown text="Изменить статус" class="float-right" size="sm" v-if="canUpdate(blocks.clients)">
                    <template v-if="!customer.referral">
                        <b-dropdown-item-button v-if="customer.status != customerStatus.created " @click="openModal('modal-mark-status-created')">
                            Создан профиль
                        </b-dropdown-item-button>
                        <b-dropdown-item-button v-if="customer.status != customerStatus.new"  @click="openModal('modal-mark-status-new')">
                            Новый
                        </b-dropdown-item-button>
                        <b-dropdown-item-button v-if="customer.status != customerStatus.consideration" @click="openModal('modal-mark-status-consideration')">
                            На рассмотрении
                        </b-dropdown-item-button>
                        <b-dropdown-item-button v-if="customer.status != customerStatus.rejected" @click="openModal('modal-mark-status-rejected')">
                            Отклонен
                        </b-dropdown-item-button>
                        <b-dropdown-item-button v-if="customer.status != customerStatus.active" @click="openModal('modal-mark-status-active')">
                            Активный
                        </b-dropdown-item-button>
                        <b-dropdown-item-button v-if="customer.status != customerStatus.problem" @click="openModal('modal-mark-status-problem')">
                            Пометить проблемным
                        </b-dropdown-item-button>
                        <b-dropdown-item-button v-if="customer.status != customerStatus.potential_rp" @click="openModal('modal-mark-status-potential_rp')">
                            Потенциальный реферальный партнер
                        </b-dropdown-item-button>
                        <b-dropdown-item-button v-if="customer.status == customerStatus.potential_rp" @click="makeReferral">
                            Сделать реферальным партнером
                        </b-dropdown-item-button>
                    </template>
                    <b-dropdown-item-button v-if="customer.status == customerStatus.block || customer.status == customerStatus.temporarily_suspended" @click="openModal('modal-mark-status-active')">
                        Активировать
                    </b-dropdown-item-button>
                    <b-dropdown-item-button v-if="customer.status != customerStatus.block" @click="openModal('modal-mark-status-block')">
                        Заблокировать
                    </b-dropdown-item-button>
                    <template v-if="customer.referral">
                        <b-dropdown-item-button @click="makeProfessional">
                            Сделать профессионалом
                        </b-dropdown-item-button>
                        <b-dropdown-item-button v-if="customer.status != customerStatus.temporarily_suspended" @click="openModal('modal-mark-status-temporarily-suspended')">
                            Приостановить сотрудничество
                        </b-dropdown-item-button>
                    </template>
                </b-dropdown>
            </th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <th>Роль</th>
            <td colspan="3">
                {{ customer.referral ? 'Реферальный Партнер' : 'Профессионал' }}
                <!--{{ customer.role_date ? `(${formatDate(customer.role_date)})` : '' }}-->
                <span v-if="customer.referrer">
                    (РП: <a :href="getRoute('customers.detail', {id: customer.referrer.id})">{{customer.referrer.title}}</a>)
                </span>
            </td>
        </tr>
        <tr v-if="customer.referral">
            <th>Уровень</th>
            <td colspan="3">
                <div class="input-group input-group-sm">
                    <select class="form-control form-control-sm" v-model="form.referral_level_id">
                        <option v-for="referralLevel in referralLevels" :value="referralLevel.id">
                            {{ referralLevel.name }}
                        </option>
                    </select>
                    <div class="input-group-append" v-if="customer.commission_route">
                        <a class="btn btn-outline-info" :href="customer.commission_route">
                            <fa-icon icon="percent"/>
                        </a>
                    </div>
                </div>
            </td>
        </tr>
        <tr>
            <th>Статус</th>
            <td>
                <div class="input-group input-grou  p-sm">
                            {{ customerStatusName[customer.status] }}
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
                    <file-input v-if="!form.avatar"
                                @uploaded="(data) => form.avatar = data.id"
                                size="sm"
                                destination='avatar'
                    />
                </template>
                <template v-else>
                    <a :href="media.file(form.avatar)" target="_blank">Посмотреть</a>
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
                <div v-for="(portfolio, i) in customer.portfolios">
                    <a :href="portfolio.link" target="_blank">
                        {{ portfolio.name }}
                    </a>
                    <b-button v-if="portfolio.duplicated_customer_id"
                              :id="'duplicated-portfolio-tooltip-' + i"
                              class="btn btn-warning btn-sm"
                    >
                        <fa-icon icon="exclamation"/>
                    </b-button>
                    <b-tooltip
                        :target="'duplicated-portfolio-tooltip-' + i"
                        triggers="hover"
                    >
                        <span v-html="portfolioDuplicatedMessage(portfolio.duplicated_customer_id)"></span>
                    </b-tooltip>
                </div>
                <div v-if="!customer.portfolios.length">-</div>
                <button v-if="canUpdate(blocks.clients)" class="btn btn-info btn-sm" v-b-modal.modal-portfolios><fa-icon icon="pencil-alt"/></button>
            </td>
        </tr>
        </tbody>
    </table>
</template>

<script>
    import Services from '../../../../../scripts/services/services.js';
    import FileInput from '../../../../components/controls/FileInput/FileInput.vue';
    import VDeleteButton from '../../../../components/controls/VDeleteButton/VDeleteButton.vue';
    import moment from 'moment';

    export default {
    name: 'infopanel',
    components: {VDeleteButton, FileInput},
    props: ['model', 'referralLevels'],
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
                referral_level_id: this.model.referral_level_id,
            }
        };
    },
    watch: {
        'model.status': function (val, oldVal) {
            this.form.status = this.model.status;
        }
    },
    computed: {
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
                (this.customer.referral_level_id || '') !== (this.form.referral_level_id || '') ||
                (this.customer.avatar !== this.form.avatar);
        },
    },
    methods: {
        saveCustomer() {
            Services.showLoader();
            Services.net().put(this.getRoute('customers.detail.save', {id: this.customer.id}), {}, {
                customer: {
                    status: this.form.status,
                    comment_status: "",
                    avatar: this.form.avatar,
                    referral_level_id: this.form.referral_level_id,
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
                this.customer.referral_level_id = this.form.referral_level_id;
                this.customer.comment_status = "";
                Services.msg("Изменения сохранены");
            }).finally(data => {
                Services.hideLoader();
            })
        },
        makeDial() {
            Services.showLoader();
            Services.net().post(this.getRoute('customers.detail.dial', {id: this.customer.id}), null, {provider: 'check_professional'})
                .then(data => {
                    Services.msg("Запрос на звонок отправлен");
                }).finally(data => {
                    Services.hideLoader();
                })
        },
        authByUser() {
            Services.showLoader();
            Services.net().post(this.getRoute('customers.detail.auth', {id: this.customer.user_id}), null, )
                .then(data => {
                    if (data.url) {
                        window.open(data.url);
                        Services.msg("Авторизация выполнена, витрина откроется в новом окне");
                    } else {
                        Services.msg('Ошибка при авторизации');
                    }
                }).finally(data => {
                Services.hideLoader();
            })
        },
        makeReferral() {
            Services.showLoader();
            Services.net().put(this.getRoute('customers.detail.referral', {id: this.customer.id})).then(data => {
                this.customer.status = this.customerStatus.active;
                this.customer.referral = true;
                this.customer.referral_level_id = data.defaultLevel;
                this.form.referral_level_id = data.defaultLevel;
                //this.customer.role_date = moment().format('YYYY-MM-DD HH:mm:ss');
                Services.msg("Изменения сохранены");
            }).finally(data => {
                Services.hideLoader();
            })
        },
        makeProfessional() {
            Services.showLoader();
            Services.net().put(this.getRoute('customers.detail.professional', {id: this.customer.id})).then(data => {
                this.customer.status = this.customerStatus.active;
                this.customer.referral = false;
                this.customer.referral_level_id = null;
                this.form.referral_level_id = null;
                //this.customer.role_date = moment().format('YYYY-MM-DD HH:mm:ss');
                Services.msg("Изменения сохранены");
            }).finally(data => {
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
            this.form.referral_level_id = this.customer.referral_level_id;
        },
        disableStatus(status_id) {
            return Number(status_id) === Number(this.customerStatus.problem) ||
                Number(status_id) === Number(this.customerStatus.temporarily_suspended) ||
                Number(status_id) === Number(this.customerStatus.block);
        },
        openModal(id) {
            this.$bvModal.show(id);
        },
        portfolioDuplicatedMessage(duplicated_customer_id) {
            let customerRoute = this.getRoute('customers.detail', {id: duplicated_customer_id});
            let customerLink = `<a href="${customerRoute}" target="_blank">#${duplicated_customer_id}</a>`;

            if (this.customer.status === this.customerStatus.active) {
                return `Ссылка совпадает с портфолио другого клиента ${customerLink}`;
            }

            return `Проф.статус не подтвержден автоматически из-за совпадения с другим клиентом ${customerLink}`;
        },

        formatDate(str) {
            return moment(str).format('DD.MM.YYYY');
        }
    }
};
</script>

<style scoped>

</style>
