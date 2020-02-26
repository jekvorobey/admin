<template>
    <layout-main back>
        <div class="d-flex flex-wrap align-items-stretch justify-content-start product-header">
            <div class="shadow flex-grow-3 mr-3 mt-3">
                <h2>{{ product.name }}</h2>
                <div style="height: 40px">
                    <span class="badge" :class="approvalClass(product.approval_status)">{{ options.approval[product.approval_status] || 'N/A' }}</span>
                    <template v-if="!isRejectApprovalStatus && !isApprovedApprovalStatus">
                        <button class="btn btn-primary" @click="changeProductApproveStatus(5)">Согласовать</button>
                        <button class="btn btn-primary" @click="openModal('productReject')">Отклонить</button>
                    </template>
                </div>

                <p class="text-secondary">Артикул: <span class="float-right">{{ product.vendor_code }}</span></p>

            </div>
            <div class="shadow flex-grow-2 mr-3 mt-3">
                <img :src="mainImage.url" :alt="product.name">
            </div>
        </div>
        <v-tabs :current="nav.currentTab" :items="nav.tabs" @nav="tab => nav.currentTab = tab"></v-tabs>
        <property-tab
                v-if="nav.currentTab === 'props'"
                :product="product"
                :propertyValues="this.properties"
                :options="options"
                @onSave="refresh"
        ></property-tab>
        <content-tab
                v-if="nav.currentTab === 'content'"
                :images="images"
                :product="product"
                @onSave="refresh"
            ></content-tab>
        <history-tab
                v-if="nav.currentTab === 'history'"
                :operator="operator"
        ></history-tab>

        <product-reject-modal
                :product-id="product.id"
                @onSave="refresh"
                modal-name="productReject">
        </product-reject-modal>
    </layout-main>
</template>

<script>

    import {mapGetters} from 'vuex';
    import VTabs from '../../../components/tabs/tabs.vue';

    import PropertyTab from './components/property-tab.vue';
    import ContentTab from './components/content-tab.vue';
    import HistoryTab from './components/history-tab.vue';
    import ProductRejectModal from './components/product-reject-modal.vue';
    import Services from '../../../../scripts/services/services';
    import modalMixin from '../../../mixins/modal';

    export default {
    components: {
        VTabs,

        PropertyTab,
        ContentTab,
        HistoryTab,
        ProductRejectModal,
    },
    mixins: [modalMixin],
    props: {
        iProduct: {},
        iImages: {},
        iProperties: {},
        options: {}
    },
    data() {
        return {
            product: this.iProduct,
            images: this.iImages,
            properties: this.iProperties,

            nav: {
                currentTab: 'props',
                tabs: [
                    {value: 'props', text: 'Мастер-данные'},
                    {value: 'content', text: 'Контент'},
                    {value: 'history', text: 'История'},
                ]
            }
        };
    },

    methods: {
        refresh() {
            Services.net().get(this.getRoute('products.detailData', {id: this.product.id}))
                .then((data)=> {
                    this.product = data.product;
                    this.images = data.images;
                    this.properties = data.properties;
                    this.options.availableProperties = data.availableProperties;
                    this.options.directoryValues = data.directoryValues;
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
        ...mapGetters(['getRoute']),
        title() {
            return this.$store.state.title;
        },
        mainImage() {
            let mainImages = this.images.filter(image => image.type === 1);
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
