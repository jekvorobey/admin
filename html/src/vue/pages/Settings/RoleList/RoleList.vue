<template>
    <layout-main>
        <b-card>
            <template v-slot:header>
                Фильтр
            </template>
            <div class="row">
                <f-input v-model="filter.name" class="col-sm-12 col-md-3 col-xl-2">
                    Название
                </f-input>
                <f-select v-model="filter.front_id" :options="frontOptions" class="col-sm-12 col-md-3 col-xl-2">
                    Система
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
            <button @click="openModal('roleAdd')" class="btn btn-sm btn-success">
                <fa-icon icon="plus"/>
                Добавить роль
            </button>
        </div>
        <table class="table">
            <thead>
            <tr>
                <th></th>
                <th>№</th>
                <th>Наименование</th>
                <th>Система</th>
                <th v-if="canUpdate(blocks.settings)">Действия</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="role in roles">
                <td>
                    <input type="checkbox" :checked="itemSelected(role.id)"
                           @change="e => selectItem(e, role.id)">
                </td>
                <td>{{ role.id }}</td>
                <td v-if="canView(blocks.settings)">
                    <a :href="getRoute('settings.roleDetail', {id: role.id})">{{ role.name }}</a>
                </td>
                <td v-else>{{ role.name }}</td>
                <td>{{ frontName(role.front) }}</td>
                <td v-if="canUpdate(blocks.settings)">
                    <v-delete-button
                            v-if="role.can_delete"
                            btnClass="btn btn-danger btn-sm"
                            @delete="deleteRole(role.id)"
                    />
                </td>
            </tr>
            </tbody>
        </table>
        <div>
            <b-pagination
                v-if="pager.pages > 1"
                v-model="currentPage"
                :total-rows="pager.total"
                :per-page="pager.pageSize"
                @change="changePage"
                :hide-goto-end-buttons="pager.pages < 10"
                class="mt-3 float-right"
            ></b-pagination>
        </div>
        <role-add-modal :fronts="options.fronts" @onSave="onRoleCreated"></role-add-modal>
    </layout-main>
</template>

<script>

import Services from '../../../../scripts/services/services';
import withQuery from 'with-query';
import VDeleteButton from "../../../components/controls/VDeleteButton/VDeleteButton.vue";
import RoleAddModal from '../components/role-add-modal.vue';
import modalMixin from '../../../mixins/modal.js';
import FInput from '../../../components/filter/f-input.vue';
import FSelect from '../../../components/filter/f-select.vue';
import {required} from "vuelidate/lib/validators";

const cleanFilter = {
    name: '',
    front_id: [],
};

const serverKeys = [
    'name',
    'front_id',
];

export default {
    mixins: [modalMixin],
    components: {
        RoleAddModal,
        FInput,
        FSelect,
        VDeleteButton
    },
    props: {
        iRoles: {},
        iPager: {},
        iCurrentPage: {},
        iFilter: {},
        options: {}
    },
    data() {
        let filter = Object.assign({}, cleanFilter, this.iFilter);
        return {
            roles: this.iRoles,
            pager: this.iPager,
            currentPage: this.iCurrentPage,
            appliedFilter: {},
            filter,
            form: {
                name: null,
                front_id: null,
            },
            selectedItems: []
        };
    },
    validations: {
        form: {
            name: {required},
            front_id: {required},
        }
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
            Services.net().get(this.route('settings.roleListPagination'), {
                page: this.currentPage,
                filter: this.appliedFilter,
            }).then(data => {
                this.roles = data.items;
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
        frontName(id) {
            let fronts = Object.values(this.options.fronts).filter(front => front.id === id);
            return fronts.length > 0 ? fronts[0].name : 'N/A';
        },
        onRoleCreated(newData) {
            this.roles = newData.iRoles;
            this.pager = newData.iPager;
            this.showMessageBox({text: "Роль создана!"});
        },
        deleteRole(id) {
            Services.net().delete(this.getRoute('settings.deleteRole', {id: id}), {}, {}, {})
                .then(data => {
                    this.roles = data.iRoles;
                    this.pager = data.iPager;
                    this.showMessageBox({text: 'Роль удалена'});
                });
        },
    },
    computed: {
        frontOptions() {
            return Object.values(this.options.fronts).map(front => ({
                value: front.id,
                text: front.name
            }));
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
