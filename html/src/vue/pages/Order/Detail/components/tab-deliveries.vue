<template>
    <div>
        <b-card v-for="delivery in deliveries" v-bind:key="delivery.id" class="mb-4">
            <b-row>
                <div class="col-sm-6"><h4 class="card-title">Доставка {{delivery.number}}</h4></div>
                <div class="col-sm-6">
                    <div class="float-right">
                        <button class="btn btn-light btn-sm" @click="editDelivery(delivery)">
                            <fa-icon icon="pencil-alt" title="Изменить"/>
                        </button>
                        <button class="btn btn-light btn-sm">
                            <fa-icon icon="times" title="Удалить"/>
                        </button>
                    </div>
                </div>
            </b-row>
            <b-row>
                <div class="col-sm-6">
                    <span class="font-weight-bold">ID:</span> {{delivery.id}}
                </div>
                <div class="col-sm-6">
                    <span class="font-weight-bold">Статус доставки:</span>
                    <delivery-status :status='delivery.status'/>
                    {{delivery.status_at}}
                    <p v-if="delivery.is_problem">
                        <span class="badge badge-danger">Проблемная</span>
                        {{delivery.is_problem_at}}
                    </p>
                    <p v-if="delivery.is_canceled">
                        <span class="badge badge-danger">Отменена</span>
                        {{delivery.is_canceled_at}}
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
                    {{delivery.status_xml_id}} {{delivery.status_xml_id_at}}
                </div>
            </b-row>
            <b-row class="mt-2 border-top">
                <div class="col-sm-6">
                    <span class="font-weight-bold">Мерчанты:</span>
                    <template v-for="(merchant, key) in delivery.merchants">
                        <span v-if="key > 0">, </span><a :href="getRoute('merchant.detail', {id: merchant.id})" target="_blank">{{ merchant.legal_name }}</a>
                    </template>
                </div>
                <div class="col-sm-6">
                    <span class="font-weight-bold">№ отправлений:</span>
                    <template v-for="(shipment, key) in delivery.shipments">
                        <span v-if="key > 0">, </span><button @click="openTab('shipments')" class="btn btn-link">{{ shipment.number }}</button>
                    </template>
                </div>
            </b-row>
            <b-row class="mt-2 border-top">
                <div class="col-sm-6">
                    <span class="font-weight-bold">Способ доставки:</span> {{delivery.delivery_method.name}}
                </div>
                <div class="col-sm-6">
                    <span class="font-weight-bold">Логистический оператор:</span>
                    <a :href="getRoute('deliveryService.detail', {id: delivery.delivery_service.id})" target="_blank">
                        {{delivery.delivery_service.name}}
                    </a>
                </div>
            </b-row>
            <b-row>
                <div class="col-sm-6">
                    <span class="font-weight-bold">DT:</span> {{delivery.dt ? delivery.dt + ' ' + pluralForm(delivery.dt, ['день', 'дня', 'дней']) : ''}}
                </div>
                <div class="col-sm-6">
                    <span class="font-weight-bold">PDD:</span>
                    {{delivery.pdd}}
                </div>
            </b-row>
            <b-row>
                <div class="col-sm-6">
                    <span class="font-weight-bold">Желаемая покупателем дата доставки:</span>
                    {{delivery.delivery_at}}
                </div>
                <div class="col-sm-6">
                    <template v-if="delivery.point">
                        <p class="font-weight-bold">{{delivery.point.type.name}} {{delivery.point.name}}</p>
                        <p><span class="font-weight-bold">Адрес:</span> {{delivery.point.address.address_string}}</p>
                        <p><span class="font-weight-bold">Телефон:</span> {{delivery.point.phone}}</p>
                        <p><span class="font-weight-bold">График работы:</span> {{delivery.point.timetable}}</p>
                        <p><span class="font-weight-bold">Способы оплаты:</span> {{delivery.point.has_payment_card ? 'Наличные и банковские карты' : 'Только наличные'}}</p>
                    </template>
                    <template v-else>
                        <span class="font-weight-bold">Адрес доставки:</span>
                        {{delivery.delivery_address.full_address_string}}
                    </template>
                </div>
            </b-row>
            <b-row>
                <div class="col-sm-6">
                    <span class="font-weight-bold">Трекинг-номер у ЛО:</span>
                    {{delivery.xml_id ? delivery.xml_id : 'Заказ на доставку ещё не создан'}}
                </div>
                <div class="col-sm-6">
                    <span class="font-weight-bold">Последняя ошибка при создании заказа на доставку у ЛО:</span>
                    {{delivery.error_xml_id}}
                </div>
            </b-row>
            <b-row>
                <div class="col-sm-6">
                    <span class="font-weight-bold">Тариф ЛО:</span>
                    <template v-if="delivery.tariff">
                        <span :title="delivery.tariff.description">{{delivery.tariff.name}} (ID={{delivery.tariff.xml_id}})</span>
                    </template>
                </div>
            </b-row>
            <b-row class="mt-2 border-top">
                <div class="col-sm-6">
                    <span class="font-weight-bold">Стоимость товаров:</span> {{preparePrice(delivery.product_cost)}} руб.
                </div>
                <div class="col-sm-6">
                    <span class="font-weight-bold">Стоимость доставки от ЛО:</span>
                    {{preparePrice(delivery.cost)}} руб.
                </div>
            </b-row>
            <b-row class="mt-2 border-top">
                <div class="col-sm-6">
                    <span class="font-weight-bold">Габариты (ДxШxВ):</span> {{delivery.length|integer}}x{{delivery.width|integer}}x{{delivery.height|integer}} мм
                </div>
                <div class="col-sm-6">
                    <span class="font-weight-bold">Вес:</span> {{delivery.weight}} г
                </div>
            </b-row>
            <b-row>
                <div class="col-sm-6">
                    <span class="font-weight-bold">Кол-во товаров:</span> {{delivery.product_qty}} шт.
                </div>
            </b-row>
        </b-card>

        <modal-delivery-edit :model.sync="selectedDelivery"/>
    </div>
</template>
<script>
    import Services from "../../../../../scripts/services/services";
    import ModalDeliveryEdit from "./forms/modal-delivery-edit.vue";

    export default {
        props: {
            model: {},
        },
        components: {
            ModalDeliveryEdit,
        },
        data() {
            return {
                selectedDelivery: {}
            }
        },
        methods: {
            openTab(tab) {
                Services.event().$emit('showTab', tab);
            },
            editDelivery(delivery) {
                this.selectedDelivery = delivery;
                this.$bvModal.show('modal-delivery-edit');
            }
        },
        computed: {
            deliveries: {
                get() {return this.model},
                set(value) {this.$emit('update:model', value)},
            },
        }
    }
</script>
