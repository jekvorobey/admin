<template>
    <layout-main back>
        <div class="d-flex flex-wrap align-items-stretch justify-content-start product-header">
            <div class="shadow flex-grow-3 mr-3 mt-3">
                <h2>{{ productGroupPage.name }}</h2>
            </div>
            <div class="shadow flex-grow-2 mr-3 mt-3">
                <img :src="productGroupPage.photo" :alt="productGroupPage.name">
            </div>
        </div>
    </layout-main>
</template>

<script>

import {mapGetters} from "vuex";
import VTabs from '../../../components/tabs/tabs.vue';

import Services from "../../../../scripts/services/services";

export default {
    components: {
        VTabs,
    },
    props: {
        iProductGroupPage: {},
        options: {}
    },
    data() {
        return {
            productGroupPage: this.iProductGroupPage,
        };
    },

    methods: {
        refresh() {
            Services.net().get(this.getRoute('products.detailData', {id: this.product.id}))
                .then((data)=> {
                    this.productGroupPage = data.productGroupPage;
                });
        },
    },
    computed: {
        ...mapGetters(['getRoute']),
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
