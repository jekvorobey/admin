<template>
    <div class="d-flex justify-content-start align-content-stretch">
        <div class="shadow mt-3 mr-3 p-3">
            <table class="table table-condensed">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Название</th>
                        <th>Артикул</th>
                        <th>Статус согласования</th>
                        <th>Комментарий по статусу согласования</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="product in claim.products">
                        <td>{{ product.id }}</td>
                        <td>
                            <a :href="getRoute('products.detail', {id: product.id})" target="_blank">
                                {{ product.name }}
                            </a>
                        </td>
                        <td>{{ product.vendor_code }}</td>
                        <td>{{ product.approval_status.name }}</td>
                        <td>{{ product.approval_status_comment }}</td>
                        <td>
                            <a :href="getRoute('products.detail', {id: product.id})" target="_blank"
                                    v-if="isWorkStatus && !isProductChecked(product.approval_status.id)">
                                <button class="btn btn-primary">Проверить...</button>
                            </a>
                        </td>
                    </tr>
                    <tr v-if="!claim.products">
                        <td colspan="7">Товаров нет</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script>
    import {mapGetters} from 'vuex';

    export default {
    props: [
        'claim',
    ],
    methods: {
        isStatus(statusId) {
            return this.claim.status === statusId;
        },
        isProductChecked(approvalStatusId) {
            return approvalStatusId === 4 || approvalStatusId === 5;
        }
    },
    computed: {
        ...mapGetters(['getRoute']),
        isWorkStatus() {
            return this.isStatus(2);
        },
    },
};
</script>
<style scoped>
    th {
        vertical-align: top !important;
    }
</style>