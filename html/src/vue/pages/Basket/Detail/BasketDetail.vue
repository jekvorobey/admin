<template>
    <layout-main back>
        <b-row class="mb-2">
            <b-col class="col-12 col-md-8 mb-2">
                <infopanel :model.sync="basket"/>
            </b-col>
        </b-row>
        <b-row>
            <b-col class="col-12 col-md-12 mb-2">
                <basket-event-items v-if="isEventType" :model.sync="basket"></basket-event-items>
                <basket-product-items v-else :model.sync="basket"></basket-product-items>
            </b-col>
        </b-row>
        <b-row>
            <b-col class="col-12 col-md-12 mb-2">
                <manager-comment :model.sync="basket"/>
            </b-col>
        </b-row>
    </layout-main>
</template>

<script>
    import Infopanel from './components/infopanel.vue';
    import BasketProductItems from './components/basket-product-items.vue';
    import BasketEventItems from './components/basket-event-items.vue';
    import ManagerComment from './components/forms/manager-comment.vue';

    export default {
        components: {
            ManagerComment,
            BasketProductItems,
            BasketEventItems,
            Infopanel,
        },
        props: {
            iBasket: {},
        },
        data() {
            return {
                basket: this.iBasket,
            };
        },
        methods: {

        },
        computed: {
            isProductType() {
                return this.basket.type == this.basketTypes.product;
            },
            isEventType() {
                return this.basket.type == this.basketTypes.master;
            }
        },
    };
</script>
