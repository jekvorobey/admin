<template>
    <div>
        <div class="row">
            <button @click="toggleHiddenDiscounts" class="btn btn-lg btn-light">
                Cкидки мерчанта
                <fa-icon :icon="openedDiscounts ? 'angle-up' : 'angle-down'"></fa-icon>
            </button>
        </div>
        <div class="row">
            <transition name="slide">
                <slide-discounts v-if="openedDiscounts" :id="id" :legalName="legalName"></slide-discounts>
            </transition>
        </div>

        <div class="row mt-3">
            <button @click="toggleHiddenPromoCodes" class="btn btn-lg btn-light">
                Промокоды мерчанта
                <fa-icon :icon="openedPromoCodes ? 'angle-up' : 'angle-down'"></fa-icon>
            </button>
        </div>
        <div class="row">
            <transition name="slide">
                <slide-promocodes v-if="openedPromoCodes" :id="id"></slide-promocodes>
            </transition>
        </div>
    </div>
</template>

<script>
    import SlideDiscounts from './tab-marketing_components/slide-discounts.vue';
    import SlidePromocodes from './tab-marketing_components/slide-promocodes.vue';

    export default {
        name: 'tab-marketing',
        props: ['id', 'legalName'],
        components: {
            SlideDiscounts,
            SlidePromocodes,
        },
        data() {
            return {
                openedDiscounts: false,
                openedPromoCodes: false,
            }
        },
        created() {

        },
        methods: {
            toggleHiddenDiscounts() {
                this.openedDiscounts = !this.openedDiscounts;
            },
            toggleHiddenPromoCodes() {
                this.openedPromoCodes = !this.openedPromoCodes;
            },
        },
    };
</script>