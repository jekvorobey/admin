<template>
    <layout-main>
        <b-card>
            <template v-slot:header>
                Фильтр
            </template>
            <div class="row">
                <f-input v-model="filter.id" class="col-sm-12 col-md-3 col-xl-2">
                    ID пользователя
                </f-input>
                <f-input v-model="filter.full_name" class="col-sm-12 col-md-3 col-xl-2">
                    ФИО
                </f-input>
                <f-input v-model="filter.email" class="col-sm-12 col-md-3 col-xl-2">
                    Email
                </f-input>
                <f-input v-model="filter.phone" class="col-sm-12 col-md-3 col-xl-2">
                    Телефон
                </f-input>
                <f-select v-model="filter.front" :options="frontOptions" class="col-sm-12 col-md-3 col-xl-2">
                    Система
                </f-select>
                <f-select v-model="filter.role" :options="roleOptions" class="col-sm-12 col-md-3 col-xl-2">
                    Роли
                </f-select>
            </div>
            <template v-slot:footer>
                <button @click="applyFilter" class="btn btn-sm btn-dark">Применить</button>
                <button @click="clearFilter" class="btn btn-sm btn-outline-dark">Очистить</button>
                <span class="float-right">Всего ролей: {{ pager.total }}. <span
                    v-if="selectedItems.length">Выбрано: {{ selectedItems.length }}</span></span>
            </template>
        </b-card>
        <div class="d-flex justify-content-between mt-3 mb-3" v-if="canUpdate(blocks.settings)">
            <button @click="openModal('userAdd')" class="btn btn-success">
                <fa-icon icon="plus"/>
                Добавить пользователя
            </button>
            <b-button v-if="usersSelected" class="btn btn-danger" @click="banUserArray">
                Заблокировать выбранных
            </b-button>
        </div>
        <table class="table">
            <thead>
            <tr>
                <th></th>
                <th>№</th>
                <th>ФИО</th>
                <th>Логин (Витрина)</th>
                <th>Логин (ADMIN/MAS)</th>
                <th>Система</th>
                <th>Роли</th>
                <th>E-mail подтверждён</th>
                <th v-if="canUpdate(blocks.settings)">Действия</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="user in users">
                <td>
                    <input type="checkbox" :checked="itemSelected(user.id)"
                           @change="e => selectItem(e, user.id)">
                </td>
                <td>{{ user.id }}</td>
                <td>
                    <a :href="getRoute('settings.userDetail', {id: user.id})">
                        {{ user.full_name }}
                    </a>
                </td>
                <td>{{ user.login }}</td>
                <td>{{ user.login_email }}</td>
                <td v-html="frontsName(user.fronts)"></td>
                <td v-html="rolesName(user.roles)"></td>
                <td><span class="badge"
                          :class="{'badge-success': user.email_verified, 'badge-danger': !user.email_verified}">{{
                        user.email_verified ? 'Да' : 'Нет'
                    }}</span></td>
                <td v-if="canUpdate(blocks.settings)">
                    <b-button class="btn btn-sm"
                              :class="{'btn-danger': !user.banned, 'btn-success': user.banned}"
                              @click="!user.banned ? banUser(user.id) : unBanUser(user.id)">
                        {{!user.banned ? 'Заблокировать' : 'Разблокировать' }}
                    </b-button>
                </td>
            </tr>
            </tbody>
        </table>
        <div>
            <b-pagination
                v-if="pager.pages !== 1"
                v-model="currentPage"
                :total-rows="pager.total"
                :per-page="pager.pageSize"
                @change="changePage"
                :hide-goto-end-buttons="pager.pages < 10"
                class="mt-3 float-right"
            ></b-pagination>
        </div>
        <user-add-modal :fronts="options.fronts" :roles="options.roles" :merchants="options.merchants" @onSave="onUserCreated"></user-add-modal>
    </layout-main>
</template>

<script>

import Services from '../../../../scripts/services/services';
import withQuery from 'with-query';

import UserAddModal from '../components/user-add-modal.vue';
import modalMixin from '../../../mixins/modal.js';
import FInput from '../../../components/filter/f-input.vue';
import FSelect from '../../../components/filter/f-select.vue';

const cleanFilter = {
    id: '',
    full_name: '',
    email: '',
    phone: '',
    front: '',
    role: '',
};

const serverKeys = [
    'id',
    'full_name',
    'email',
    'phone',
    'front',
    'role',
];

export default {
    mixins: [modalMixin],
    components: {
        UserAddModal,
        FInput,
        FSelect,
    },
    props: {
        iUsers: {},
        iPager: {},
        iCurrentPage: {},
        iFilter: {},
        options: {}
    },
    data() {
        let filter = Object.assign({}, cleanFilter, this.iFilter);
        return {
            users: this.iUsers,
            pager: this.iPager,
            currentPage: this.iCurrentPage || 1,
            appliedFilter: {},
            filter,
            selectedItems: []
        };
    },
    methods: {
        changePage(newPage) {
            history.pushState(null, null, location.origin + location.pathname + withQuery('', {
                page: newPage,
                filter: this.appliedFilter,
            }));
        },
        loadPage() {
            Services.showLoader();
            Services.net().get(this.route('settings.userListPagination'), {
                page: this.currentPage,
                filter: this.appliedFilter,
            }).then(data => {
                this.users = data.items;
                if (data.pager) {
                    this.pager = data.pager
                    this.total = data.pager.total
                }
            }).finally(() => {
                Services.hideLoader();
            });
        },
        reload() {
            location.reload();
        },
        applyFilter() {
            let tmpFilter = {};
            for (let [key, value] of Object.entries(this.filter)) {
                if (value && serverKeys.indexOf(key) !== -1) {
                    tmpFilter[key] = value;
                }
            }
            this.appliedFilter = tmpFilter;
            this.currentPage = 1;
            this.changePage(1);
            this.loadPage();
        },
        clearFilter() {
            for (let entry of Object.entries(cleanFilter)) {
                this.filter[entry[0]] = JSON.parse(JSON.stringify(entry[1]));
            }
            this.applyFilter();
        },
        itemSelected(id) {
            return this.selectedItems.indexOf(id) !== -1;
        },
        selectItem(e, id) {
            if (e.target.checked) {
                this.selectedItems.push(id);
            } else {
                let index = this.selectedItems.indexOf(id);
                if (index !== -1) {
                    this.selectedItems.splice(index, 1);
                }
            }
        },
        frontsName(frontValues) {
            let fronts = Object.values(this.options.fronts)
                .filter(front => frontValues.includes(front.id))
                .map((front) => front.name);
            return fronts.length > 0 ? fronts.join('<br>') : 'N/A';
        },
        rolesName(roleValues) {
            let roles = this.options.roles
                .filter(role => roleValues.includes(role.id))
                .map((role) => role.name);
            return roles.length > 0 ? roles.join('<br>') : 'N/A';
        },
        onUserCreated(newData) {
            Object.assign(this.users, newData);
            this.showMessageBox({text: "Пользователь создан!"});
        },
        banUser(id) {
            Services.showLoader();
            Services.net().put(this.getRoute('user.banUser', {id: id}), {}, {}, {})
                .then(data => {
                    this.reload()
                    this.showMessageBox({text: 'Пользователь заблокирован'});
                }).finally(() => {
                Services.hideLoader();
            });
        },
        banUserArray() {
            Services.showLoader();
            Services.net().put(this.getRoute('settings.banArray'), {}, {ids: this.selectedItems}, {})
                .then(data => {
                    this.reload()
                    this.showMessageBox({text: 'Выбранные пользователи заблокированы'});
                }).finally(() => {
                Services.hideLoader();
            });
        },
        unBanUser(id) {
            Services.showLoader();
            Services.net().put(this.getRoute('user.unBanUser', {id: id}), {}, {}, {})
                .then(data => {
                    this.reload()
                    this.showMessageBox({text: 'Пользователь разблокирован'});
                }).finally(() => {
                Services.hideLoader();
            });
        },
    },
    computed: {
        roleOptions() {
            return Object.values(this.options.roles).map(role => ({
                value: role.id,
                text: role.name
            }));
        },
        frontOptions() {
            return Object.values(this.options.fronts).map(front => ({
                value: front.id,
                text: front.name
            }));
        },
        usersSelected() {
            return this.selectedItems.length > 0;
        },
    },
    created() {
        window.onpopstate = () => {
            let query = qs.parse(document.location.search.substr(1));
            if (query.page) {
                this.currentPage = query.page;
            }
        };
    },
    watch: {
        currentPage() {
            this.loadPage();
        }
    },
};
</script>
