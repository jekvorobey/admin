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
                        <button class="btn btn-primary" v-if="isAllProductsAvailableStatus && !isAssembled"
                                @click="openModal('addShipmentPackage')">
                            + Добавить коробку
                        </button>
                    </div>
                </div>
            </div>
            <table class="table table-condensed">
                <thead>
                <tr>
                    <th>
                        <template v-if="hasShipmentPackages && !isAssembled">
                            <input type="checkbox" id="select-all-page-shipments" v-model="isSelectAllBasketItem" @click="selectAllBasketItems()">
                            <label for="select-all-page-shipments" class="mb-0">Все</label>
                        </template>
                    </th>
                    <th>ID</th>
                    <th>Фото</th>
                    <th class="with-small">Название <small>Артикул</small></th>
                    <th class="with-small">Категория <small>Бренд</small></th>
                    <th>Количество</th>
                    <th>Цена</th>
                    <th>Скидка</th>
                    <th>Стоимость</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <!--Нераспределенные по коробкам товары отправления-->
                <tr v-for="basketItem in basketItems">
                    <td>
                        <input type="checkbox" value="true" class="shipment-select" :value="basketItem.id"
                               v-model="selectedBasketItemIds" v-if="hasShipmentPackages">
                    </td>
                    <td>{{ basketItem.id }}</td>
                    <td><img :src="product(basketItem.id).photo" class="preview" :alt="product(basketItem.id).name"
                             v-if="product(basketItem.id).photo"></td>
                    <td class="with-small">
                        <a :href="getRoute('product.edit', {id: product(basketItem.id).id})">{{ basketItem.name }}</a>
                        <small>{{ product(basketItem.id).vendor_code }}</small>
                    </td>
                    <td class="with-small">
                        {{ product(basketItem.id).category.name }}
                        <small>{{ product(basketItem.id).brand.name }}</small>
                    </td>
                    <td>{{ basketItem.qty | integer }}</td>
                    <td>{{ basketItem.price }}</td>
                    <td>{{ basketItem.cost - basketItem.price }}</td>
                    <td>{{ basketItem.cost }}</td>
                    <td></td>
                </tr>
                <!--Распределенные по коробкам товары отправления-->
                <template v-for="(shipmentPackage, number) in shipment.packages">
                    <!--Строка с информацией о коробке-->
                    <tr>
                        <td colspan="4">Коробка {{ number + 1 }} ({{ packages[shipmentPackage.package_id].name }})</td>
                        <td colspan="5">
                            <button class="btn btn-secondary" v-if="selectedBasketItemIds.length"
                                    @click="addShipmentPackageItems(shipmentPackage.id)">
                                <fa-icon icon="box"></fa-icon> Добавить в коробку
                            </button>
                        </td>
                        <td>
                            <fa-icon icon="times" title="Удалить коробку" class="cursor-pointer float-right"
                                     @click="deleteShipmentPackage(shipmentPackage.id)" v-if="isAllProductsAvailableStatus"></fa-icon>
                        </td>
                    </tr>
                    <!--Товары коробки-->
                    <tr v-for="item in shipmentPackage.items">
                        <td></td>
                        <td>{{ item.basket_item_id }}</td>
                        <td><img :src="product(item.basket_item_id).photo" class="preview" :alt="product(item.basket_item_id).name"
                                 v-if="product(item.basket_item_id).photo"></td>
                        <td class="with-small">
                            <a :href="getRoute('product.edit', {id: product(item.basket_item_id).id})">
                                {{ shipment.basketItems[item.basket_item_id].name }}
                            </a>
                            <small>{{ product(item.basket_item_id).vendor_code }}</small>
                        </td>
                        <td class="with-small">
                            {{ product(item.basket_item_id).category.name }}
                            <small>{{ product(item.basket_item_id).brand.name }}</small>
                        </td>
                        <td>{{ item.qty | integer }}</td>
                        <td>{{ shipment.basketItems[item.basket_item_id].price }}</td>
                        <td>N/A</td>
                        <td>N/A</td>
                        <td>
                            <fa-icon icon="pencil-alt" title="Изменить кол-во" class="cursor-pointer mr-3"
                                     @click="editShipmentPackageItem(shipmentPackage.id, item.basket_item_id, item.qty)"
                                     v-if="isAllProductsAvailableStatus">
                            </fa-icon>

                            <fa-icon icon="times" title="Удалить из коробки" class="cursor-pointer"
                                     @click="deleteShipmentPackageItem(shipmentPackage.id, item.basket_item_id)"
                                     v-if="isAllProductsAvailableStatus"></fa-icon>
                        </td>
                    </tr>
                    <tr v-if="!shipmentPackage.items">
                        <td colspan="12">Коробка пуста</td>
                    </tr>
                </template>
                </tbody>
            </table>
        </div>

        <transition name="modal">
            <modal :close="closeModal" v-if="isModalOpen('addShipmentPackage')">
                <div slot="header">
                    Добавление новой коробки
                </div>
                <div slot="body">
                    <package-form
                            :shipment="shipment"
                            :packages="packages"
                            @onSave="onChange"
                    ></package-form>
                </div>
            </modal>
        </transition>

        <transition name="modal">
            <modal :close="closeModal" v-if="isModalOpen('addShipmentPackageItems')">
                <div slot="header">
                    Добавление товаров в коробку
                </div>
                <div slot="body">
                    <add-shipment-package-items-form
                            :shipment="shipment"
                            :basket-items="selectedBasketItem"
                            :shipment-package-id="selectedShipmentPackageId"
                            @onSave="onShipmentPackageItemsAdd"
                    ></add-shipment-package-items-form>
                </div>
            </modal>
        </transition>

        <transition name="modal">
            <modal :close="closeModal" v-if="isModalOpen('editShipmentPackageItem')">
                <div slot="header">
                    Редактирование количества товара
                </div>
                <div slot="body">
                    <edit-shipment-package-item-form
                            :shipment="shipment"
                            :basket-item-id="selectedBasketItemId"
                            :qty="selectedQty"
                            :shipment-package-id="selectedShipmentPackageId"
                            @onSave="onShipmentPackageItemEdit"
                    ></edit-shipment-package-item-form>
                </div>
            </modal>
        </transition>
    </div>
</template>

<script>
    import Dropdown from '../../../../../components/dropdown/dropdown.vue';
    import modal from '../../../../../components/controls/modal/modal.vue';
    import VSelect from '../../../../../components/controls/VSelect/VSelect.vue';
    import PackageForm from './forms/package-form.vue';
    import AddShipmentPackageItemsForm from './forms/add-shipment-package-items-form.vue';
    import EditShipmentPackageItemForm from './forms/edit-shipment-package-item-form.vue';

    import modalMixin from '../../../../../mixins/modal';
    import Services from '../../../../../../scripts/services/services';

    export default {
        props: [
            'shipment',
            'packages',
        ],
        components: {
            Dropdown,
            VSelect,
            modal,
            PackageForm,
            AddShipmentPackageItemsForm,
            EditShipmentPackageItemForm,
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
            product(basketItemId) {
                return this.shipment.products[basketItemId];
            },
            selectAllBasketItems() {
                this.isSelectAllBasketItem = !this.isSelectAllBasketItem;
                this.selectedBasketItemIds = [];
                if(this.isSelectAllBasketItem){
                    for (let [id, basketItem] of Object.entries(this.basketItems)) {
                        this.selectedBasketItemIds.push(basketItem.id);
                    }
                }
            },
            deleteShipmentPackage(shipmentPackageId) {
                Services.net().delete(
                    this.getRoute('shipment.deleteShipmentPackage', {
                        id: this.shipment.id,
                        shipmentPackageId: shipmentPackageId,
                    }),
                    {},
                    {}
                )
                    .then(result => {
                        this.onChange(result);
                    });
            },
            addShipmentPackageItems(shipmentPackageId) {
                this.selectedShipmentPackageId = shipmentPackageId;

                this.openModal('addShipmentPackageItems');
            },
            editShipmentPackageItem(shipmentPackageId, basketItemId, qty) {
                this.selectedShipmentPackageId = shipmentPackageId;
                this.selectedBasketItemId = basketItemId;
                this.selectedQty = parseInt(qty);

                this.openModal('editShipmentPackageItem');
            },
            deleteShipmentPackageItem(shipmentPackageId, basketItemId) {
                Services.net().delete(
                    this.getRoute('shipment.deleteShipmentPackageItem', {
                        id: this.shipment.id,
                        shipmentPackageId: shipmentPackageId,
                        basketItemId: basketItemId,
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
            onShipmentPackageItemsAdd(data) {
                this.selectedShipmentPackageId = 0;
                this.selectedBasketItemIds = [];
                this.isSelectAllBasketItem = false;

                this.onChange(data);
            },
            onShipmentPackageItemEdit(data) {
                this.selectedShipmentPackageId = 0;
                this.selectedBasketItemId = 0;

                this.onChange(data);
            }
        },
        computed: {
            basketItems() {
                let basketItems = {};
                for (let [id, basketItem] of Object.entries(this.shipment.basketItems)) {
                    if (basketItem.qty > 0) {
                        basketItems[basketItem.id] = basketItem;
                    }
                }

                return basketItems;
            },
            selectedBasketItem() {
                let selectedBasketItem = {};
                for (let [id, basketItem] of Object.entries(this.basketItems)) {
                    if (this.selectedBasketItemIds.indexOf(basketItem.id) !== -1) {
                        selectedBasketItem[basketItem.id] = basketItem;
                    }
                }

                return selectedBasketItem;
            },
            isAllProductsAvailableStatus() {
                return this.shipment.status.id === 3;
            },
            isAssembled() {
                return Object.entries(this.basketItems).length === 0 && this.basketItems.constructor === Object;
            },
            hasShipmentPackages() {
                return this.shipment.packages.length > 0;
            },
        },
    };
</script>
<style scoped>
    th {
        vertical-align: top !important;
    }
    .with-small small{
        display: block;
        color: gray;
        line-height: 1rem;
        overflow: hidden;
    }
    .preview {
        height: 50px;
        border-radius: 5px;
    }
    .float-right {
        float: right;
    }
</style>
