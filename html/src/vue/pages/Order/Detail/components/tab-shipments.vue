<template>
    <div>
        <b-card v-for="shipment in order.shipments" v-bind:key="shipment.id" class="mb-4">
            <b-row>
                <div class="col-sm-6">
                    <h4 class="card-title">
                        <fa-icon icon="truck-loading"></fa-icon>
                        Отправление {{ shipment.number }}
                    </h4>
                </div>
                <div class="col-sm-6">
                    <div class="float-right">
                        <b-dropdown text="Документы" size="sm" v-if="documents(shipment)">
                            <b-dropdown-item-button v-for="document in documents(shipment)"
                                                    @click="downloadDocument(shipment, document.value)"
                                                    v-bind:key="document.value">
                                {{ document.text }}
                            </b-dropdown-item-button>
                        </b-dropdown>
                        <b-dropdown text="Шаблоны документов" size="sm">
                            <b-dropdown-item-button v-for="document in documentTemplates"
                                                    @click="downloadDocumentTemplate(document.value)"
                                                    v-bind:key="document.value">
                                {{ document.text }}
                            </b-dropdown-item-button>
                        </b-dropdown>
                        <b-dropdown text="Действия" size="sm"
                                    v-if="canMarkAsNonProblem(shipment) || canGetBarcodes(shipment) || canGetCdekReceipt(shipment) || canCancelShipment(shipment)">
                            <b-dropdown-item-button v-if="canMarkAsNonProblem(shipment)"
                                                    @click="markAsNonProblem(shipment)">
                                Пометить как непроблемное
                            </b-dropdown-item-button>
                            <b-dropdown-item-button :disabled="!canGetBarcodes(shipment)">
                                <a :href="barcodes ? getRoute('orders.detail.shipments.barcodes', {id: order.id, shipmentId: shipment.id}) : ''"

                                   :class="canGetBarcodes(shipment) ? 'text-dark' : 'text-danger'"
                                   :title="getBarcodesTitle(shipment)">
                                    <fa-icon icon="barcode"></fa-icon>
                                    Получить штрихкоды
                                </a>
                            </b-dropdown-item-button>
                            <b-dropdown-item-button v-if="isAssembledStatus(shipment) && canGetCdekReceipt(shipment)">
                                <a :href="getRoute('orders.detail.shipments.cdekReceipt', {id: order.id, shipmentId: shipment.id})"
                                   class="text-dark">
                                    <fa-icon icon="file-invoice"></fa-icon>
                                    Получить квитанцию
                                </a>
                            </b-dropdown-item-button>
                            <b-dropdown-item-button v-if="canCancelShipment(shipment)"
                                                    @click="showOrderReturnModal(shipment)">
                                <fa-icon icon="times"></fa-icon>
                                Отменить отправление
                            </b-dropdown-item-button>
                        </b-dropdown>
                        <button class="btn btn-light btn-sm" @click="editShipment(shipment)"
                                v-if="canEditShipment(shipment)">
                            <fa-icon icon="pencil-alt" title="Изменить"/>
                        </button>
                    </div>
                </div>
            </b-row>
            <b-row>
                <div class="col-sm-6">
                    <p><span class="font-weight-bold">ID:</span> {{ shipment.id }}</p>
                    <p><span class="font-weight-bold">ID доставки:</span> {{ shipment.delivery_id }}</p>
                </div>
                <div class="col-sm-6">
                    <span class="font-weight-bold">Статус отправления:</span>
                    <shipment-status :status='shipment.status'/>
                    {{ shipment.status_at }}
                    <p v-if="shipment.is_problem">
                        <span class="badge badge-danger cursor-default" v-b-popover.hover="shipment.assembly_problem_comment">Проблемное</span>
                        {{ shipment.is_problem_at }}
                    </p>
                    <p v-if="shipment.is_canceled">
                        <span class="badge badge-danger">Отменено</span>
                        {{ shipment.is_canceled_at }}
                    </p>
                    <p v-if="shipment.is_canceled">
                        <span class="font-weight-bold">Причина отмены:</span>
                        {{ getReturnReason(shipment) }}
                    </p>
                </div>
            </b-row>
            <b-row>
                <div class="col-sm-6">
                    <span class="font-weight-bold">Статус оплаты:</span>
                    <payment-status :status='shipment.payment_status'/>
                </div>
                <div class="col-sm-6">
                    <span class="font-weight-bold">Статус доставки у ЛО:</span>
                    <span v-if="shipment.delivery_status_xml_id">
                        {{ shipment.delivery_status_xml_id.name }}
                        <fa-icon icon="question-circle" v-if="shipment.delivery_status_xml_id.description"
                                 v-b-popover.hover="shipment.delivery_status_xml_id.description"></fa-icon>
                    </span>
                    {{ shipment.delivery_status_xml_id_at }}
                </div>
            </b-row>
            <b-row class="mt-2 border-top">
                <div class="col-sm-6">
                    <span class="font-weight-bold">Мерчант:</span>
                    <a :href="getRoute('merchant.detail', {id: shipment.merchant.id})"
                       target="_blank">{{ shipment.merchant.legal_name }}</a>
                </div>
                <div class="col-sm-6">
                    <span class="font-weight-bold">Склад:</span>
                    <a :href="getRoute('merchantStore.edit', {id: shipment.store.id})"
                       target="_blank">{{ shipment.store.address.address_string }}</a>
                </div>
            </b-row>
            <b-row class="mt-2 border-top">
                <div class="col-sm-6">
                    <span class="font-weight-bold">Груз:</span>
                    <a :href="getRoute('cargo.detail', {id: shipment.cargo.id})" target="_blank" v-if="shipment.cargo">#{{
                            shipment.cargo.id
                        }} от {{ shipment.cargo.created_at }}</a>
                    <template v-else>нет</template>
                </div>
                <div class="col-sm-6">
                    <p>
                        <span class="font-weight-bold">Логистический оператор для нулевой мили:</span>
                        <a :href="getRoute('deliveryService.detail', {id: shipment.delivery_service_zero_mile ? shipment.delivery_service_zero_mile.id : shipment.delivery_service.id})"
                           target="_blank">
                            {{
                                shipment.delivery_service_zero_mile ? shipment.delivery_service_zero_mile.name : shipment.delivery_service.name
                            }}
                        </a>
                    </p>
                    <p>
                        <span class="font-weight-bold">Логистический оператор для последней мили:</span>
                        <a :href="getRoute('deliveryService.detail', {id: shipment.delivery_service.id})"
                           target="_blank">
                            {{ shipment.delivery_service.name }}
                        </a>
                    </p>
                </div>
            </b-row>
            <b-row>
                <div class="col-sm-6">
                    <p><span class="font-weight-bold">PSD:</span> {{ shipment.psd }}</p>
                    <p v-if="shipment.fsd"><span class="font-weight-bold">FSD:</span> {{ shipment.fsd }}</p>
                </div>
                <div class="col-sm-6">
                    <span class="font-weight-bold">PDD:</span>
                    {{ shipment.delivery_pdd }}
                </div>
            </b-row>
            <b-row class="mt-2 border-top">
                <div class="col-sm-6">
                    <span class="font-weight-bold">Стоимость товаров:</span> {{ preparePrice(shipment.cost) }} руб.
                </div>
            </b-row>
            <b-row class="mt-2 border-top">
                <div class="col-sm-6">
                    <span class="font-weight-bold">Габариты (ДxШxВ):</span>
                    {{ shipment.length|integer }}x{{ shipment.width|integer }}x{{ shipment.height|integer }} мм
                </div>
                <div class="col-sm-6">
                    <span class="font-weight-bold">Вес:</span> {{ shipment.weight|integer }} г
                </div>
            </b-row>
            <b-row>
                <div class="col-sm-6">
                    <span class="font-weight-bold">Кол-во коробок:</span>
                    {{ shipment.packages.length }} шт
                </div>
                <div class="col-sm-6">
                    <span class="font-weight-bold">Кол-во товаров:</span> {{ shipment.product_qty }} шт.
                </div>
            </b-row>
            <b-row v-if="shipment.assembly_problem_comment">
                <b-col>
                    <span class="font-weight-bold">Комментарий мерчанта о проблеме со сборкой отправления:</span>
                    {{ shipment.assembly_problem_comment }}
                </b-col>
            </b-row>

            <shipment-items :model-order.sync="order" :model-shipment.sync="shipment" :can-edit="true" class="mt-4"/>
        </b-card>

        <modal-shipment-edit :model-shipment.sync="selectedShipment" :model-order.sync="order"
                             v-if="Object.values(selectedShipment).length > 0"/>
        <modal-add-return-reason :returnReasons="order.orderReturnReasons" type="shipment"
                                 @update:modelElement="cancelShipment($event)"/>
    </div>
</template>
<script>
import ModalShipmentEdit from './forms/modal-shipment-edit.vue';
import ShipmentItems from './forms/shipment-items.vue';
import Services from '../../../../../scripts/services/services';
import ModalAddReturnReason from "./forms/modal-add-return-reason.vue";

export default {
    props: {
        model: {},
        barcodes: ''
    },
    components: {
        ModalShipmentEdit,
        ShipmentItems,
        ModalAddReturnReason,
    },
    data() {
        return {
            selectedShipment: {},
            shipmentForCancel: {},
            documentTemplates: [
                {value: 'claimAct', text: 'Акт-претензия'},
                {value: 'acceptanceAct', text: 'Акт приема-передачи'},
                {value: 'assemblingCard', text: 'Карточка сборки'},
                {value: 'inventory', text: 'Опись'},
            ],
        }
    },
    methods: {
        productPhoto(product) {
            return '/files/compressed/' + product.mainImage.file_id + '/50/50/webp';
        },
        downloadDocument(shipment, type) {
            window.open(this.getRoute('orders.detail.shipments.documents.' + type, {
                id: this.order.id,
                shipmentId: shipment.id
            }));
        },
        downloadDocumentTemplate(type) {
            window.open(this.getRoute('documentTemplates.' + type));
        },
        isAssembledStatus(shipment) {
            return shipment.status && shipment.status.id === this.shipmentStatuses.assembled.id;
        },
        isAssembledConsolidatedStatus(shipment) {
            return shipment.status && shipment.status.id === this.shipmentStatuses.assembled.id;
        },
        getBarcodesTitle(shipment) {
            return this.canGetBarcodes(shipment) ? '' :
                'Получить штрихкоды Вы сможете когда будет создано задание на доставку у логистического оператора';
        },
        canGetBarcodes(shipment) {
            return shipment.delivery_xml_id && this.barcodes;
        },
        canGetCdekReceipt(shipment) {
            return shipment.delivery_xml_id && shipment.delivery_service.id === this.deliveryServices.cdek.id;
        },
        canMarkAsNonProblem(shipment) {
            return shipment.is_problem && !shipment.is_canceled;
        },
        canCancelShipment(shipment) {
            return shipment.status && shipment.status.id < this.shipmentStatuses.done.id && !shipment.is_canceled
        },
        markAsNonProblem(shipment) {
            let errorMessage = 'Ошибка при изменении отправления';

            Services.showLoader();
            Services.net().put(this.getRoute('orders.detail.shipments.markAsNonProblem', {
                id: this.order.id,
                shipmentId: shipment.id
            })).then(data => {
                if (data.order) {
                    this.$set(this, 'order', data.order);
                    this.$set(this.order, 'shipments', data.order.shipments);
                    Services.msg("Изменения сохранены");
                } else {
                    Services.msg(errorMessage, 'danger');
                }
            }, () => {
                Services.msg(errorMessage, 'danger');
            }).finally(data => {
                Services.hideLoader();
            });
        },
        showOrderReturnModal(shipment) {
            this.shipmentForCancel = shipment;
            if (Boolean(this.order.can_partially_cancelled)) {
                this.$bvModal.show('modal-add-return-reason-shipment');
            } else {
                Services.msg('Заказ был оплачен способом оплаты, для которого недоступен частичный возврат', 'danger');
            }
        },
        cancelShipment(returnReason) {
            let errorMessage = 'Ошибка при отмене отправления';

            Services.showLoader();
            Services.net().put(this.getRoute('orders.detail.shipments.cancel', {
                id: this.order.id,
                shipmentId: this.shipmentForCancel.id
            }), null, {
                orderReturnReason: returnReason
            }).then(data => {
                if (data.order) {
                    this.$set(this, 'order', data.order);
                    this.$set(this.order, 'shipments', data.order.shipments);
                    Services.msg("Изменения сохранены");
                } else {
                    Services.msg(errorMessage, 'danger');
                }
            }, () => {
                Services.msg(errorMessage, 'danger');
            }).finally(data => {
                Services.hideLoader();
            });
        },
        canEditShipment(shipment) {
            return shipment.status && shipment.status.id < this.shipmentStatuses.shipped.id && !shipment.is_canceled;
        },
        editShipment(shipment) {
            this.selectedShipment = shipment;
            this.$bvModal.show('modal-shipment-edit');
        },
        documents(shipment) {
            let documents = [{value: 'assemblingCard', text: 'Карточка сборки'}];

            if (shipment.status && shipment.status.id >= this.shipmentStatuses.assembling.id) {
                documents.push({value: 'inventory', text: 'Опись'});
            }
            if (shipment.status && shipment.status.id >= this.shipmentStatuses.assembled.id) {
                documents.push({value: 'acceptanceAct', text: 'Акт приема-передачи'});
            }

            return documents;
        },
        getReturnReason(shipment) {
            let returnReason = this.order.orderReturnReasons.find(
                returnReason => returnReason.id === shipment.return_reason_id
            );

            return returnReason ? returnReason.text : '-';
        },
    },
    computed: {
        order: {
            get() {
                return this.model
            },
            set(value) {
                this.$emit('update:model', value)
            },
        },
    }
}
</script>
