<template>
    <div class="shadow mt-3 p-3 w-100">
        <table class="table table-condensed" v-if="history.length > 0">
            <thead>
                <tr>
                    <th>Дата</th>
                    <th>Заказ</th>
                    <th>Сумма</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(item, index) in history">
                    <td>{{ item.created_at }}</td>
                    <td><a :href="getRoute('orders.flowDetail', {id: item.id})">{{ item.number }}</a></td>
                    <td>{{ item.cost }}</td>
                </tr>
            </tbody>
        </table>
        <span v-else>
            Для данного покупателя нет истории заказов!
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
