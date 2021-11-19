<template>
    <div>
        <div class="d-flex justify-content-start align-items-start">
            <shadow-card title="Хранение и доставка" :buttons="canUpdate(blocks.products) ? {onEdit:'pencil-alt'} : {}" @onEdit="openModal('DeliveryValuesEdit')">
                <table class="values-table">
                    <tbody>
                    <tr>
                        <th>Ширина:</th>
                        <td>{{ product.width }} мм</td>
                    </tr>
                    <tr>
                        <th>Высота:</th>
                        <td>{{ product.height }} мм</td>
                    </tr>
                    <tr>
                        <th>Глубина:</th>
                        <td>{{ product.length }} мм</td>
                    </tr>
                    <tr>
                        <th>Вес:</th>
                        <td>{{ product.weight }} гр</td>
                    </tr>
                    <tr>
                        <th>Кол-во дней для возврата:</th>
                        <td>{{ product.days_to_return }}</td>
                    </tr>
                    <tr>
                        <th>Особая упаковка:</th>
                        <td>{{ product.need_special_case ? product.need_special_case : 'Нет' }}</td>
                    </tr>
                    <tr>
                        <th>Особые условия хранения:</th>
                        <td>{{ product.need_special_store ? product.need_special_store : 'Нет' }}</td>
                    </tr>
                    <tr>
                        <th>Хрупкое:</th>
                        <td>{{ product.fragile ? 'Да' : 'Нет' }}</td>
                    </tr>
                    <tr>
                        <th>Газ:</th>
                        <td>{{ product.gas ? 'Да' : 'Нет' }}</td>
                    </tr>
                    <tr>
                        <th>Легковоспламеняющееся:</th>
                        <td>{{ product.explosive ? 'Да' : 'Нет' }}</td>
                    </tr>
                    <tr>
                        <th>В составе элемент питания:</th>
                        <td>{{ product.has_battery ? 'Да' : 'Нет' }}</td>
                    </tr>
                    </tbody>
                </table>
            </shadow-card>
        </div>

        <transition name="modal">
            <modal :close="closeModal" v-if="isModalOpen('DeliveryValuesEdit')">
                <div slot="header">
                    Хранение и доставка
                </div>
                <div slot="body">
                    <store-delivery-form :source="product"
                                         :options="options"
                                         @onSave="onSave"/>
                </div>
            </modal>
        </transition>
    </div>
</template>

<script>
    import modalMixin from '../../../../mixins/modal.js';

    import Modal from '../../../../components/controls/modal/modal.vue';
    import ShadowCard from '../../../../components/shadow-card.vue';
    import StoreDeliveryForm from './forms/store-and-delivery.vue';

    export default {
        components: {
            Modal,
            ShadowCard,
            StoreDeliveryForm,
        },
        mixins: [modalMixin],
        props: {
            product: Object,
            options: Object
        },
        methods: {
            onSave() {
                this.closeModal();
                this.$emit('onSave');
            },
        },
        computed: {

        }
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
</style>
