<template>
    <div class="shadow mt-3 p-3 w-100">
        <table class="table table-condensed" v-if="history.length > 0">
            <thead>
            <tr>
                <th>Дата</th>
                <th>Пользователь</th>
                <th>Сущность</th>
                <th>Действие</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="(item, index) in history">
                <td>{{ item.created_at }}</td>
                <td><span v-if="item.user">{{ item.user.email }} (ID: {{ item.user_id }})</span><span v-else>Система</span></td>
                <td>{{ item.entity }} (ID: {{ item.entity_id }})</td>
                <td>{{ item.type.name }}</td>
            </tr>
            </tbody>
        </table>
        <span v-else>
            Нет истории изменений!
        </span>
    </div>
</template>

<script>

    export default {
        props: {
            history: Array,
            pager: Object
        },
        mounted() {
        }
    }
</script>

<style scoped>

</style>
