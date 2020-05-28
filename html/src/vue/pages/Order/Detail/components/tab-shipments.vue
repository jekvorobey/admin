<template>
    <div>
        <b-card v-for="shipment in order.shipments" v-bind:key="shipment.id" class="mb-4">
            <b-row>
                <div class="col-sm-6"><h4 class="card-title">Отправление {{shipment.number}}</h4></div>
                <div class="col-sm-6">
                    <div class="float-right">
                        <b-dropdown text="Действия" size="sm" v-if="canMarkAsProblem(shipment) || canMarkAsNonProblem(shipment) || canGetBarcodes(shipment) || canGetCdekReceipt(shipment) || canCancelShipment(shipment)">
                            <b-dropdown-item-button v-if="canMarkAsProblem(shipment)" @click="markAsProblem(shipment)">
                                <fa-icon icon="exclamation"></fa-icon> Пометить как проблемное
                            </b-dropdown-item-button>
                            <b-dropdown-item-button v-if="canMarkAsNonProblem(shipment)" @click="markAsNonProblem(shipment)">
                                Пометить как непроблемное
                            </b-dropdown-item-button>
                            <b-dropdown-item-button v-if="isAssembledStatus(shipment)">
                                <a :href="canGetBarcodes(shipment) ? getRoute('orders.detail.shipments.barcodes', {id: order.id, shipmentId: shipment.id}) : '#'"
                                   class="btn" :class="canGetBarcodes(shipment) ? 'btn-primary' : 'btn-danger'"
                                   :title="getBarcodesTitle(shipment)">
                                    <fa-icon icon="barcode"></fa-icon> Получить штрихкоды
                                </a>
                            </b-dropdown-item-button>
                            <b-dropdown-item-button v-if="isAssembledStatus(shipment) && canGetCdekReceipt(shipment)">
                                <a :href="getRoute('orders.detail.shipments.cdekReceipt', {id: order.id, shipmentId: shipment.id})"
                                   class="btn btn-primary">
                                    <fa-icon icon="file-invoice"></fa-icon> Получить квитанцию
                                </a>
                            </b-dropdown-item-button>
                            <b-dropdown-item-button v-if="canCancelShipment(shipment)" @click="cancelShipment(shipment)">
                                <fa-icon icon="times"></fa-icon> Отменить отправление
                            </b-dropdown-item-button>
                        </b-dropdown>
                        <button class="btn btn-light btn-sm" @click="editShipment(shipment)" v-if="canEditShipment(shipment)">
                            <fa-icon icon="pencil-alt" title="Изменить"/>
                        </button>
                    </div>
                </div>
            </b-row>
            <b-row>
                <div class="col-sm-6">
                    <p><span class="font-weight-bold">ID:</span> {{shipment.id}}</p>
                    <p><span class="font-weight-bold">ID доставки:</span> {{shipment.delivery_id}}</p>
                </div>
                <div class="col-sm-6">
                    <span class="font-weight-bold">Статус отправления:</span>
                    <shipment-status :status='shipment.status'/>
                    {{shipment.status_at}}
                    <p v-if="shipment.is_problem">
                        <span class="badge badge-danger">Проблемное</span>
                        {{shipment.is_problem_at}}
                    </p>
                    <p v-if="shipment.is_canceled">
                        <span class="badge badge-danger">Отменено</span>
                        {{shipment.is_canceled_at}}
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
                    {{shipment.delivery_status_xml_id ? shipment.delivery_status_xml_id : 'N/A'}} {{shipment.delivery_status_xml_id_at}}
                </div>
            </b-row>
            <b-row class="mt-2 border-top">
                <div class="col-sm-6">
                    <span class="font-weight-bold">Мерчант:</span>
                    <a :href="getRoute('merchant.detail', {id: shipment.merchant.id})" target="_blank">{{ shipment.merchant.legal_name }}</a>
                </div>
                <div class="col-sm-6">
                    <span class="font-weight-bold">Склад:</span>
                    <a :href="getRoute('merchantStore.edit', {id: shipment.store.id})" target="_blank">{{ shipment.store.address.address_string }}</a>
                </div>
            </b-row>
            <b-row class="mt-2 border-top">
                <div class="col-sm-6">
                    <span class="font-weight-bold">Груз:</span>
                    <a :href="getRoute('cargo.detail', {id: shipment.cargo.id})" target="_blank" v-if="shipment.cargo">#{{ shipment.cargo.id }} от {{ shipment.cargo.created_at }}</a>
                    <template v-else>нет</template>
                </div>
                <div class="col-sm-6">
                    <p>
                        <span class="font-weight-bold">Логистический оператор для нулевой мили:</span>
                        <a :href="getRoute('deliveryService.detail', {id: shipment.delivery_service_zero_mile.id})" target="_blank">
                            {{shipment.delivery_service_zero_mile.name}}
                        </a>
                    </p>
                    <p>
                        <span class="font-weight-bold">Логистический оператор для последней мили:</span>
                        <a :href="getRoute('deliveryService.detail', {id: shipment.delivery_service.id})" target="_blank">
                            {{shipment.delivery_service.name}}
                        </a>
                    </p>
                </div>
            </b-row>
            <b-row>
                <div class="col-sm-6">
                    <p><span class="font-weight-bold">PSD:</span> {{shipment.psd}}</p>
                    <p v-if="shipment.fsd"><span class="font-weight-bold">FSD:</span> {{shipment.fsd}}</p>
                </div>
                <div class="col-sm-6">
                    <span class="font-weight-bold">PDD:</span>
                    {{shipment.delivery_pdd}}
                </div>
            </b-row>
            <b-row class="mt-2 border-top">
                <div class="col-sm-6">
                    <span class="font-weight-bold">Стоимость товаров:</span> {{preparePrice(shipment.cost)}} руб.
                </div>
            </b-row>
            <b-row class="mt-2 border-top">
                <div class="col-sm-6">
                    <span class="font-weight-bold">Габариты (ДxШxВ):</span> {{shipment.length|integer}}x{{shipment.width|integer}}x{{shipment.height|integer}} мм
                </div>
                <div class="col-sm-6">
                    <span class="font-weight-bold">Вес:</span> {{shipment.weight}} г
                </div>
            </b-row>
            <b-row>
                <div class="col-sm-6">
                    <span class="font-weight-bold">Кол-во коробок:</span>
                    {{shipment.packages.length}} шт
                </div>
                <div class="col-sm-6">
                    <span class="font-weight-bold">Кол-во товаров:</span> {{shipment.product_qty}} шт.
                </div>
            </b-row>
            <b-row v-if="shipment.assembly_problem_comment">
                <b-col>
                    <span class="font-weight-bold">Комментарий мерчанта о проблеме со сборкой отправления:</span>
                    {{shipment.assembly_problem_comment}}
                </b-col>
            </b-row>

            <b-card class="mt-4">
                <b-table-simple hover small caption-top responsive>
                <b-thead>
                    <b-tr>
                        <b-th>Фото</b-th>
                        <b-th class="with-small">Название <small>Артикул</small></b-th>
                        <b-th class="with-small">Категория <small>Бренд</small></b-th>
                        <b-th>Количество</b-th>
                        <b-th>Цена без скидки</b-th>
                        <b-th>Скидка</b-th>
                        <b-th>Цена со скидкой</b-th>
                        <th></th>
                    </b-tr>
                </b-thead>
                <b-tbody>
                    <template v-if="shipment.packages.length > 0">
                        <template v-for="(pack, key) in shipment.packages">
                            <tr>
                                <b-td colspan="8">
                                    Коробка #{{ key+1 }}
                                </b-td>
                                <b-td>
                                    <fa-icon icon="pencil-alt" title="Изменить" class="cursor-pointer mr-3"
                                             @click="editPackage(item.id)"
                                    />
                                    <fa-icon icon="times" title="Удалить" class="cursor-pointer"
                                             @click="deletePackage(item.id)"
                                    />
                                </b-td>
                            </tr>
                            <tr v-for="(item, key) in pack.items">
                                <b-td><img :src="productPhoto(item.basketItem.product)" class="preview" :alt="item.name"
                                         v-if="item.basketItem.product.mainImage"></b-td>
                                <b-td class="with-small">
                                    <a :href="getRoute('products.detail', {id: item.basketItem.product.id})" target="_blank">
                                        {{ item.basketItem.name }}
                                    </a>
                                    <small>{{ item.basketItem.product.vendor_code }}</small>
                                </b-td>
                                <b-td class="with-small">
                                    {{ item.basketItem.product && item.basketItem.product.category ? item.basketItem.product.category.name : '' }}
                                    <small>{{ item.basketItem.product && item.basketItem.product.category ? item.basketItem.product.brand.name : '' }}</small>
                                </b-td>
                                <b-td>{{ item.qty | integer }}</b-td>
                                <b-td>{{ preparePrice(item.cost)}} руб.</b-td>
                                <b-td>{{ preparePrice(item.cost - item.price) }} руб.</b-td>
                                <b-td>{{ preparePrice(item.price) }} руб.</b-td>
                                <b-td></b-td>
                            </tr>
                        </template>
                    </template>
                    <template v-else>
                        <tr v-for="(item, key) in shipment.basketItems">
                            <b-td><img :src="productPhoto(item.product)" class="preview" :alt="item.name" v-if="item.product.mainImage"></b-td>
                            <b-td class="with-small">
                                <a :href="getRoute('products.detail', {id: item.product.id})" target="_blank">
                                    {{ item.name }}
                                </a>
                                <small>{{ item.product.vendor_code }}</small>
                            </b-td>
                            <b-td class="with-small">
                                {{ item.product && item.product.category ? item.product.category.name : ''}}
                                <small>{{ item.product && item.product.brand ? item.product.brand.name : ''}}</small>
                            </b-td>
                            <b-td>{{ item.qty | integer }}</b-td>
                            <b-td>{{ preparePrice(item.cost) }} руб.</b-td>
                            <b-td>{{ preparePrice((item.cost - item.price)) }} руб.</b-td>
                            <b-td>{{ preparePrice(item.price) }} руб.</b-td>
                            <b-td></b-td>
                        </tr>
                    </template>
                </b-tbody>
            </b-table-simple>
            </b-card>
        </b-card>

        <modal-shipment-edit :model-shipment.sync="selectedShipment" :model-order.sync="order" v-if="Object.values(selectedShipment).length > 0"/>
    </div>
</template>
<script>
    import ModalShipmentEdit from "./forms/modal-shipment-edit.vue";
    import Services from "../../../../../scripts/services/services";

    export default {
        props: {
            model: {},
        },
        components: {
            ModalShipmentEdit,
        },
        data() {
            return {
                selectedShipment: {},
            }
        },
        methods: {
            productPhoto(product) {
                return '/files/compressed/' + product.mainImage.file_id + '/50/50/webp';
            },
            isAssembledStatus(shipment) {
                return shipment.status.id === 6;
            },
            getBarcodesTitle(shipment) {
                return this.canGetBarcodes(shipment) ? '' :
                    'Получить штрихкоды Вы сможете когда будет создано задание на доставку у логистического оператора';
            },
            canGetBarcodes(shipment) {
                return shipment.delivery_xml_id;
            },
            canGetCdekReceipt(shipment) {
                return shipment.delivery.xml_id && shipment.delivery.delivery_service === 3;
            },
            canMarkAsProblem(shipment) {
                return shipment.is_problem;
            },
            canMarkAsNonProblem(shipment) {
                return !shipment.is_problem;
            },
            canCancelShipment(shipment) {
                return shipment.status.id < 26 && !shipment.is_canceled
            },
            markAsProblem(shipment) {
                let errorMessage = 'Ошибка при изменении отправления';

                Services.showLoader();
                Services.net().put(this.getRoute('orders.detail.shipments.markAsProblem', {id: order.id, shipmentId: shipment.id})).then(data => {
                    if (data.order) {
                        this.order = data.order;
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
            markAsNonProblem(shipment) {
                let errorMessage = 'Ошибка при изменении отправления';

                Services.showLoader();
                Services.net().put(this.getRoute('orders.detail.shipments.markAsNonProblem', {id: order.id, shipmentId: shipment.id})).then(data => {
                    if (data.order) {
                        this.order = data.order;
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
            cancelShipment(shipment) {
                let errorMessage = 'Ошибка при отмене отправления';

                Services.showLoader();
                Services.net().put(this.getRoute('orders.detail.shipments.cancel', {id: order.id, shipmentId: shipment.id})).then(data => {
                    if (data.order) {
                        this.order = data.order;
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
                return shipment.status.id < 7 && !shipment.is_canceled;
            },
            editShipment(shipment) {
                this.selectedShipment = shipment;
                this.$bvModal.show('modal-shipment-edit');
            }
        },
        computed: {
            order: {
                get() {return this.model},
                set(value) {this.$emit('update:model', value)},
            },
        }
    }
</script>

<style scoped>
    .with-small small {
        display: block;
        color: gray;
        line-height: 1rem;
        overflow: hidden;
    }
    .preview {
        height: 50px;
        border-radius: 5px;
    }
</style>
