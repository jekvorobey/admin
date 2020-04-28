<template>
    <div>
        <div class="card">
            <div class="card-header">
                Фильтр
            </div>
            <div class="card-body">
            </div>
            <div class="card-footer">
<!--                <button @click="applyFilter" class="btn btn-sm btn-dark">Применить</button>-->
<!--                <button @click="clearFilter" class="btn btn-sm btn-outline-dark">Очистить</button>-->
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-12 mt-3">
<!--                <a :href="getRoute('operator.create')" class="btn btn-success">Создать оператора</a>-->

<!--                <button class="btn btn-secondary" disabled v-if="countSelected !== 1">Редактировать оператора</button>-->
<!--                <a :href="getRoute('operator.edit', {id: selectedOperators[0].id})" class="btn btn-warning" v-else>Редактировать оператора</a>-->

<!--                <button class="btn btn-danger" :disabled="countSelected < 1" @click="deleteOperator()">Удалить-->
<!--                    <template v-if="countSelected <= 1">оператора</template>-->
<!--                    <template v-else>операторов</template>-->
<!--                </button>-->

<!--                <button class="btn btn-secondary" @click="createChat()">Написать</button>-->

<!--                <button class="btn btn-secondary" :disabled="countSelected < 1" @click="changeStatus()">Сменить роль-->
<!--                    <template v-if="countSelected <= 1">оператора</template>-->
<!--                    <template v-else>операторов</template>-->
<!--                </button>-->
            </div>
        </div>

        <table class="table table-condensed">
            <thead>
            <tr>
                <th>
<!--                    <input type="checkbox"-->
<!--                           id="select-all-page-shipments"-->
<!--                           v-model="selectAll"-->
<!--                           @click="changeSelectAll()"-->
<!--                    >-->
<!--                    <label for="select-all-page-shipments" class="mb-0">Все</label>-->
                </th>
                <th v-for="column in columns" v-if="column.isShown">{{column.name}}</th>
                <th>
<!--                    <button class="btn btn-light float-right" @click="showChangeColumns">-->
<!--                        <fa-icon icon="cog"></fa-icon>-->
<!--                    </button>-->
<!--                    <modal-columns :i-columns="editedShowColumns"></modal-columns>-->
                </th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="operator in operators">
                <td>
<!--                    <input type="checkbox"-->
<!--                           value="true"-->
<!--                           class="offer-select"-->
<!--                           v-model="checkboxes[offer.id]"-->
<!--                           :value="offer.id">-->
                </td>
                <td v-for="column in columns" v-if="column.isShown" v-html="column.value(operator)"></td>
            </tr>
            <tr v-if="!operators.length">
                <td :colspan="columns.length + 1">Операторы отсутствуют</td>
            </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
    import Services from '../../../../../scripts/services/services.js';

    const cleanHiddenFilter = {
        is_receive_sms: null,
        roles: [],
        is_main: null,
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
        'is_receive_sms',
        'roles',
        'is_main',
    ];

    export default {
        name: 'tab-operator',
        props: ['id'],
        data() {
            return {
                operators: [],
                columns: [
                    {
                        name: 'ID пользователя',
                        code: 'user_id',
                        value: function(operator) {
                            return operator.user_id;
                        },
                        isShown: true,
                        isAlwaysShown: true,
                    },
                    {
                        name: 'ФИО оператора',
                        code: 'full_name',
                        value: function(operator) {
                            return operator.full_name;
                        },
                        isShown: true,
                        isAlwaysShown: true,
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
                        name: 'Предпочтительный способ связи (Получает СМС)',
                        code: 'is_receive_sms',
                        value: function(operator) {
                            return operator.is_receive_sms ? 'Да' : 'Нет';
                        },
                        isShown: true,
                        isAlwaysShown: false,
                    },
                    {
                        name: 'Роли оператора',
                        code: 'roles',
                        value: function(operator) {
                            let text = '';
                            // operator.roles.forEach((role) => text += role);
                            return text;
                        },
                        isShown: true,
                        isAlwaysShown: false,
                    },
                    {
                        name: 'Статус оператора',
                        code: 'is_main',
                        value: function(operator) {
                            return operator.is_main ? 'Администратор' : 'Оператор';
                        },
                        isShown: true,
                        isAlwaysShown: false,
                    },
                    {
                        name: 'Логин в MAS',
                        code: 'login',
                        value: function(operator) {
                            return operator.login;
                        },
                        isShown: true,
                        isAlwaysShown: true,
                    },
                ]
            }
        },
        created() {
            Services.showLoader();
            Services.net().get(this.getRoute('merchant.detail.operator', {id: this.id})).then(data => {
                this.operators = data.operators;
            }).finally(() => {
                Services.hideLoader();
            })
        }
    };
</script>

<style scoped>

</style>