<template>
    <div>
        <b-card v-for="delivery in order.deliveries" v-bind:key="delivery.id" class="mb-4">
            <b-row>
                <div class="col-sm-6">
                    <h4 class="card-title">
                        <fa-icon icon="truck"></fa-icon>
                        Доставка {{ delivery.number }}
                    </h4>
                </div>
                <div class="col-sm-6">
                    <div class="float-right" v-if="canUpdate(blocks.orders)">
                        <b-dropdown text="Действия" size="sm"
                                    v-if="canSaveDeliveryOrder(delivery) || canCancelDeliveryOrder(delivery) || canCancelDelivery(delivery)">
                            <b-dropdown-item-button v-if="canSaveDeliveryOrder(delivery)"
                                                    @click="saveDeliveryOrder(delivery)">
                                Создать/обновить заказ на доставку у ЛО
                            </b-dropdown-item-button>
                            <b-dropdown-item-button v-if="canCancelDeliveryOrder(delivery)"
                                                    @click="cancelDeliveryOrder(delivery)">
                                Отменить заказ на доставку у ЛО
                            </b-dropdown-item-button>
                            <b-dropdown-item-button v-if="canCancelDelivery(delivery)"
                                                    @click="showOrderReturnModal(delivery)">
                                Отменить доставку
                            </b-dropdown-item-button>
                        </b-dropdown>
                        <button class="btn btn-light btn-sm" @click="editDelivery(delivery)"
                                v-if="canEditDelivery(delivery)">
                            <fa-icon icon="pencil-alt" title="Изменить"/>
                        </button>
                    </div>
                </div>
            </b-row>
            <b-row>
                <div class="col-sm-6">
                    <span class="font-weight-bold">ID:</span> {{ delivery.id }}
                </div>
                <div class="col-sm-6">
                    <span class="font-weight-bold">Статус доставки:</span>
                    <delivery-status :status='delivery.status'/>
                    {{ delivery.status_at }}
                    <p v-if="delivery.is_problem">
                        <span class="badge badge-danger">Проблемная</span>
                        {{ delivery.is_problem_at }}
                    </p>
                    <p v-if="delivery.is_canceled">
                        <span class="badge badge-danger">Отменена</span>
                        {{ delivery.is_canceled_at }}
                    </p>
                    <p v-if="delivery.is_canceled">
                        <span class="font-weight-bold">Причина отмены:</span>
                        {{ getReturnReason(delivery) }}
                    </p>
                </div>
            </b-row>
            <b-row>
                <div class="col-sm-6">
                    <span class="font-weight-bold">Статус оплаты:</span>
                    <payment-status :status='delivery.payment_status'/>
                </div>
                <div class="col-sm-6">
                    <span class="font-weight-bold">Статус доставки у ЛО:</span>
                    <span v-if="delivery.status_xml_id">
                        {{ delivery.status_xml_id.name }}
                        <fa-icon icon="question-circle" v-if="delivery.status_xml_id.description"
                                 v-b-popover.hover="delivery.status_xml_id.description"></fa-icon>
                    </span>
                    {{ delivery.status_xml_id_at }}
                </div>
            </b-row>
            <b-row class="mt-2 border-top">
                <div class="col-sm-6">
                    <span class="font-weight-bold">Мерчанты:</span>
                    <template v-for="(merchant, key) in delivery.merchants">
                        <span v-if="key > 0">, </span><a :href="getRoute('merchant.detail', {id: merchant.id})"
                                                         target="_blank">{{ merchant.legal_name }}</a>
                    </template>
                </div>
                <div class="col-sm-6">
                    <span class="font-weight-bold">№ отправлений:</span>
                    <template v-for="(shipment, key) in delivery.shipments">
                        <span v-if="key > 0">, </span>
                        <button @click="openTab('shipments')" class="btn btn-link">{{ shipment.number }}</button>
                    </template>
                </div>
            </b-row>
            <b-row class="mt-2 border-top">
                <div class="col-sm-6">
                    <span class="font-weight-bold">Способ доставки:</span> {{ delivery.delivery_method.name }}
                </div>
                <div class="col-sm-6">
                    <span class="font-weight-bold">Логистический оператор:</span>
                    <a :href="getRoute('deliveryService.detail', {id: delivery.delivery_service.id})" target="_blank">
                        {{ delivery.delivery_service.name }}
                    </a>
                </div>
            </b-row>
            <b-row>
                <div class="col-sm-6">
                    <span class="font-weight-bold">DT:</span>
                    {{ delivery.dt ? delivery.dt + ' ' + pluralForm(delivery.dt, ['день', 'дня', 'дней']) : '' }}
                </div>
                <div class="col-sm-6">
                    <span class="font-weight-bold">PDD:</span>
                    {{ delivery.pdd }}
                </div>
            </b-row>
            <b-row>
                <div class="col-sm-6">
                    <span class="font-weight-bold">Желаемая покупателем дата доставки:</span>
                    {{ delivery.delivery_at }}<br>
                    <span class="font-weight-bold">Желаемое покупателем время доставки:</span>
                    <template v-if="delivery.delivery_time_start && delivery.delivery_time_end">
                        с {{ delivery.delivery_time_start }} по {{ delivery.delivery_time_end }}
                        <template v-if="delivery.delivery_time_code">(code={{ delivery.delivery_time_code }})</template>
                    </template>
                    <template v-else>не указано</template>
                </div>
                <div class="col-sm-6">
                    <template v-if="delivery.point">
                        <p class="font-weight-bold text-danger" v-if="!delivery.point.active">Точка выдачи заказа больше
                            не активна! Необходимо сменить на другую!</p>
                        <p class="font-weight-bold">{{ delivery.point.type.name }} {{ delivery.point.name }}</p>
                        <p><span class="font-weight-bold">Адрес:</span> {{ delivery.point.address.address_string }}</p>
                        <p><span class="font-weight-bold">Телефон:</span> {{ delivery.point.phone }}</p>
                        <p><span class="font-weight-bold">График работы:</span> {{ delivery.point.timetable }}</p>
                        <p><span class="font-weight-bold">Способы оплаты:</span>
                            {{ delivery.point.has_payment_card ? 'Наличные и банковские карты' : 'Только наличные' }}
                        </p>
                    </template>
                    <template v-else>
                        <span class="font-weight-bold">Адрес доставки:</span>
                        {{ delivery.delivery_address.full_address_string }}
                    </template>
                </div>
            </b-row>
            <b-row>
                <div class="col-sm-6">
                    <span class="font-weight-bold">Трекинг-номер у ЛО:</span>
                    {{
                        delivery.tracknumber ? delivery.tracknumber : (delivery.xml_id ? delivery.xml_id : 'Заказ на доставку ещё не создан')
                    }}
                </div>
                <div class="col-sm-6">
                    <span class="font-weight-bold">Последняя ошибка при создании заказа на доставку у ЛО:</span>
                    {{ delivery.error_xml_id }}
                </div>
            </b-row>
            <b-row>
                <div class="col-sm-6">
                    <span class="font-weight-bold">Тариф ЛО:</span>
                    <template v-if="delivery.tariff">
                        <span :title="delivery.tariff.description">{{
                                delivery.tariff.name
                            }} (ID={{ delivery.tariff.xml_id }})</span>
                    </template>
                </div>
            </b-row>
            <b-row class="mt-2 border-top">
                <div class="col-sm-6">
                    <span class="font-weight-bold">Стоимость товаров:</span> {{ preparePrice(delivery.product_cost) }}
                    руб.
                </div>
                <div class="col-sm-6">
                    <span class="font-weight-bold">Стоимость доставки от ЛО:</span>
                    {{ preparePrice(delivery.cost) }} руб.
                </div>
            </b-row>
            <b-row class="mt-2 border-top">
                <div class="col-sm-6">
                    <span class="font-weight-bold">Габариты (ДxШxВ):</span>
                    {{ delivery.length|integer }}x{{ delivery.width|integer }}x{{ delivery.height|integer }} мм
                </div>
                <div class="col-sm-6">
                    <span class="font-weight-bold">Вес:</span> {{ delivery.weight|integer }} г
                </div>
            </b-row>
            <b-row>
                <div class="col-sm-6">
                    <span class="font-weight-bold">Кол-во товаров:</span> {{ delivery.product_qty }} шт.
                </div>
            </b-row>
        </b-card>

        <modal-delivery-edit :model-delivery.sync="selectedDelivery" :model-order.sync="order"
                             v-if="Object.values(selectedDelivery).length > 0"/>
        <modal-add-return-reason :returnReasons="order.orderReturnReasons" type="delivery"
                                 @update:modelElement="cancelDelivery($event)"/>
    </div>
</template>
<script>
import Services from '../../../../../scripts/services/services';
import ModalDeliveryEdit from './forms/modal-delivery-edit.vue';
import ModalAddReturnReason from "./forms/modal-add-return-reason.vue";

export default {
    props: {
        model: {},
    },
    components: {
        ModalDeliveryEdit,
        ModalAddReturnReason,
    },
    data() {
        return {
            selectedDelivery: {},
            deliveryForCancel: {},
        }
    },
    methods: {
        openTab(tab) {
            Services.event().$emit('showTab', tab);
        },
        canSaveDeliveryOrder(delivery) {
            return ((delivery.status && delivery.status.id === this.deliveryStatuses.assembling.id) ||
                (delivery.status && delivery.status.id === this.deliveryStatuses.assembled.id) &&
                !delivery.is_canceled);
        },
        saveDeliveryOrder(delivery) {
            let errorMessage = 'Ошибка при создании/обновлении заказа на доставку у ЛО';

            Services.showLoader();
            Services.net().put(this.getRoute('orders.detail.deliveries.saveDeliveryOrder', {
                id: delivery.order_id,
                deliveryId: delivery.id
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
        canCancelDeliveryOrder(delivery) {
            return delivery.status && delivery.status.id < this.deliveryStatuses.onPointIn.id && delivery.xml_id;
        },
        showOrderReturnModal(delivery) {
            this.deliveryForCancel = delivery;
            if (Boolean(this.order.can_partially_cancelled)) {
                this.$bvModal.show('modal-add-return-reason-delivery');
            } else {
                Services.msg('Заказ был оплачен способом оплаты, для которого недоступен частичный возврат', 'danger');
            }
        },
        cancelDeliveryOrder(delivery) {

            let errorMessage = 'Ошибка при отмене заказа на доставку у ЛО';

            Services.showLoader();
            Services.net().put(this.getRoute('orders.detail.deliveries.cancelDeliveryOrder', {
                id: delivery.order_id,
                deliveryId: delivery.id
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
        canCancelDelivery(delivery) {
            return delivery.status && delivery.status.id < this.deliveryStatuses.done.id && !delivery.is_canceled
        },
        cancelDelivery(returnReason) {
            let errorMessage = 'Ошибка при отмене доставки';

            Services.showLoader();
            Services.net().put(this.getRoute('orders.detail.deliveries.cancel', {
                id: this.deliveryForCancel.order_id,
                deliveryId: this.deliveryForCancel.id
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
        canEditDelivery(delivery) {
            return delivery.status && delivery.status.id < this.deliveryStatuses.done.id && !delivery.is_canceled;
        },
        editDelivery(delivery) {
            this.selectedDelivery = delivery;
            this.$bvModal.show('modal-delivery-edit');
        },
        getReturnReason(delivery) {
            let returnReason = this.order.orderReturnReasons.find(
                returnReason => returnReason.id === delivery.return_reason_id
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
