<template>
    <div>
        <div class="card">
            <div class="card-header">
                Фильтр
                <button @click="toggleHiddenFilter" class="btn btn-sm btn-light float-right">
                    {{ opened ? 'Меньше' : 'Больше' }} фильтров
                    <fa-icon :icon="opened ? 'compress-arrows-alt' : 'expand-arrows-alt'"></fa-icon>
                </button>
            </div>
            <div class="card-body">
                <div class="row">
                    <f-input v-model="filter.user_id"  class="col-2">
                        ID пользователя
                    </f-input>
                    <f-input v-model="filter.full_name" class="col-5">
                        ФИО
                    </f-input>
                    <f-input v-model="filter.email" class="col-5">
                        Email
                    </f-input>
                </div>
                <div class="row">
                    <f-input v-model="filter.phone"  class="col-6">
                        Телефон
                    </f-input>
                    <f-input v-model="filter.login" class="col-6">
                        Логин
                    </f-input>
                </div>
                <transition name="slide">
                    <div v-if="opened" class="additional-filter pt-3 mt-3">
                        <div class="row">
                            <f-multi-select v-model="filter.communication_method" :options="communicationMethodOptions" class="col-4">
                                Способ связи
                            </f-multi-select>
                            <f-multi-select v-model="filter.role" :options="roles" class="col-4">
                                Роли
                            </f-multi-select>
                            <v-select v-model="filter.active" :options="activeOptions" class="col-4">
                                Статус
                            </v-select>
                        </div>
                    </div>
                </transition>
            </div>
            <div class="card-footer">
                <button @click="applyFilter" class="btn btn-sm btn-dark">Применить</button>
                <button @click="clearFilter" class="btn btn-sm btn-outline-dark">Очистить</button>
            </div>
        </div>

        <div class="row mb-3" v-if="canUpdate(blocks.merchants)">
            <div class="col-12 mt-3">
                <a :href="getRoute('merchant.operator.indexCreate') + '?merchant_id=' + id"
                   class="btn btn-success"
                >Создать оператора</a>

                <button class="btn btn-secondary" disabled v-if="countSelected !== 1">Редактировать оператора</button>
                <a :href="getRoute('merchant.operator.indexEdit', {id: selectedOperators[0].id})" class="btn btn-warning" v-else>Редактировать оператора</a>

                <button class="btn btn-danger" :disabled="countSelected < 1" @click="deleteOperator()">
                    Удалить {{ pluralForm(countSelected, formsGenitive) }}
                </button>

                <button class="btn btn-primary" @click="onShowModalCreate()">
                    Написать {{ pluralForm(countSelected, formsDative) }}
                </button>

                <button class="btn btn-info" :disabled="countSelected !== 1" @click="changeRolesOperator()">
                    Сменить роль {{ pluralForm(countSelected, formsGenitive) }}
                </button>
            </div>
        </div>

        <table class="table table-condensed">
            <thead>
            <tr>
                <th>
                    <input type="checkbox"
                           id="select-all-page-operators"
                           v-model="selectAll"
                           @click="changeSelectAll()"
                    >
                    <label for="select-all-page-operators" class="mb-0">Все</label>
                </th>
                <th v-for="column in columns" v-if="column.isShown">{{ column.name }}</th>
                <th>
                    <button class="btn btn-light float-right" @click="showChangeColumns">
                        <fa-icon icon="cog"></fa-icon>
                    </button>
                    <modal-columns :i-columns="editedShowColumns"></modal-columns>
                </th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="operator in operators">
                <td>
                    <input type="checkbox"
                           value="true"
                           class="operator-select"
                           v-model="checkboxes[operator.id]"
                           :value="operator.id">
                </td>
                <td v-for="column in columns" v-if="column.isShown" v-html="column.value(operator)"></td>
            </tr>
            <tr v-if="!operators.length">
                <td :colspan="columns.length + 1">Операторы отсутствуют</td>
            </tr>
            </tbody>
        </table>
        <b-pagination
                v-if="pager.pages > 1"
                v-model="currentPage"
                :total-rows="pager.total"
                :per-page="pager.pageSize"
                :hide-goto-end-buttons="pager.pages < 10"
                class="float-right"
        ></b-pagination>

        <transition name="modal">
            <modal :close="closeModal" v-if="isModalOpen('DeleteOperator')">
                <div slot="header">
                    <b>Вы уверены, что хотите удалить {{ pluralForm(countSelected, formsNextGenitive) }}
                        {{ pluralForm(countSelected, formsGenitive) }}?</b>
                </div>
                <div slot="body">
                    <div v-for="operator in selectedOperators">#{{ operator.id }} {{ operator.full_name }}</div>
                    <button class="btn btn-danger mt-3" type="button" @click="approveDelete()">Удалить</button>
                </div>
            </modal>
        </transition>

        <b-modal id="modal-create" title="Создание чата" hide-footer>
            <communication-chat-creator :usersProp="selectedOperators.map(operator => {return {'id': operator.user_id, 'email': operator.email}})"
                                        :userSendIds="selectedOperators.map(operator => operator.user_id)"
            />
        </b-modal>

        <transition name="modal">
            <modal :close="closeModal" v-if="isModalOpen('ChangeRolesOperator')">
                <div slot="header">
                    <b>Изменить роли оператора {{ selectedOperators[0].full_name }}</b>
                </div>
                <div slot="body">
                    <template v-for="role in roles">
                        <div class="form-check">
                            <input class="form-check-input"
                                   type="checkbox"
                                   :id="`role-${role.value}`"
                                   @change="e => rolesCheckbox(e, role.value)"
                                   :value="role.value"
                                   :checked="selectedOperators[0].roles.includes(role.value)"
                            >
                            <label class="form-check-label" :for="`role-${role.value}`">
                                {{ role.text }}
                            </label>
                        </div>
                    </template>
                    <button class="btn btn-info mt-3" type="button" @click="approveChangeRoles()">Сохранить изменения</button>
                </div>
            </modal>
        </transition>
    </div>
</template>

<script>
    import FInput from '../../../../components/filter/f-input.vue';
    import FMultiSelect from '../../../../components/filter/f-multi-select.vue';
    import VSelect from '../../../../components/controls/VSelect/VSelect.vue';
    import CommunicationChatCreator
        from "../../../../components/communication/communication-chat-creator/communication-chat-creator.vue";

    import ModalColumns from "../../../../components/modal-columns/modal-columns.vue";
    import modal from '../../../../components/controls/modal/modal.vue';
    import modalMixin from "../../../../mixins/modal.js";

    import Services from '../../../../../scripts/services/services.js';
    import Helpers from "../../../../../scripts/helpers.js";

    const cleanHiddenFilter = {
        communication_method: [],
        role: [],
        active: null,
    };

    const cleanFilter = Object.assign({
        user_id: null,
        full_name: '',
        email: '',
        phone: '',
        login: '',
    }, cleanHiddenFilter);

    const serverKeys = [
        'user_id',
        'full_name',
        'email',
        'phone',
        'login',
        'communication_method',
        'role',
        'active',
    ];

    const formsGenitiveConst  = [
        "оператора",
        "операторов",
        "операторов"
    ];

    const formsDativeConst  = [
        "оператору",
        "операторам",
        "операторам"
    ];

    const formsNextGenitiveConst  = [
        "следующего",
        "следующих",
        "следующих"
    ];

    const formsPrepositionalConst  = [
        "операторе",
        "операторах",
        "операторах"
    ];

    export default {
        name: 'tab-operator',
        props: ['id'],
        components: {
            FInput,
            FMultiSelect,
            VSelect,
            modal,
            ModalColumns,
            CommunicationChatCreator
        },
        mixins: [modalMixin],
        data() {
            let self = this;
            let filter = Object.assign({}, cleanFilter);
            filter.role = filter.role.map(value => parseInt(value));

            return {
                operators: [],
                opened: false,
                filter,
                appliedFilter: {},
                communicationMethods: [],
                roles: [],
                selectAll: false,
                checkboxes: {},
                formsGenitive: formsGenitiveConst,
                formsDative: formsDativeConst,
                formsNextGenitive: formsNextGenitiveConst,
                formsPrepositional: formsPrepositionalConst,
                rolesSelect: [],
                currentPage: 1,
                pager: {},
                columns: [
                    {
                        name: '#',
                        code: 'id',
                        value: function(operator) {
                            return operator.id;
                        },
                        isShown: true,
                        isAlwaysShown: true,
                    },
                    {
                        name: 'ID',
                        code: 'user_id',
                        value: function(operator) {
                            return operator.user_id;
                        },
                        isShown: true,
                        isAlwaysShown: true,
                    },
                    {
                        name: 'ФИО',
                        code: 'full_name',
                        value: function(operator) {
                            return operator.full_name;
                        },
                        isShown: true,
                        isAlwaysShown: true,
                    },
                    {
                        name: 'Должность',
                        code: 'position',
                        value: function(operator) {
                            return operator.position;
                        },
                        isShown: true,
                        isAlwaysShown: false,
                    },
                    {
                        name: 'Email',
                        code: 'email',
                        value: function(operator) {
                            return operator.email;
                        },
                        isShown: true,
                        isAlwaysShown: true,
                    },
                    {
                        name: 'Телефон',
                        code: 'phone',
                        value: function(operator) {
                            return operator.phone;
                        },
                        isShown: true,
                        isAlwaysShown: true,
                    },
                    {
                        name: 'Способ связи',
                        code: 'communication_method',
                        value: function(operator) {
                            return operator.communication_method;
                        },
                        isShown: true,
                        isAlwaysShown: false,
                    },
                    {
                        name: 'Роли',
                        code: 'roles',
                        value: (operator) => {
                            return this.roles
                                .filter(role => operator.roles.includes(role.value))
                                .map(role => role.text)
                                .join('<br>') || 'N/A';
                        },
                        isShown: true,
                        isAlwaysShown: false,
                    },
                    {
                        name: 'Статус',
                        code: 'active',
                        value: function(operator) {
                            return '<span class="badge ' + self.activeStatusClass(operator.active) + '">' +
                                ((operator.active) ? 'Активен' : 'Не активен') +
                                '</span>';
                        },
                        isShown: true,
                        isAlwaysShown: false,
                    },
                    {
                        name: 'Логин',
                        code: 'login',
                        value: function(operator) {
                            return operator.login;
                        },
                        isShown: true,
                        isAlwaysShown: true,
                    },
                ],
            }
        },
        created() {
            Services.showLoader();
            Promise.all([
                Services.net().get(
                    this.getRoute('merchant.detail.operator.data', {id: this.id})
                ),
                this.paginationPromise(),
            ]).then(data => {
                this.communicationMethods = data[0].communication_methods;
                this.roles = data[0].roles;
                this.operators = data[1].operators;
                this.pager = data[1].pager
            }).finally(() => {
                Services.hideLoader();
            });
            Services.event().$on('closeModalCreate', this.onCloseModalCreate);
        },
        methods: {
            paginationPromise() {
                return Services.net().get(
                    this.getRoute('merchant.detail.operator.pagination', {id: this.id}),
                    {
                        page: this.currentPage,
                        filter: this.appliedFilter,
                    }
                );
            },
            activeStatusClass(activeStatusId) {
                switch (activeStatusId) {
                    case 0: return 'badge-danger';
                    case 1: return 'badge-success';
                    default: return 'badge-danger';
                }
            },
            showChangeColumns() {
                this.openModal('list_columns');
            },
            toggleHiddenFilter() {
                this.opened = !this.opened;
                if (this.opened === false) {
                    for (let entry of Object.entries(cleanHiddenFilter)) {
                        this.filter[entry[0]] = JSON.parse(JSON.stringify(entry[1]));
                    }
                    this.applyFilter();
                }
            },
            applyFilter() {
                let tmpFilter = {};
                for (let [key, value] of Object.entries(this.filter)) {
                    if (value && serverKeys.indexOf(key) !== -1) {
                        tmpFilter[key] = value;
                    }
                }
                this.appliedFilter = tmpFilter;
                this.loadPage();
            },
            clearFilter() {
                for (let entry of Object.entries(cleanFilter)) {
                    this.filter[entry[0]] = JSON.parse(JSON.stringify(entry[1]));
                }
                this.applyFilter();
            },
            loadPage() {
                Services.showLoader();
                this.paginationPromise().then(data => {
                    this.operators = data.operators;
                    if (data.pager) {
                        this.pager = data.pager
                    }
                }).finally(() => {
                    Services.hideLoader();
                });
            },
            forEachOperator(callback) {
                for (let i in this.operators) {
                    callback(this.operators[i]['id']);
                }
            },
            changeSelectAll() {
                let newValue = !this.selectAll;
                let checkboxes = {};
                this.forEachOperator((operatorId) => {
                    checkboxes[operatorId] = newValue;
                });
                this.checkboxes = checkboxes;
            },
            deleteOperator() {
                this.openModal('DeleteOperator');
            },
            approveDelete() {
                Services.showLoader();
                Services.net().delete(this.route('merchant.operator.delete'), {
                    operator_ids: this.selectedIds,
                }).then(() => {
                    this.closeModal('DeleteOperator');
                    this.loadPage();
                    this.checkboxes = {};
                    Services.msg('Данные об ' + Helpers.plural_form(this.countSelected, this.formsPrepositional) +
                        ' успешно удалены.');
                }, () => {
                    Services.msg('Произошла ошибка при удалении данных об ' +
                        Helpers.plural_form(this.countSelected, this.formsPrepositional) + '.', 'danger');
                }).finally(() => {
                    Services.hideLoader();
                });
            },
            onShowModalCreate() {
                this.$bvModal.show('modal-create');
            },
            onCloseModalCreate() {
                this.$bvModal.hide('modal-create');
            },
            changeRolesOperator() {
                this.openModal('ChangeRolesOperator');
                this.rolesSelect = [...this.selectedOperators[0].roles];
            },
            rolesCheckbox(e, id) {
                id = parseInt(id);
                if (e.target.checked) {
                    this.rolesSelect.push(id);
                } else {
                    this.rolesSelect = this.rolesSelect.filter((roleId) => {
                        return roleId !== id;
                    });
                }
            },
            approveChangeRoles() {
                Services.showLoader();
                Services.net().put(this.route('merchant.operator.changeRoles'), {}, {
                    user_id: this.selectedOperators[0].user_id,
                    roles: this.rolesSelect,
                }).then(() => {
                    this.closeModal('ChangeRolesOperator');
                    this.loadPage();
                    Services.msg('Роли оператора успешно изменены.');
                }, () => {
                    Services.msg('Произошла ошибка при ролей оператора.', 'danger');
                }).finally(() => {
                    Services.hideLoader();
                });
            },
        },
        computed: {
            editedShowColumns() {
                return this.columns.filter(function(column) {
                    return !column.isAlwaysShown;
                })
            },
            communicationMethodOptions() {
                return Object.values(this.communicationMethods).map(method => ({
                    value: method.id,
                    text: method.name,
                }));
            },
            activeOptions() {
                return [
                    {
                        value: null,
                        text: 'Не выбран',
                    },
                    {
                        value: '0',
                        text: 'Не активен',
                    },
                    {
                        value: '1',
                        text: 'Активен',
                    },
                ];
            },
            countSelected() {
                return Object.values(this.checkboxes).reduce((acc, val) => { return acc + val; }, 0);
            },
            selectedOperators() {
                return this.operators.filter(operator => {
                    return (operator.id in this.checkboxes) && this.checkboxes[operator.id];
                });
            },
            selectedIds() {
                return this.operators.filter(operator => {
                    return (operator.id in this.checkboxes) && this.checkboxes[operator.id];
                }).map(operator => operator.id);
            },
        },
        watch: {
            currentPage() {
                this.loadPage();
            }
        }
    };
</script>

<style scoped>

</style>
