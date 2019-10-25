<template>
    <layout-main back>
        <div class="d-flex flex-wrap align-items-stretch justify-content-start product-header">
            <div class="shadow flex-grow-3 mr-3 mt-3">
                <h2>{{ product.name }}</h2>
                <div style="height: 40px">
                    <span class="badge" :class="approvalClass(product.approval_status)">{{ options.approval[product.approval_status] || 'N/A' }}</span>
                    <span class="segment" :class="segmentClass(product.segment)">{{ product.segment }}</span>
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
        <values-tab
                v-if="nav.currentTab === 'values'"
                :product="product"
                :brand="options.brand"
                :category="options.category"
                :stocks="stocks"
                :stores="options.stores"
        ></values-tab>
        <history-tab
                v-if="nav.currentTab === 'history'"
                :operator="operator"
        ></history-tab>
    </layout-main>
</template>

<script>

import {mapGetters} from "vuex";
import VTabs from '../../../components/tabs/tabs.vue';

import PropertyTab from './components/property-tab.vue';
import ContentTab from './components/content-tab.vue';
import ValuesTab from './components/values-tab.vue';
import HistoryTab from './components/history-tab.vue';
import Services from "../../../../scripts/services/services";

export default {
    components: {
        VTabs,

        PropertyTab,
        ContentTab,
        ValuesTab,
        HistoryTab,
    },
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
                    {value: 'values', text: 'Хранение'},
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
        segmentClass(segment) {
            return segment ? `segment-${segment.toLowerCase()}` : '';
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
        }
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
    .segment {
        padding: 5px;
        border-radius: 50%;
        float: right;
        color: white;
        font-weight: bold;
        line-height: 20px;
        width: 32px;
        height: 32px;
        text-align: center;
    }
    .segment-a {
        background: #ffd700;
    }
    .segment-b {
        background: #c0c0c0;
    }
    .segment-c {
        background: #cd7f32;
    }
</style>
