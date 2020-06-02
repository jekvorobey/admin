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
                        <th>
                            <template v-if="isWorkStatus">
                                <button class="btn btn-primary"
                                        @click="doForAll('accept')"
                                        title="Принять все товары из заявления">Согласовать все</button><br>
                                <button class="btn btn-primary mt-1"
                                        @click="openModal('offerProductReject')"
                                        title="Отклонить все товары из заявления">Отклонить все</button>
                            </template>
                        </th>
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
        <modal-products-reject
                :comment.sync="comment"
                @submit="doForAll"
                modal-name="offerProductReject">
        </modal-products-reject>
    </div>
</template>

<script>

    import Services from "../../../../../../scripts/services/services";
    import modalMixin from "../../../../../mixins/modal";
    import ModalProductsReject from './modal-products-reject.vue';

    export default {
        components: {ModalProductsReject},
        mixins: [modalMixin],
        props: ['claim',],
        data() {
            return {
                comment: '',
            }
        },
        methods: {
            isStatus(statusId) {
                return this.claim.status === statusId;
            },
            isProductChecked(approvalStatusId) {
                return approvalStatusId === 4 || approvalStatusId === 5;
            },
            /**
             * Выполнить действие для всех ID в заявке
             * @example accept - одобрить все товары в заявке
             * @example reject - отклонить все товары в заявке
             * @param action
             */
            doForAll(action) {
                let status = '';
                switch (action) {
                    case 'accept':
                        status = action;
                        break;
                    case 'reject':
                        status = action;
                        break;
                    default: return;
                }

                Services.showLoader();
                Services.net().put(this.getRoute('products.massApproval', {}), {},
                    {
                        productIds: Object.keys(this.claim.products),
                        status: status,
                        comment: this.comment
                    }).then(data => {
                        Services.msg("Изменения сохраняются...");
                }, () => {
                    this.showMessageBox({title: 'Ошибка', text: 'Не удалось согласовать или отклонить продукты'});
                }).finally(() => {
                    Services.hideLoader();
                    setTimeout(() => {
                        window.location = this.getRoute(
                            'productCheckClaims.detail', {id: this.claim.id}
                            )}, 1000);
                })
            },
        },
        computed: {
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