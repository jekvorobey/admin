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
                    <td><a :href="getRoute('orders.detail', {id: item.id})">{{ item.number }}</a></td>
                    <td>{{ roundValue(item.price) }} руб.</td>
                </tr>
            </tbody>
        </table>
        <span v-else>
            Для данного покупателя нет истории заказов!
        </span>
    </div>
</template>

<script>

    import Helpers from "../../../../../scripts/helpers";

    export default {
    props: {
        history: Array,
        pager: Object
    },
    methods: {
        roundValue(value) {
            return Helpers.roundValue(value)
        }
    },
    mounted() {
    }
}
</script>

<style scoped>

</style>
