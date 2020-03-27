<template>
    <layout-main>
        <promo-code-form
            :submit-text="'Создать промокод'"
            :action="action"
        ></promo-code-form>
    </layout-main>
</template>

<script>
    import PromoCodeForm from '../components/promo-code-form.vue';

    export default {
        name: 'page-promo-code-create',
        components: {
            PromoCodeForm
        },
        props: {

        },
        data() {
            return {

            }
        },
        methods: {
            action(promoCode) {
                console.log(promoCode);
            },
        }
    }
</script>
