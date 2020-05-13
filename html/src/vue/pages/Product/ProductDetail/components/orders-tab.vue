<template>
    <div>
        <table class="table table-condensed">
            <thead>
                <tr>
                    <th>№ заказа/№ отправления</th>
                    <th>Дата заказа</th>
                    <th>Дата доставки</th>
                    <th>Сумма</th>
                    <th>Покупатель</th>
                    <th>Адрес доставки</th>
                    <th>Дата отгрузки</th>
                    <th>Статус заказа</th>
                    <th>Статус отправления</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>100001/100001-1</td>
                    <td>13.05.2020</td>
                    <td>18.05.2020</td>
                    <td>4195 р.</td>
                    <td>Баринов Виктор Петрович</td>
                    <td>г. Москва, Большая шоссейная, д 12</td>
                    <td>14.05.2020</td>
                    <td>Доставлено</td>
                    <td>Доставлено получателю</td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
    import FInput from "../../../../components/filter/f-input.vue";

    export default {
        components:{
            FInput,
        },
        data: function() {
            return {
            }
        },
        props: {
        },
        methods: {
        },
        computed: {
        },
    }
</script>
