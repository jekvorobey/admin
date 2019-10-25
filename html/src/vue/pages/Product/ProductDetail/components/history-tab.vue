<template>
    <div>
        <table class="table table-condensed">
            <thead>
                <tr>
                    <th>Дата</th>
                    <th>Пользователь</th>
                    <th>Действие</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>2019-06-13 14:37</td>
                    <td>Администратор</td>
                    <td>Установил цену оффера в 15000 руб.</td>
                </tr>
                <tr>
                    <td>2019-06-12 17:22</td>
                    <td>Администратор</td>
                    <td>Сменил сегмент товара на "С"</td>
                </tr>
                <tr>
                    <td>2019-06-12 16:13</td>
                    <td>Администратор</td>
                    <td>Создал товар</td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
    export default {
        props: {
            operator: {}
        }
    }
</script>

<style scoped>

</style>