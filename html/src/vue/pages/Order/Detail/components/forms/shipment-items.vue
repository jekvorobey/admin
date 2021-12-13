<template>
    <b-card>
        <b-row class="mb-3">
            <b-col>
                <div class="float-right" v-if="canUpdate(blocks.orders)">
                    <button class="btn btn-primary" v-if="isAwaitingConfirmationStatus && !isCancel && !isProblem"
                            @click="changeShipmentStatus(shipmentStatuses.assembling.id)">
                        Все товары в наличии
                    </button>
                    <button class="btn btn-primary" v-if="isAssemblingStatus && !isCancel && !isProblem"
                            @click="changeShipmentStatus(shipmentStatuses.assembled.id)">
                        Собрано
                    </button>
                    <template v-if="!isProblem && !isCancel && isAssemblingStatus && !isAssembled">
                        <button class="btn btn-primary" @click="addShipmentPackage(shipment)">
                            + Добавить коробку
                        </button>
                        <modal-add-shipment-package :model-shipment.sync="selectedShipment" :model-order.sync="order"
                                                    v-if="Object.values(selectedShipment).length > 0"/>
                    </template>
                </div>
            </b-col>
        </b-row>
        <b-table-simple hover small caption-top responsive>
            <b-thead>
                <b-tr>
                    <b-th v-if="canEdit && hasShipmentPackages && !isAssembled && !shipment.is_problem">
                        <input type="checkbox" id="select-all-page-shipments" v-model="isSelectAllBasketItem"
                               @click="selectAllBasketItems()">
                        <label for="select-all-page-shipments" class="mb-0">Все</label>
                    </b-th>
                    <b-th v-if="returnable && order.status.id === orderStatuses.done.id && !shipment.is_canceled && shipment.packages.length">Возврат</b-th>
                    <b-th>Фото</b-th>
                    <b-th class="with-small">Название <small>ID</small><small>Артикул</small></b-th>
                    <b-th class="with-small">Категория <small>Бренд</small></b-th>
                    <b-th class="with-small">Количество <small>Вес 1 шт</small><small>ДxШxВ 1 шт</small></b-th>
                    <b-th>Цена за единицу без скидки
                        <fa-icon icon="question-circle"
                                 v-b-popover.hover="tooltipUnitCostHelp"></fa-icon>
                    </b-th>
                    <b-th>Скидка за единицу
                        <fa-icon icon="question-circle"
                                 v-b-popover.hover="tooltipUnitDiscountHelp"></fa-icon>
                    </b-th>
                    <b-th>Цена за единицу со скидкой
                        <fa-icon icon="question-circle"
                                 v-b-popover.hover="tooltipUnitPriceHelp"></fa-icon>
                    </b-th>
                    <b-th>Сумма без скидки
                        <fa-icon icon="question-circle"
                                 v-b-popover.hover="tooltipCostHelp"></fa-icon>
                    </b-th>
                    <b-th>Скидка
                        <fa-icon icon="question-circle" v-b-popover.hover="tooltipDiscountHelp"></fa-icon>
                    </b-th>
                    <b-th>Сумма со скидкой
                        <fa-icon icon="question-circle"
                                 v-b-popover.hover="tooltipPriceHelp"></fa-icon>
                    </b-th>
                    <b-th v-if="canEdit"></b-th>
                </b-tr>
            </b-thead>
            <b-tbody>
                <template v-if="shipment.nonPackedBasketItems">
                    <tr v-for="basketItem in shipment.nonPackedBasketItems">
                        <b-td v-if="canEdit && hasShipmentPackages && !isAssembled && !shipment.is_problem">
                            <input type="checkbox" value="true" class="shipment-select" :value="basketItem.id"
                                   v-model="selectedBasketItemIds"
                                   v-if="!shipment.is_problem && isAssemblingStatus && hasShipmentPackages">
                        </b-td>
                        <b-td><img :src="productPhoto(basketItem.product)" class="preview" :alt="basketItem.name"
                                   v-if="basketItem.product.mainImage"></b-td>
                        <b-td class="with-small">
                            <a :href="getRoute('products.detail', {id: basketItem.product.id})" target="_blank">
                                {{ basketItem.name }}
                            </a>
                            <small>{{ basketItem.product.id }}</small>
                            <small>{{ basketItem.product ? basketItem.product.vendor_code : '' }}</small>
                        </b-td>
                        <b-td class="with-small">
                            {{ basketItem.product && basketItem.product.category ? basketItem.product.category.name : '' }}
                            <small>{{ basketItem.product && basketItem.product.brand ? basketItem.product.brand.name : '' }}</small>
                        </b-td>
                        <b-td class="with-small">
                            {{ basketItem.qty | integer }} шт
                            <small> {{ basketItem.product.weight }} г</small>
                            <small> {{ basketItem.product.length }} x {{ basketItem.product.width }} x
                                {{ basketItem.product.height }} мм</small>
                        </b-td>
                        <b-td>{{ preparePrice(basketItem.cost / basketItem.qty_original) }} руб</b-td>
                        <b-td>{{ preparePrice((basketItem.cost - basketItem.price) / basketItem.qty_original) }} руб</b-td>
                        <b-td>{{ preparePrice(basketItem.price / basketItem.qty_original) }} руб</b-td>
                        <b-td>{{ preparePrice(basketItem.qty * basketItem.cost / basketItem.qty_original) }} руб</b-td>
                        <b-td>{{ preparePrice(basketItem.cost - basketItem.price) }} руб
                        </b-td>
                        <b-td>{{ preparePrice(basketItem.qty * basketItem.price / basketItem.qty_original) }} руб</b-td>
                        <b-td v-if="canEdit"></b-td>
                    </tr>
                </template>
                <template v-if="shipment.packages.length > 0">
                    <template v-for="(shipmentPackage, pKey) in shipment.packages">
                        <b-tr>
                            <b-td v-if="canEdit" class="d-lg-none">
                                <div class="float-left">
                                    <fa-icon icon="times" title="Удалить коробку" class="cursor-pointer"
                                             @click="deleteShipmentPackage(shipmentPackage.id)"
                                             v-if="!shipment.is_problem && isAssemblingStatus"></fa-icon>
                                </div>
                            </b-td>
                            <b-td :colspan="canEdit ? 10 : 9">
                                <b-row>
                                    <div class="col-sm-9">
                                        <b>Коробка #{{ pKey + 1 }} (ID: {{ shipmentPackage.id }},
                                            {{ shipmentPackage.package.name }}, вес брутто {{ shipmentPackage.weight }}
                                            г, вес пустой коробки {{ shipmentPackage.wrapper_weight }} г)</b>
                                    </div>
                                    <div class="col-sm-3" v-if="canUpdate(blocks.orders)">
                                        <button class="btn btn-secondary"
                                                v-if="!shipment.is_problem && isAssemblingStatus && selectedBasketItemIds.length"
                                                @click="addShipmentPackageItems(shipmentPackage)">
                                            <fa-icon icon="box"></fa-icon>
                                            Добавить в коробку
                                        </button>
                                        <modal-add-shipment-package-items :model-shipment.sync="shipment"
                                                                          :model-order.sync="order"
                                                                          :shipment-package.sync="selectedShipmentPackage"
                                                                          :shipment-package-num="pKey+1"
                                                                          :basket-items.sync="selectedBasketItems"
                                                                          @onSave="onShipmentPackageItemsAdd"
                                                                          v-if="Object.values(selectedShipmentPackage).length > 0 && Object.values(selectedBasketItems).length > 0"/>
                                    </div>
                                </b-row>
                            </b-td>
                            <b-td v-if="canEdit">
                                <div class="float-right">
                                    <fa-icon icon="times" title="Удалить коробку" class="cursor-pointer"
                                             @click="deleteShipmentPackage(shipmentPackage.id)"
                                             v-if="!shipment.is_problem && isAssemblingStatus"></fa-icon>
                                </div>
                            </b-td>
                        </b-tr>
                        <b-tr v-for="(item, key) in shipmentPackage.items" v-bind:key="item.id">
                            <b-td v-if="canEdit" class="d-lg-none">
                                <div v-if="!shipment.is_problem && isAssemblingStatus" class="float-right">
                                    <fa-icon icon="pencil-alt" title="Изменить кол-во" class="cursor-pointer"
                                             @click="editShipmentPackageItem(shipmentPackage, item)">
                                    </fa-icon>
                                    <br>
                                    <fa-icon icon="times" title="Удалить из коробки" class="cursor-pointer"
                                             @click="deleteShipmentPackageItem(shipmentPackage.id, item.basket_item_id)">
                                    </fa-icon>
                                </div>
                            </b-td>
                          <b-td v-if="returnable && order.status.id === orderStatuses.done.id && !shipment.is_canceled">
                            <input type="checkbox"
                                   class="shipment-select"
                                   @change="$emit('toggleBasketItemReturn', item.basket_item_id)"
                                   :checked="basketItemsToReturn.includes(item.basket_item_id)"
                                   :disabled="item.basketItem.is_returned"
                            />
                          </b-td>
                            <b-td><img :src="productPhoto(item.basketItem.product)" class="preview" :alt="item.name"
                                       v-if="item.basketItem.product.mainImage"></b-td>
                            <b-td class="with-small">
                                <a :href="getRoute('products.detail', {id: item.basketItem.product.id})"
                                   target="_blank">
                                    {{ item.basketItem.name }}
                                </a>
                                <small>{{ item.basketItem.product.id }}</small>
                                <small>{{ item.basketItem.product.vendor_code }}</small>
                            </b-td>
                            <b-td class="with-small">
                                {{
                                    item.basketItem.product && item.basketItem.product.category ? item.basketItem.product.category.name : ''
                                }}
                                <small>{{
                                        item.basketItem.product && item.basketItem.product.category ? item.basketItem.product.brand.name : ''
                                    }}</small>
                            </b-td>
                            <b-td class="with-small">
                                {{ item.qty | integer }} шт
                                <small> {{ item.basketItem.product.weight }} г</small>
                                <small> {{ item.basketItem.product.length }} x {{ item.basketItem.product.width }} x
                                    {{ item.basketItem.product.height }} мм</small>
                            </b-td>
                            <b-td>{{ preparePrice(item.basketItem.cost / item.basketItem.qty) }} руб</b-td>
                            <b-td>{{ preparePrice((item.basketItem.cost - item.basketItem.price) / item.basketItem.qty) }} руб</b-td>
                            <b-td>{{ preparePrice(item.basketItem.price / item.basketItem.qty) }} руб</b-td>
                            <b-td>{{ preparePrice(item.qty * item.basketItem.cost / item.basketItem.qty) }} руб</b-td>
                            <b-td>{{ preparePrice((item.basketItem.cost - item.basketItem.price)) }} руб
                            </b-td>
                            <b-td>{{ preparePrice(item.qty * item.basketItem.price / item.basketItem.qty) }} руб</b-td>
                            <b-td v-if="canEdit">
                                <div v-if="!shipment.is_problem && isAssemblingStatus" class="float-right">
                                    <fa-icon icon="pencil-alt" title="Изменить кол-во" class="cursor-pointer"
                                             @click="editShipmentPackageItem(shipmentPackage, item)">
                                    </fa-icon>
                                    <br>
                                    <modal-edit-shipment-package-item :model-shipment.sync="shipment"
                                                                      :model-order.sync="order"
                                                                      :shipment-package.sync="selectedShipmentPackage"
                                                                      :shipment-package-num="key+1"
                                                                      :shipment-item.sync="selectedShipmentItem"
                                                                      :max-qty="selectedMaxQty"
                                                                      @onSave="onShipmentPackageItemEdit"
                                                                      v-if="Object.values(selectedShipmentItem).length > 0"/>

                                    <fa-icon icon="times" title="Удалить из коробки" class="cursor-pointer"
                                             @click="deleteShipmentPackageItem(shipmentPackage.id, item.basket_item_id)">
                                    </fa-icon>
                                </div>
                            </b-td>
                        </b-tr>
                        <b-tr v-if="!shipmentPackage.items || !shipmentPackage.items.length">
                            <td :colspan="canEdit ? 12 : 10">Коробка пуста</td>
                        </b-tr>
                    </template>
                </template>
            </b-tbody>
        </b-table-simple>
    </b-card>
</template>

<script>
import Services from '../../../../../../scripts/services/services.js';
import ModalAddShipmentPackage from './modal-add-shipment-package.vue';
import ModalAddShipmentPackageItems from './modal-add-shipment-package-items.vue';
import ModalEditShipmentPackageItem from './modal-edit-shipment-package-item.vue';

export default {
    name: "shipment-items",
    components: {
        ModalEditShipmentPackageItem,
        ModalAddShipmentPackageItems,
        ModalAddShipmentPackage,
    },
    props: {
        modelShipment: {
            type: Object,
        },
        modelOrder: {
            type: Object,
        },
        withEdit: {
            type: Boolean,
            default: false,
        },
        returnable: {
          type: Boolean,
          default: false
        },
        basketItemsToReturn: {
            type: Array,
        },
    },
    data() {
        return {
            selectedBasketItemIds: [],
            selectedShipment: {},
            isSelectAllBasketItem: false,
            selectedShipmentItem: {},
            selectedBasketItem: {},
            selectedMaxQty: 0,
            selectedShipmentPackage: {},
        }
    },
    methods: {
        productPhoto(product) {
            return '/files/compressed/' + product.mainImage.file_id + '/50/50/webp';
        },
        isStatus(statusId) {
            return this.shipment.status && this.shipment.status.id === statusId;
        },
        addShipmentPackageItems(shipmentPackage) {
            this.selectedShipmentPackage = shipmentPackage;

            this.$bvModal.show('modal-add-shipment-package-items');
        },
        selectAllBasketItems() {
            this.isSelectAllBasketItem = !this.isSelectAllBasketItem;
            this.selectedBasketItemIds = [];
            if (this.isSelectAllBasketItem) {
                for (let [id, basketItem] of Object.entries(this.shipment.nonPackedBasketItems)) {
                    this.selectedBasketItemIds.push(basketItem.id);
                }
            }
        },
        addShipmentPackage(shipment) {
            this.selectedShipment = shipment;
            this.$bvModal.show('modal-add-shipment-package');
        },
        changeShipmentStatus(statusId) {
            Services.showLoader();
            Services.net().put(
                this.getRoute(
                    'orders.detail.shipments.changeShipmentStatus',
                    {id: this.order.id, shipmentId: this.shipment.id},
                ),
                {},
                {'status': statusId},
            ).then((data) => {
                this.$set(this, 'order', data.order);
                this.$set(this.order, 'shipments', data.order.shipments);

                Services.msg('Изменения сохранены');
            }).finally(() => {
                Services.hideLoader();
            });
        },
        deleteShipmentPackage(shipmentPackageId) {
            Services.showLoader();
            Services.net().delete(
                this.getRoute('orders.detail.shipments.deleteShipmentPackage', {
                    id: this.order.id,
                    shipmentId: this.shipment.id,
                    shipmentPackageId: shipmentPackageId,
                }),
                {},
                {}
            )
                .then((data) => {
                    this.$set(this, 'order', data.order);
                    this.$set(this.order, 'shipments', data.order.shipments);
                    Services.msg("Изменения сохранены");
                }).finally(data => {
                Services.hideLoader();
            });
        },
        editShipmentPackageItem(shipmentPackage, shipmentItem) {
            this.selectedShipmentPackage = shipmentPackage;
            this.selectedShipmentItem = shipmentItem;
            this.selectedMaxQty = parseInt(shipmentItem.basketItem.id in this.shipment.nonPackedBasketItems ?
                (this.shipment.nonPackedBasketItems[shipmentItem.basketItem.id].qty + shipmentItem.qty) : shipmentItem.qty);

            this.$bvModal.show('modal-edit-shipment-package-item');
        },
        deleteShipmentPackageItem(shipmentPackageId, basketItemId) {
            Services.showLoader();
            Services.net().delete(
                this.getRoute('orders.detail.shipments.deleteShipmentPackageItem', {
                    id: this.order.id,
                    shipmentId: this.shipment.id,
                    shipmentPackageId: shipmentPackageId,
                    basketItemId: basketItemId,
                }),
                {},
                {}
            )
                .then(data => {
                    this.$set(this, 'order', data.order);
                    this.$set(this.order, 'shipments', data.order.shipments);
                    Services.msg("Изменения сохранены");
                }).finally(data => {
                Services.hideLoader();
            });
        },
        onShipmentPackageItemsAdd() {
            this.selectedShipmentPackage = {};
            this.selectedBasketItemIds = [];
            this.isSelectAllBasketItem = false;
            this.$bvModal.hide('modal-add-shipment-package-items');
        },
        onShipmentPackageItemEdit() {
            this.selectedShipmentPackage = {};
            this.selectedShipmentItem = {};
            this.$bvModal.hide('modal-edit-shipment-package-item');
        }
    },
    computed: {
        order: {
            get() {
                return this.modelOrder
            },
            set(value) {
                this.$emit('update:modelOrder', value)
            },
        },
        shipment: {
            get() {
                return this.modelShipment
            },
            set(value) {
                this.$emit('update:modelShipment', value)
            },
        },
        selectedBasketItems() {
            let selectedBasketItems = {};
            for (let [id, basketItem] of Object.entries(this.shipment.nonPackedBasketItems)) {
                if (this.selectedBasketItemIds.indexOf(basketItem.id) !== -1) {
                    selectedBasketItems[basketItem.id] = basketItem;
                }
            }

            return selectedBasketItems;
        },
        isCancel() {
            return this.shipment.is_canceled;
        },
        isProblem() {
            return this.shipment.is_problem;
        },
        isAwaitingConfirmationStatus() {
            return this.isStatus(this.shipmentStatuses.awaitingConfirmation.id);
        },
        isAssemblingStatus() {
            return this.isStatus(this.shipmentStatuses.assembling.id);
        },
        isAssembled() {
            return Object.keys(this.shipment.nonPackedBasketItems).length === 0;
        },
        hasShipmentPackages() {
            return this.shipment.packages.length > 0;
        },
        tooltipUnitCostHelp() {
            return 'Цена товара без скидки за единицу товара';
        },
        tooltipUnitDiscountHelp() {
            return 'Величина скидки за единицу товара';
        },
        tooltipUnitPriceHelp() {
            return 'Цена товара со всеми скидками за единицу товара';
        },
        tooltipCostHelp() {
            return 'Цена товара без скидки с учётом количества';
        },
        tooltipDiscountHelp() {
            return 'Величина скидки с учётом количества';
        },
        tooltipPriceHelp() {
            return 'Цена товара со всеми скидками с учётом количества';
        },
        canEdit() {
            return this.withEdit && this.canUpdate(this.blocks.orders);
        }
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
