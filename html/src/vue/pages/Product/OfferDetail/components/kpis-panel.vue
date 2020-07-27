<template>
    <b-card>
        <table class="table table-sm">
            <thead>
            <tr>
                <th colspan="4">KPIs</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <th>На складах</th>
                <td>{{ 0 }} шт.</td>
            </tr>
            <tr>
                <th>В заказах клиентов</th>
                <td>{{ 0 }} шт.</td>
            </tr>
            <tr>
                <th>На комплектации</th>
                <td>{{ 0 }} шт.</td>
            </tr>
            <tr>
                <th>Готовы к отгрузке</th>
                <td>{{ 0 }} шт.</td>
            </tr>
            <tr>
                <th>Доставляются</th>
                <td>{{ 0 }} шт.</td>
            </tr>
            <tr>
                <th>В пункте выдачи</th>
                <td>{{ 0 }} шт.</td>
            </tr>
            <tr>
                <th>У курьера</th>
                <td>{{ 0 }} шт.</td>
            </tr>
            <tr>
                <th>Доставлены</th>
                <td>{{ 0 }} шт.</td>
            </tr>
            <tr>
                <th>Проблемные</th>
                <td>{{ 0 }} шт.</td>
            </tr>
            <tr>
                <th>Ожидается возврат от курьера</th>
                <td>{{ 0 }} шт.</td>
            </tr>
            <tr>
                <th>Возвращены клиентами</th>
                <td>{{ 0 }} шт.</td>
            </tr>
            <tr>
                <th>Отменены</th>
                <td>{{ 0 }} шт.</td>
            </tr>
            </tbody>
        </table>
    </b-card>
</template>

<script>
    export default {
        props: {
            kpi: {}
        },
    }
</script>