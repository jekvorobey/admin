<template>
    <layout-main>
        <div class="card">
            <div class="card-body">
                <b-form @submit.prevent="applyFilters">
                    <b-row class="mb-2">
                        <b-col>
                            <label for="filter-status">Статус</label>
                            <b-form-select v-model="filter.status" id="filter-status">
                                <b-form-select-option :value="null">Все</b-form-select-option>
                                <b-form-select-option :value="id" v-for="(title, id) in statuses" :key="id">
                                    {{ title }}
                                </b-form-select-option>
                            </b-form-select>
                        </b-col>
                        <b-col>
                            <label for="filter-has_password">Пароль</label>
                            <b-form-select v-model="filter.has_password" id="filter-has_password">
                                <b-form-select-option :value="null">Не выбрано</b-form-select-option>
                                <b-form-select-option :value="item.value" v-for="(item, id) in passwords" :key="id">
                                    {{ item.title }}
                                </b-form-select-option>
                            </b-form-select>
                        </b-col>
                        <v-input v-model="filter.registered_by_user_id" type="number" class="col-md-4 col-12">Кем зарегистрирован</v-input>
<!--                        <b-col>-->
<!--                            <label for="filter-registered_by_user_id">Кем зарегистрирован</label>-->
<!--                            <b-form-select v-model="filter.registered_by_user_id" id="filter-registered_by_user_id">-->
<!--                                <b-form-select-option :value="null">Не выбрано</b-form-select-option>-->
<!--                                <b-form-select-option :value="item.id" v-for="(item, id) in registeringUsers" :key="id">-->
<!--                                    {{ item.full_name ? item.full_name + ' ' + item.id: item.id }}-->
<!--                                </b-form-select-option>-->
<!--                            </b-form-select>-->
<!--                        </b-col>-->
                        <b-col>
                            <v-input v-model="filter.phone" v-mask="telMask" validation="phone">Телефон</v-input>
                        </b-col>
                    </b-row>
                    <div class="row mb-2">
                        <v-input v-model="filter.full_name" validation="fio" class="col-md-4 col-12">ФИО</v-input>
                        <v-select v-model="filter.gender" :options="genders" class="col-md-4 col-12">Пол</v-select>


                        <f-date v-if="!filter.use_period"
                                v-model="filter.created_at"
                                @change="filter.created_between = []"
                                class="col-md-4 col-12">
                            <div class="custom-control custom-switch">
                                <input type="checkbox"
                                       v-model="filter.use_period"
                                       class="custom-control-input"
                                       id="created_at">
                                <label class="custom-control-label" for="created_at">Дата регистрации покупателя</label>
                            </div>
                        </f-date>
                        <f-date v-else
                                v-model="filter.created_between"
                                @change="filter.created_at = []"
                                class="col-md-4 col-12"
                                range confirm>
                            <div class="custom-control custom-switch">
                                <input type="checkbox"
                                       v-model="filter.use_period"
                                       class="custom-control-input"
                                       id="created_between">
                                <label class="custom-control-label" for="created_between">Период регистрации</label>
                            </div>
                        </f-date>
                    </div>
                    <div class="row mb-2">
                        <v-select v-if="roles" v-model="filter.role" :options="roles" class="col-md-4 col-12">Роль</v-select>
                    </div>

                    <b-button type="submit" variant="dark">Искать</b-button>
                    <b-button @click="cleanFilter" type="button" variant="light">Очистить поля</b-button>
                    <br>
                    <b-button type="button" variant="outline-dark" v-if="!isReferral && canUpdate(blocks.clients)" v-b-modal="modalIdCreateUser" class="btn mt-2">
                        Создать клиента
                    </b-button>
                    <b-button v-if="options && ( canUpdate(blocks.settings) || canUpdate(blocks.users) )" @click="openModal('userAdd')" class="btn btn-success mt-2">
                        Добавить пользователя
                    </b-button>
                </b-form>
            </div>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>ID клиента</th>
                    <th>Дата регистарции</th>
                    <th>ФИО</th>
                    <th>Телефон</th>
                    <th>Email</th>
                    <th>Сегмент RFM</th>
                    <th>Дата последнего посещения</th>
                    <th>Статус</th>
                    <th>Пароль</th>
                    <th>Кто регистрировал</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(user, index) in users">
                    <td><a :href="getRoute('customers.detail', {id: user.id})">{{ user.id }}</a></td>
                    <td>{{ datePrint(user.register_date) }}</td>
                    <td>{{ user.full_name || 'N/A' }}</td>
                    <td>{{ user.phone }}</td>
                    <td>{{ user.email || '-' }}</td>
                    <td>{{ user.segment || '-'  }}</td>
                    <td>{{ user.last_visit || '-'  }}</td>
                    <td>{{ statuses[user.status] }}</td>
                    <td>{{ user.has_password | userHasPassword }}</td>
                    <td>
                        <a :href="getRoute('settings.userDetail', {id: user.registered_by_user_id})" target="_blank">
                            {{ user.registered_by_user_id }}
                        </a>
                    </td>
                    <td></td>
                </tr>
            </tbody>
        </table>
        <b-pagination
                v-if="pager.count > pager.perPage"
                v-model="pager.page"
                :total-rows="pager.count"
                :per-page="pager.perPage"
                class="mt-3 float-right"
        />

        <modal-create-user v-if="!isReferral && canUpdate(blocks.clients)" :id="modalIdCreateUser"/>
        <user-add-modal v-if="options" :fronts="options.fronts" :roles="options.roles" :merchants="options.merchants"
                        @onSave="onUserCreated"></user-add-modal>
    </layout-main>
</template>

<script>

import VInput from '../../../components/controls/VInput/VInput.vue';
import VSelect from '../../../components/controls/VSelect/VSelect.vue';
import VDate from '../../../components/controls/VDate/VDate.vue';
import FDate from '../../../components/filter/f-date.vue';
import { telMask } from '../../../../scripts/mask.js';
import Services from '../../../../scripts/services/services.js';
import modalMixin from '../../../mixins/modal.js';
import ModalCreateUser from './components/modal-create-user.vue';
import UserAddModal from '../../Settings/components/user-add-modal.vue';

const defaultFilter = {
    status: null,
    registered_by_user_id: null,
    has_password: null,
    phone: '',
    full_name: '',
    gender: 0,
    created_between: [],
    created_at: null,
    use_period: false,
    role: 0,
};

export default {
    mixins: [modalMixin],
    components: {ModalCreateUser, VInput, VSelect, VDate, FDate, UserAddModal},
    props: ['statuses', 'perPage', 'isReferral', 'roles', 'registeringUsers', 'options'],
    data() {
        return {
            modalIdCreateUser: 'modalIdCreateUser',
            filter: {...defaultFilter},

            users: [],
            pager: {
                page: 1,
                count: 1,
                perPage: this.perPage,
            },
            genders: {
                0: 'Все',
                2: 'Мужской',
                1: 'Женский'
            },
            passwords: [
                {
                    title: 'Установлен',
                    value: 'yes'
                },
                {
                    title: 'Не установлен',
                    value: 'no'
                },
            ]
        };
    },
    watch: {
        'pager.page': 'fetchUsers'
    },
    computed: {
        telMask() {
            return telMask;
        },
    },
    methods: {
        fetchUsers() {
            let filter = {...this.filter};

            filter.isReferral = this.isReferral ? 1 : 0;
            filter.page = this.pager.page;
            Services.net().get(this.getRoute('customers.filter'), filter).then((data)=> {
                this.users = data.users;
                this.pager.count = data.count || 0;
            });
        },
        applyFilters() {
            if (this.pager.page === 1) {
                this.fetchUsers();
            }

            this.pager.page = 1;
            // fetchUsers called into watch 'pager.page'
        },
        cleanFilter() {
            this.filter = {...defaultFilter};
            this.applyFilters();
        },
        onUserCreated(newData) {
            Object.assign(this.users, newData);
            this.showMessageBox({text: "Пользователь создан!"});
        },
    },
    filters : {
        userHasPassword(value) {
            return value ? 'Установлен' : 'Не установлен'
        }
    },
    created() {
        this.fetchUsers();
    }
};
</script>
