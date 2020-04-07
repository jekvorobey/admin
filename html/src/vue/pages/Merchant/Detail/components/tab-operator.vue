<template>
    <div>
        <div class="mt-3 mb-3">
            <button class="btn btn-sm btn-dark">
                <fa-icon icon="plus"></fa-icon>
                Добавить оператора
            </button>
        </div>
        <table class="table table-sm">
            <thead>
                <tr>
                    <th>ФИО</th>
                    <th>Телефон</th>
                    <th>Получает СМС</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <tr v-for="operator in operators">
                <td>{{ users[operator.user_id]  ? users[operator.user_id].full_name : ''}}</td>
                <td>{{ users[operator.user_id] ? users[operator.user_id].phone : '' }}</td>
                <td>{{ operator.is_receive_sms ? 'Да' : 'Нет' }}</td>
                <td>
                    <fa-icon icon="pencil-alt"></fa-icon>
                    <fa-icon icon="trash-alt"></fa-icon>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
import Services from '../../../../../scripts/services/services.js';

export default {
    name: 'tab-operator',
    props: ['id'],
    data() {
        return {
            operators: [],
            users: {},
        }
    },
    created() {
        Services.showLoader();
        Services.net().get(this.getRoute('merchant.detail.operator', {id: this.id})).then(data => {
            this.operators = data.operators;
            this.users = data.users;
        }).finally(() => {
            Services.hideLoader();
        })
    }
};
</script>

<style scoped>

</style>