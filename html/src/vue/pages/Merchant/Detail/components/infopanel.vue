<template>
    <table class="table table-sm">
        <thead>
        <tr>
            <th colspan="4">
                Инфопанель
            </th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <th>Название организации</th>
            <td colspan="3">
                <input class="form-control form-control-sm" v-model="form.legal_name"/>
            </td>
        </tr>
        <tr>
            <th>Город</th>
            <td colspan="3">
                <input class="form-control form-control-sm" v-model="form.city"/>
            </td>
        </tr>
        <tr>
            <th>ФИО</th>
            <td>
                {{ merchant.main_operator.last_name }}
                {{ merchant.main_operator.first_name }}
                {{ merchant.main_operator.middle_name }}
            </td>
        </tr>
        <tr>
            <th>E-mail</th>
            <td>{{ merchant.main_operator.email }}</td>
            <th>Телефон</th>
            <td>{{ merchant.main_operator.phone }}</td>
        </tr>
        <tr>
            <th>Статус</th>
            <td>
                <select class="form-control form-control-sm" v-model="form.status">
                    <option v-for="status in statuses" :value="status.id">{{ status.name }}</option>
                </select>
            </td>
            <th>Дата получения статуса</th>
            <td>{{ merchant.status_at }}</td>
        </tr>
        <tr>
            <th>Рейтинг</th>
            <td>
                <select class="form-control form-control-sm" v-model="form.rating_id">
                    <option :value="null">Выбрать</option>
                    <option v-for="rating in ratings" :value="rating.id">{{ rating.name }}</option>
                </select>
            </td>
            <th>Менеджер</th>
            <td>
                <select class="form-control form-control-sm" v-model="form.manager_id">
                    <option :value="null">Выбрать</option>
                    <option v-for="manager in managers" :value="manager.id">{{ manager.name }}</option>
                </select>
            </td>
        </tr>
        </tbody>
    </table>
</template>

<script>
export default {
    name: 'infopanel',
    props: ['model', 'statuses', 'ratings', 'managers'],
    data() {
        return {
            form: {
                legal_name: this.model.legal_name,
                status: this.model.status,
                city: this.model.city,
                rating_id: this.model.rating_id,
                manager_id: this.model.manager_id,
            }
        }
    },
    computed: {
        merchant: {
            get() {return this.model},
            set(value) {this.$emit('update:model', value)},
        },
    },
};
</script>

<style scoped>

</style>