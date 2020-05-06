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
                            <f-multi-select v-model="filter.role" :options="roleOptions" class="col-4">
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

        <div class="row mb-3">
            <div class="col-12 mt-3">
                <a :href="getRoute('merchant.operator.indexCreate') + '?merchant_id=' + id"
                   class="btn btn-success"
                >Создать менеджера</a>

                <button class="btn btn-secondary" disabled v-if="countSelected !== 1">Редактировать менеджера</button>
                <a :href="getRoute('merchant.operator.indexEdit', {id: selectedOperators[0].id})" class="btn btn-warning" v-else>Редактировать менеджера</a>

                <button class="btn btn-danger" :disabled="countSelected < 1" @click="deleteOperator()">
                    Удалить {{ pluralForm(countSelected, formsGenitive) }}
                </button>

<!--                <button class="btn btn-secondary" @click="createChat()">-->
<!--                    Написать {{ pluralForm(countSelected, formsDative) }}-->
<!--                </button>-->

<!--                <button class="btn btn-secondary" :disabled="countSelected < 1" @click="changeStatus()">-->
<!--                    Сменить роль {{ pluralForm(countSelected, formsGenitive) }}-->
<!--                </button>-->
            </div>
        </div>

        <table class="table table-condensed">
            <thead>
            <tr>
                <th>
                    <input type="checkbox"
                           id="select-all-page-shipments"
                           v-model="selectAll"
                           @click="changeSelectAll()"
                    >
                    <label for="select-all-page-shipments" class="mb-0">Все</label>
                </th>
                <th v-for="column in columns" v-if="column.isShown">{{column.name}}</th>
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

        <transition name="modal">
            <modal :close="closeModal" v-if="isModalOpen('DeleteOperator')">
                <div slot="header">
                    <b>Вы уверены, что хотите удалить {{pluralForm(selectedOperators.length, formsNextGenitive)}}
                        {{pluralForm(selectedOperators.length, formsGenitive)}}?</b>
                </div>
                <div slot="body">
                    <div v-for="operator in selectedOperators">#{{ operator.id }} {{ operator.full_name }}</div>
                    <button class="btn btn-danger mt-3" type="button" @click="approveDelete()">Удалить</button>
                </div>
            </modal>
        </transition>
    </div>
</template>

<script>
    import FInput from '../../../../components/filter/f-input.vue';
    import FMultiSelect from '../../../../components/filter/f-multi-select.vue';
    import VSelect from '../../../../components/controls/VSelect/VSelect.vue';

    import ModalColumns from "../../../../components/modal-columns/modal-columns.vue";
    import modal from '../../../../components/controls/modal/modal.vue';
    import modalMixin from "../../../../mixins/modal.js";

    import Services from '../../../../../scripts/services/services.js';

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
        "менеджера",
        "менеджеров",
        "менеджеров"
    ];

    const formsDativeConst  = [
        "менеджеру",
        "менеджерам",
        "менеджерам"
    ];

    const formsNextGenitiveConst  = [
        "следующего",
        "следующих",
        "следующих"
    ];

    export default {
        name: 'tab-operator',
        props: ['id'],
        components: {
            FInput,
            FMultiSelect,
            VSelect,
            modal,
            ModalColumns
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
                        value: function(operator) {
                            let text = '';
                            Object.values(operator.roles).forEach((role) => text = text + role + '<br>');
                            return text.slice(0, -4);
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
                this.itemsPromise(),
            ]).then(data => {
                this.communicationMethods = data[0].communication_methods;
                this.roles = data[0].roles;
                this.operators = data[1].operators;
            }).finally(() => {
                Services.hideLoader();
            });
        },
        methods: {
            itemsPromise() {
                return Services.net().get(
                    this.getRoute('merchant.detail.operator', {id: this.id}),
                    {filter: this.appliedFilter}
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
                this.load();
            },
            clearFilter() {
                for (let entry of Object.entries(cleanFilter)) {
                    this.filter[entry[0]] = JSON.parse(JSON.stringify(entry[1]));
                }
                this.applyFilter();
            },
            load() {
                Services.showLoader();
                this.itemsPromise().then(data => {
                    this.operators = data.operators;
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
                }).then(data => {
                    this.closeModal('DeleteOperator');
                    this.load();
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
            roleOptions() {
                let arr = [];
                for (let [roleId, roleName] of Object.entries(this.roles)) {
                    arr.push({
                        value: roleId,
                        text: roleName,
                    });
                }
                return arr;
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
                return this.operators.filter((operator) => {
                    return (operator.id in this.checkboxes) && this.checkboxes[operator.id];
                });
            },
            selectedIds() {
                return this.operators.filter(operator => {
                    return (operator.id in this.checkboxes) && this.checkboxes[operator.id];
                }).map(operator => operator.id);
            },
        },
    };
</script>

<style scoped>

</style>