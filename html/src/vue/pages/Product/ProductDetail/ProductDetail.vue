<template>
    <layout-main back>
        <div class="d-flex flex-wrap align-items-stretch justify-content-start product-header">
            <div class="shadow flex-grow-3 mr-3 mt-3">
                <h2>{{ product.name }}</h2>
                <div style="height: 40px" v-if="canView(blocks.products)">
                    <span class="badge" :class="approvalClass(product.approval_status)">{{ options.approval[product.approval_status] || 'N/A' }}</span>
                    <template v-if="!isRejectApprovalStatus && !isApprovedApprovalStatus && canUpdate(blocks.products)">
                        <button class="btn btn-primary" @click="changeProductApproveStatus(5)">Согласовать</button>
                        <button class="btn btn-primary" @click="openModal('productReject')">Отклонить</button>
                    </template>
                </div>

                <p class="text-secondary">ID: <span class="float-right">{{ product.id }}</span></p>
                <p class="text-secondary">Артикул: <span class="float-right">{{ product.vendor_code }}</span></p>
                <p class="text-secondary">Текущая цена товара на витрине: <span class="float-right">{{ product.currentOffer.price }} ₽</span></p>
                <p class="text-secondary">Текущий остаток товара на витрине: <span class="float-right">{{ product.currentOffer.qty }} шт.</span></p>
                <p class="text-secondary">Дата создания товара: <span class="float-right">{{ product.created_at }}</span></p>
                <p class="text-secondary">Дата последнего обновления товара: <span class="float-right">{{ product.updated_at }}</span></p>
                <div style="margin-top: 10px" v-if="canUpdate(blocks.products)">
                    <button @click="openModal('productStatusEdit')" class="btn btn-outline-dark md-3">
                        Изменить статус
                    </button>
                </div>
            </div>
            <div class="shadow flex-grow-2 mr-3 mt-3">
                <img :src="mainImage.url" :alt="product.name">
            </div>
        </div>
        <v-tabs v-if="canView(blocks.products)" :current="nav.currentTab" :items="nav.tabs" @nav="tab => nav.currentTab = tab"></v-tabs>
        <property-tab
                v-if="nav.currentTab === 'props'&& canView(blocks.products)"
                :product="product"
                :propertyValues="this.properties"
                :badges="badges"
                :options="options"
                @onSave="refresh"
        ></property-tab>
        <delivery-tab
                v-if="nav.currentTab === 'delivery'"
                :product="product"
                :options="options"
                @onSave="refresh"
        ></delivery-tab>
        <content-tab
                v-if="nav.currentTab === 'content'"
                :images="images"
                :product="product"
                @onSave="refresh"
            ></content-tab>
        <categories-tab
                v-if="nav.currentTab === 'categories'"
                :product="product"
                :propertyValues="this.properties"
                :options="options"
        ></categories-tab>
        <offers-tab
                v-if="nav.currentTab === 'offers'"
                :offers="this.product.offers"
                :current-offer="this.product.currentOffer"
                :options="this.options"
        ></offers-tab>
        <orders-tab
                v-if="nav.currentTab === 'orders'"
                :orders="this.product.orders"
                :allStatuses="this.options.orderStatuses"
                :offersIds="this.product.offersIds"
        ></orders-tab>
        <history-tab
                v-if="nav.currentTab === 'history'"
                :operator="operator"
        ></history-tab>
        <marketing-tab
                v-if="nav.currentTab === 'marketing'"
                :product="product"
                :marketing="options.marketing"
        ></marketing-tab>
        <product-reject-modal
                :product-id="product.id"
                @onSave="refresh"
                modal-name="productReject">
        </product-reject-modal>
        <product-status-modal
                :currentStatus ="this.product.approval_status"
                :approval-options="this.options.approval"
                :productId = "this.product.id"
                @onSave="refresh"
                modal-name="productStatusEdit">
        </product-status-modal>
    </layout-main>
</template>

<script>

    import VTabs from '../../../components/tabs/tabs.vue';

    import PropertyTab from './components/property-tab.vue';
    import DeliveryTab from './components/delivery-tab.vue';
    import ContentTab from './components/content-tab.vue';
    import CategoriesTab from './components/categories-tab.vue';
    import OffersTab from './components/offers-tab.vue';
    import OrdersTab from './components/orders-tab.vue';
    import HistoryTab from './components/history-tab.vue';
    import MarketingTab from './components/marketing-tab.vue';
    import ProductRejectModal from './components/product-reject-modal.vue';
    import ProductStatusModal from './components/product-status-modal.vue';
    import Services from '../../../../scripts/services/services';
    import modalMixin from '../../../mixins/modal';

    export default {
    components: {
        VTabs,

        PropertyTab,
        DeliveryTab,
        ContentTab,
        CategoriesTab,
        OffersTab,
        OrdersTab,
        HistoryTab,
        MarketingTab,
        ProductRejectModal,
        ProductStatusModal,
    },
    mixins: [modalMixin],
    props: {
        iProduct: {},
        iImages: {},
        iBadges: {},
        iProperties: {},
        options: {}
    },
    data() {
        return {
            product: this.iProduct,
            images: this.iImages.filter(image => image.type !== this.$store.state.layout.productImageTypes.catalog),
            badges: this.iBadges,
            properties: this.iProperties,

            nav: {
                currentTab: 'props',
                tabs: [
                    {value: 'props', text: 'Мастер-данные'},
                    {value: 'delivery', text: 'Хранение и доставка'},
                    {value: 'content', text: 'Контент'},
                    {value: 'categories', text: 'Категории'},
                    {value: 'offers', text: 'Предложения'},
                    {value: 'orders', text: 'В заказах'},
                    {value: 'history', text: 'История'},
                    {value: 'marketing', text: 'Маркетинг'},
                ]
            }
        };
    },

    methods: {
        refresh() {
            Services.showLoader();
            Services.net().get(this.getRoute('products.detailData', {id: this.product.id})).then((data)=> {
                this.product = data.product;
                this.images = data.images.filter(image => image.type !== this.productImageType.catalog);
                this.properties = data.properties;
                this.options.availableProperties = data.availableProperties;
                this.options.directoryValues = data.directoryValues;
                this.options.availableBadges = data.availableBadges;
            }).finally(() => {
                Services.hideLoader();
            });
        },
        changeProductApproveStatus(statusId) {
            let errorMessage = 'Ошибка при изменении статуса согласования товара.';

            Services.net().put(this.getRoute('products.changeApproveStatus', {id: this.product.id}), null,
                {'approval_status': statusId}).then(data => {
                this.refresh();
            }, () => {
                this.showMessageBox({title: 'Ошибка', text: errorMessage});
            });
        },
        approvalClass(approval_status) {
            switch (approval_status) {
                case 1: return 'badge-dark';
                case 2: return 'badge-info';
                case 3: return 'badge-warning';
                case 4: return 'badge-danger';
                case 5: return 'badge-success';
            }
        },
        isApprovalStatus(statusId) {
            return this.product.approval_status === statusId;
        },
    },
    computed: {
        title() {
            return this.$store.state.title;
        },
        mainImage() {
            let mainImages = this.images.filter(image => image.type === this.productImageType.gallery);

            return mainImages.length > 0 ? mainImages[0] : {
                id: 0,
                url: '//placehold.it/150x150?text=No+image'
            };
        },
        isRejectApprovalStatus() {
            return this.isApprovalStatus(4);
        },
        isApprovedApprovalStatus() {
            return this.isApprovalStatus(5);
        },
    },
};
</script>
<style scoped>
    .product-header {
        min-height: 200px;
    }
    .product-header > div {
        padding: 16px;
    }
    .product-header img {
        max-height: calc( 200px - 16px * 2 );
    }
    .product-header p {
        margin: 0;
        padding: 0;
    }
    .measure {
        width: 30px;
        margin-left: 10px;
    }
</style>
