<template>
    <div>
        <table class="table table-condensed" v-if="history.length > 0">
            <thead>
                <tr>
                    <th>Дата</th>
                    <th>Пользователь</th>
                    <th>Действие</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(item, index) in history">
                    <td>{{ item.created_at }}</td>
                    <td><span v-if="user">{{ item.user.email }} (ID: {{ item.user_id }})</span><span v-else>Система</span></td>
                    <td>{{ item.typeName }}</td>
                </tr>
            </tbody>
        </table>
        <span v-else>
            Для данного заказа нет истории изменений!
        </span>
    </div>
</template>

<script>
import {mapGetters} from "vuex";

export default {
    props: {
        history: Array,
        pager: Object
    },
    computed: {
        ...mapGetters(['getRoute']),
    },
    mounted() {
    }
}
</script>

<style scoped>

</style>
