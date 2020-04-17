<template>
    <div>
        <button @click="toggleHiddenDiscounts" class="btn btn-sm btn-light">
            {{ openedDiscounts ? 'Скрыть' : 'Показать' }} скидки мерчанта
            <fa-icon :icon="openedDiscounts ? 'angle-up' : 'angle-down'"></fa-icon>
        </button>

        <transition name="slide">
                <slide-discounts v-if="openedDiscounts" :id="id"></slide-discounts>
        </transition>
    </div>
</template>

<script>
    import SlideDiscounts from './tab-marketing_components/slide-discounts.vue';

    import Services from '../../../../../scripts/services/services.js';

    export default {
        name: 'tab-marketing',
        props: ['id'],
        components: {
            SlideDiscounts,
        },
        data() {
            return {
                openedDiscounts: false,
            }
        },
        created() {

        },
        methods: {
            toggleHiddenDiscounts() {
                this.openedDiscounts = !this.openedDiscounts;
            },
        },
    };
</script>