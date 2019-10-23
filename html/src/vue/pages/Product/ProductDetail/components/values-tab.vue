<template>
    <div class="d-flex justify-content-start align-content-stretch">
        <div class="shadow mt-3 mr-3">
            <div class="card-head">
                Особенности хранения и транспортировки
                <fa-icon icon="pencil-alt" class="corner-edit-btn"></fa-icon>
            </div>
            <div class="px-5 pb-5">
                <table class="values-table">
                    <tbody>
                        <tr>
                            <th>Условия хранения:</th>
                            <td>
                                Температура: 5-30 ℃
                            </td>
                        </tr>
                        <tr>
                            <th></th>
                            <td>Давление на пачку: < 5 кг</td>
                        </tr>
                        <tr>
                            <th>Особая упаковка:</th>
                            <td>
                                Не требуется
                            </td>
                        </tr>
                        <tr>
                            <th>Срок годности:</th>
                            <td>5 лет</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: {
            product: {},
            brand: {},
            category: {},
            stocks: {},
            stores: {}
        },
        methods: {}
    }
</script>

<style scoped>
    .values-table th {
        text-align: end;
        padding-right: 8px;
    }
    .values-table td {
        padding-left: 8px;
    }
    .card-head {
        height: 48px;
        padding: 8px 16px;
    }
    .corner-edit-btn {
        position: relative;
        float: right;
        top: 5px;
        color: gray;
        transition: 0.3s all;
        cursor: pointer;
    }
    .corner-edit-btn:hover {
        color: black;
    }
</style>