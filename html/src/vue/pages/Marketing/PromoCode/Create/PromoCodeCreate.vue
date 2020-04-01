<template>
    <layout-main>
        <promo-code-form
            :discounts="discounts"
            :gifts="gifts"
            :bonuses="bonuses"
            :i-segments="iSegments"
            :i-roles="iRoles"
            :i-promo-codes="iPromoCodes"
            :promo-code-types="promoCodeTypes"
            :promo-code-statuses="promoCodeStatuses"
            :submit-text="'Создать промокод'"
            :action="action"
            :processing="processing"
        ></promo-code-form>
    </layout-main>
</template>

<script>
    import PromoCodeForm from '../components/promo-code-form.vue';
    import Services from '../../../../../scripts/services/services';

    export default {
        name: 'page-promo-code-create',
        components: {
            PromoCodeForm,
            Services
        },
        props: {
            promoCodeTypes: Object,
            promoCodeStatuses: Object,
            discounts: Object|Array,
            gifts: Object|Array,
            bonuses: Object|Array,
            iSegments: Array,
            iRoles: Array,
            iPromoCodes: Array,
        },
        data() {
            return {
                processing: false,
            }
        },
        methods: {
            action(promoCode) {
                Services.showLoader();
                Services.net().get(this.getRoute('promo-code.save'), null, {
                    promoCode: promoCode
                }).then(code => {
                    this.promoCode.code = code;
                    Services.hideLoader();
                });

                Services.net().post(this.route('promo-code.save'), {}, promoCode).then(data => {
                    Services.msg("Промокод добавлен");
                }, () => {
                    Services.msg("Ошибка при добавлении промокода");
                }).finally(data => {
                    Services.hideLoader();
                    this.processing = false;
                });
            },
        },
        computed: {
        }
    }
</script>
