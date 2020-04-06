<template>
    <div class="d-flex justify-content-start align-content-stretch">
        <div class="shadow mt-3 mr-3 p-3">
            <div class="d-flex justify-content-between mt-3 mb-3">
                <div class="action-bar d-flex justify-content-start">
                    <dropdown :items="dropdownItems" class="mr-4 order-btn">
                        <fa-icon icon="file-download"></fa-icon>
                        Скачать документы
                    </dropdown>
                    <dropdown :items="dropdownItems" class="mr-4 order-btn">
                        <fa-icon icon="print"></fa-icon>
                        Распечатать документы
                    </dropdown>
                </div>
                <div class="d-flex justify-content-end">
                    <div>
                        <button class="btn btn-primary" v-if="isCreatedStatus && !isCancel"
                                @click="openModal('addShipment2Cargo')">
                            + Добавить заказы
                        </button>
                    </div>
                </div>
            </div>
            <table class="table table-condensed">
                <thead>
                    <tr>
                        <th>№ отправления</th>
                        <th>Дата отправления</th>
                        <th>Сумма</th>
                        <th>Требуемая дата отгрузки</th>
                        <th>Кол-во коробок</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="shipment in cargo.shipments">
                        <td>
                            <a :href="getRoute('shipment.detail', {id: shipment.id})">{{ shipment.number }}</a>
                        </td>
                        <td>{{ shipment.created_at }}</td>
                        <td>{{ shipment.price }}</td>
                        <td>{{ shipment.required_shipping_at }}</td>
                        <td>{{ shipment.packages.length }}</td>
                        <td>
                            <fa-icon icon="times" title="Удалить из груза" class="cursor-pointer"
                                    @click="deleteShipmentFromCargo(shipment.id)"
                                    v-if="isCreatedStatus && !isCancel"></fa-icon>
                        </td>
                    </tr>
                    <tr v-if="!cargo.shipments || !cargo.shipments.length">
                        <td colspan="7">Заказов нет</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <transition name="modal">
            <modal :close="closeModal" v-if="isModalOpen('addShipment2Cargo')">
                <div slot="header">
                    Добавление заказов в груз
                </div>
                <div slot="body">
                    <add-shipments-form
                            :cargo="cargo"
                            @onSave="onChange"
                    ></add-shipments-form>
                </div>
            </modal>
        </transition>
    </div>
</template>

<script>
    import Dropdown from '../../../../../components/dropdown/dropdown.vue';
    import modal from '../../../../../components/controls/modal/modal.vue';
    import VSelect from '../../../../../components/controls/VSelect/VSelect.vue';
    import AddShipmentsForm from './forms/add-shipments-form.vue';

    import modalMixin from '../../../../../mixins/modal';
    import Services from '../../../../../../scripts/services/services';

    export default {
    props: [
        'cargo',
    ],
    components: {
        AddShipmentsForm,
        Dropdown,
        VSelect,
        modal,
    },
    mixins: [modalMixin],
    data() {
        return {
            isSelectAllBasketItem: false,
            selectedBasketItemIds: [],
            selectedShipmentPackageId : 0,
            selectedBasketItemId : 0,
            selectedQty : 0,
            dropdownItems: [
                {value: 1, text: 'Все'},
                {value: 2, text: 'Покупателю'},
                {value: 3, text: 'Курьеру'},
            ],
        };
    },
    methods: {
        deleteShipmentFromCargo(shipmentId) {
            Services.net().delete(
                this.getRoute('cargo.deleteShipmentFromCargo', {
                    id: this.cargo.id,
                    shipmentId: shipmentId,
                }),
                {},
                {}
            )
            .then(result => {
                this.onChange(result);
            });
        },
        onChange(data) {
            this.$emit('onChange', data);

            this.closeModal();
        },
        isStatus(statusId) {
            return this.cargo.status.id === statusId;
        },
    },
    computed: {
        isCreatedStatus() {
            return this.isStatus(1);
        },
        isRequestSend() {
            return this.cargo.xml_id;
        },
        isCancel() {
            return this.cargo.is_canceled;
        },
    },
};
</script>
<style scoped>
    th {
        vertical-align: top !important;
    }
</style>
